<?php
// Servicio de respaldo previo al "Restablecer a Estado de Fábrica". Genera un
// volcado .sql (estructura + datos) completo de la base de datos ANTES de que
// SistemaReset borre una sola fila.
//
// Este es el "paracaídas" del reseteo: ConfiguracionController::reset() SOLO debe
// llamar a SistemaReset::ejecutar() si BackupService::crear() devolvió exito=true.
// Si por cualquier motivo no se pudo generar y verificar el archivo .sql, el
// reseteo debe abortarse por completo y no debe tocarse ninguna tabla.
//
// Estrategia en dos niveles, para que crear() casi nunca falle:
//  1) mysqldump nativo (fiel al 100%: incluye triggers, rutinas, definiciones exactas).
//  2) Si mysqldump no está disponible o falla, un volcado 100% en PHP vía PDO que no
//     depende de ningún binario externo ni de exec()/shell_exec() habilitados.
class BackupService {
    private $conn;
    private $host;
    private $dbName;
    private $username;
    private $password;
    private $dirBackups = '../app/backups';
    private $archivoRutaMysqldump = '../app/config/mysqldump_path.txt';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->host = $db->getHost();
        $this->dbName = $db->getDbName();
        $this->username = $db->getUsername();
        $this->password = $db->getPassword();
    }

    // Punto de entrada único. Devuelve siempre este mismo array:
    // ['exito' => bool, 'archivo' => string|null, 'ruta_relativa' => string|null, 'metodo' => string|null, 'error' => string|null]
    public function crear() {
        if (!is_dir($this->dirBackups)) {
            @mkdir($this->dirBackups, 0755, true);
        }
        $this->asegurarProteccionCarpeta();

        if (!is_dir($this->dirBackups) || !is_writable($this->dirBackups)) {
            return $this->fallo("La carpeta 'app/backups/' no existe o no tiene permisos de escritura. Ejecuta setup/configurar_entorno.bat (como Administrador) y vuelve a intentarlo.");
        }

        $nombreArchivo = 'backup_pre_reset_' . date('Y-m-d_H-i-s') . '.sql';
        $rutaCompleta = rtrim($this->dirBackups, '/') . '/' . $nombreArchivo;

        // Estrategia 1: mysqldump nativo.
        $rutaMysqldump = $this->localizarMysqldump();
        if ($rutaMysqldump !== null && $this->volcarConMysqldump($rutaMysqldump, $rutaCompleta)) {
            return $this->verificarYResponder($rutaCompleta, $nombreArchivo, 'mysqldump');
        }
        @unlink($rutaCompleta);

        // Estrategia 2 (paracaídas): volcado nativo en PHP, sin depender de binarios externos.
        if ($this->volcarConPhp($rutaCompleta)) {
            return $this->verificarYResponder($rutaCompleta, $nombreArchivo, 'php');
        }
        @unlink($rutaCompleta);

        return $this->fallo("No fue posible generar el archivo de respaldo por ningún método disponible (ni mysqldump ni el volcado nativo en PHP). Por seguridad, el restablecimiento fue cancelado y no se modificó ningún dato.");
    }

    private function fallo($mensaje) {
        error_log('BackupService::crear - ' . $mensaje);
        return ['exito' => false, 'archivo' => null, 'ruta_relativa' => null, 'metodo' => null, 'error' => $mensaje];
    }

    // Si acabamos de crear la carpeta (o si a alguien se le olvidó al clonar el
    // repo), garantizamos que quede protegida contra acceso directo por navegador,
    // igual que storage/.htaccess.
    private function asegurarProteccionCarpeta() {
        $rutaHtaccess = rtrim($this->dirBackups, '/') . '/.htaccess';
        if (is_dir($this->dirBackups) && !file_exists($rutaHtaccess)) {
            @file_put_contents($rutaHtaccess, "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n");
        }
    }

    // Verificación final ("paracaídas"): el archivo debe existir y contener SQL
    // reconocible. Si algo quedó a medias, se trata como fallo total (se borra el
    // archivo parcial para no dejar un backup corrupto que parezca válido).
    private function verificarYResponder($rutaCompleta, $nombreArchivo, $metodo) {
        clearstatcache(true, $rutaCompleta);
        if (!is_file($rutaCompleta) || filesize($rutaCompleta) < 200) {
            @unlink($rutaCompleta);
            return $this->fallo("El archivo de respaldo se generó vacío o incompleto (método: $metodo).");
        }
        $contenido = @file_get_contents($rutaCompleta);
        if ($contenido === false || stripos($contenido, 'CREATE TABLE') === false) {
            @unlink($rutaCompleta);
            return $this->fallo("El archivo de respaldo no contiene una estructura SQL válida (método: $metodo).");
        }

        return [
            'exito' => true,
            'archivo' => $nombreArchivo,
            'ruta_relativa' => 'app/backups/' . $nombreArchivo,
            'metodo' => $metodo,
            'error' => null,
        ];
    }

    // Busca el ejecutable de mysqldump. Orden de prioridad:
    //  1) La ruta que guardó setup/configurar_entorno.bat la última vez que se ejecutó.
    //  2) Ubicaciones típicas de Laragon/XAMPP/WAMP en Windows.
    //  3) Ubicaciones típicas en Linux (servidor de producción).
    // No se intenta jamás con "mysqldump" a secas dependiendo del PATH: si no se
    // encuentra una ruta verificada, se prefiere ir directo al paracaídas en PHP.
    private function localizarMysqldump() {
        if (!$this->execDisponible()) {
            return null;
        }

        if (is_file($this->archivoRutaMysqldump)) {
            $ruta = trim((string) @file_get_contents($this->archivoRutaMysqldump));
            if ($ruta !== '' && is_file($ruta)) {
                return $ruta;
            }
        }

        $patrones = [
            'C:/laragon/bin/mysql/*/bin/mysqldump.exe',
            'C:/xampp/mysql/bin/mysqldump.exe',
            'C:/wamp64/bin/mysql/*/bin/mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
        ];
        foreach ($patrones as $patron) {
            foreach ((glob($patron) ?: []) as $ruta) {
                if (is_file($ruta)) {
                    return $ruta;
                }
            }
            if (is_file($patron)) {
                return $patron;
            }
        }

        return null;
    }

    private function execDisponible() {
        if (!function_exists('exec')) {
            return false;
        }
        $deshabilitadas = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        return !in_array('exec', $deshabilitadas, true);
    }

    private function volcarConMysqldump($rutaMysqldump, $rutaDestino) {
        $partes = [
            escapeshellarg($rutaMysqldump),
            '--host=' . escapeshellarg($this->host),
            '--user=' . escapeshellarg($this->username),
        ];
        if ($this->password !== '') {
            $partes[] = '--password=' . escapeshellarg($this->password);
        }
        $partes[] = '--single-transaction';
        $partes[] = '--routines';
        $partes[] = '--triggers';
        $partes[] = '--default-character-set=utf8mb4';
        $partes[] = escapeshellarg($this->dbName);

        $comando = implode(' ', $partes) . ' > ' . escapeshellarg($rutaDestino) . ' 2>' . escapeshellarg($rutaDestino . '.err');

        @exec($comando, $salidaIgnorada, $codigoSalida);

        if ($codigoSalida !== 0 && is_file($rutaDestino . '.err')) {
            error_log('BackupService: mysqldump devolvió código ' . $codigoSalida . ': ' . @file_get_contents($rutaDestino . '.err'));
        }
        @unlink($rutaDestino . '.err');

        return $codigoSalida === 0 && is_file($rutaDestino) && filesize($rutaDestino) > 0;
    }

    // Paracaídas: genera un .sql válido (DROP TABLE + CREATE TABLE + INSERT por
    // lotes) recorriendo el esquema real vía PDO. No requiere ningún binario externo.
    private function volcarConPhp($rutaDestino) {
        $fp = @fopen($rutaDestino, 'w');
        if ($fp === false) {
            return false;
        }

        fwrite($fp, "-- Respaldo automático de Mi Botica (volcado nativo en PHP, sin mysqldump)\n");
        fwrite($fp, "-- Base de datos: {$this->dbName}\n-- Generado: " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($fp, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        try {
            $tablas = $this->conn->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tablas as $tabla) {
                $crea = $this->conn->query('SHOW CREATE TABLE `' . $tabla . '`')->fetch(PDO::FETCH_ASSOC);
                if (empty($crea['Create Table'])) {
                    fclose($fp);
                    return false;
                }
                fwrite($fp, "DROP TABLE IF EXISTS `$tabla`;\n" . $crea['Create Table'] . ";\n\n");
                $this->volcarFilasDeTabla($fp, $tabla);
                fwrite($fp, "\n");
            }
        } catch (PDOException $e) {
            error_log('BackupService::volcarConPhp - ' . $e->getMessage());
            fclose($fp);
            return false;
        }

        fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fp);
        return true;
    }

    private function volcarFilasDeTabla($fp, $tabla) {
        $stmt = $this->conn->query('SELECT * FROM `' . $tabla . '`');
        $columnas = null;
        $lote = [];
        $LOTE_MAX = 500;

        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($columnas === null) {
                $columnas = array_keys($fila);
            }
            $valores = array_map(function ($valor) {
                return $valor === null ? 'NULL' : $this->conn->quote($valor);
            }, array_values($fila));
            $lote[] = '(' . implode(', ', $valores) . ')';

            if (count($lote) >= $LOTE_MAX) {
                $this->escribirInsert($fp, $tabla, $columnas, $lote);
                $lote = [];
            }
        }
        if (!empty($lote)) {
            $this->escribirInsert($fp, $tabla, $columnas, $lote);
        }
    }

    private function escribirInsert($fp, $tabla, $columnas, $filas) {
        fwrite($fp, "INSERT INTO `$tabla` (`" . implode('`, `', $columnas) . "`) VALUES\n" . implode(",\n", $filas) . ";\n");
    }
}
