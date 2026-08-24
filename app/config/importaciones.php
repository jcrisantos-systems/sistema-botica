<?php
// Configuración declarativa del módulo de Importación Masiva (app/controllers/ImportacionController.php
// + app/models/Importacion.php). Cada entrada describe una entidad importable: de dónde sale la
// plantilla CSV, cómo se valida cada columna y cuál es la "clave natural" que decide si una fila
// es una actualización (ya existe un registro con esa clave) o una inserción nueva.
//
// Tipos de columna soportados: 'texto' (por defecto), 'decimal', 'entero', 'fecha' (AAAA-MM-DD).
// 'fk' resuelve un valor de texto (p.ej. el nombre de una categoría) contra otra tabla y guarda
// el id resultante bajo 'columna_id'. 'enum' restringe a una lista fija de valores permitidos.
return [
    'categorias' => [
        'label' => 'Categorías',
        'tabla' => 'categorias',
        'solo_admin' => true,
        'clave_natural' => 'nombre',
        'columnas' => [
            'nombre'      => ['requerido' => true],
            'descripcion' => ['requerido' => false],
        ],
    ],

    'laboratorios' => [
        'label' => 'Laboratorios',
        'tabla' => 'laboratorios',
        'solo_admin' => true,
        'clave_natural' => 'nombre',
        'columnas' => [
            'nombre'      => ['requerido' => true],
            'descripcion' => ['requerido' => false],
        ],
    ],

    'proveedores' => [
        'label' => 'Proveedores',
        'tabla' => 'proveedores',
        'solo_admin' => true,
        'clave_natural' => 'ruc',
        'columnas' => [
            'ruc'           => ['requerido' => true],
            'razon_social'  => ['requerido' => true],
            'representante' => ['requerido' => false],
            'telefono'      => ['requerido' => false],
            'direccion'     => ['requerido' => false],
        ],
    ],

    'clientes' => [
        'label' => 'Clientes',
        'tabla' => 'clientes',
        'solo_admin' => false, // cualquier usuario logueado puede registrar clientes (igual que el alta 1 a 1)
        'clave_natural' => 'num_documento',
        'columnas' => [
            'tipo_documento' => ['requerido' => true, 'enum' => ['DNI', 'RUC', 'Sin Documento']],
            'num_documento'  => ['requerido' => true],
            'nombres'        => ['requerido' => true],
            'telefono'       => ['requerido' => false],
            'direccion'      => ['requerido' => false],
        ],
    ],

    'productos' => [
        'label' => 'Productos',
        'tabla' => 'productos',
        'solo_admin' => true,
        'clave_natural' => 'codigo_barras',
        'columnas' => [
            'codigo_barras'      => ['requerido' => false],
            'nombre_generico'    => ['requerido' => true],
            'nombre_comercial'   => ['requerido' => true],
            'concentracion'      => ['requerido' => false],
            'forma_farmaceutica' => ['requerido' => false],
            'categoria'          => ['requerido' => false, 'fk' => ['tabla' => 'categorias', 'buscar_por' => 'nombre', 'columna_id' => 'id_categoria']],
            'laboratorio'        => ['requerido' => false, 'fk' => ['tabla' => 'laboratorios', 'buscar_por' => 'nombre', 'columna_id' => 'id_laboratorio']],
            'unidad_medida'      => ['requerido' => false, 'default' => 'Unidad'],
            'precio_compra'      => ['requerido' => true, 'tipo' => 'decimal'],
            'precio_venta'       => ['requerido' => true, 'tipo' => 'decimal'],
            'stock_minimo'       => ['requerido' => false, 'tipo' => 'entero', 'default' => '10'],
            'requiere_receta'    => ['requerido' => false, 'enum' => ['0', '1'], 'default' => '0'],
        ],
    ],

    // No es un INSERT/UPDATE directo sobre una tabla: cada fila válida se procesa con
    // Inventario::registrarEntrada() (mismo motor que usan Compras al recibir mercadería),
    // dentro de la misma transacción. Por eso 'tabla' queda en null y 'clave_natural' también
    // (cada fila es siempre un movimiento nuevo, nunca "actualiza" un movimiento anterior).
    'inventario' => [
        'label' => 'Inventario (Ingreso de Stock)',
        'tabla' => null,
        'solo_admin' => true,
        'clave_natural' => null,
        'columnas' => [
            'codigo_barras'     => ['requerido' => true, 'fk' => ['tabla' => 'productos', 'buscar_por' => 'codigo_barras', 'columna_id' => 'id_producto']],
            'cantidad'          => ['requerido' => true, 'tipo' => 'entero'],
            'lote'              => ['requerido' => false, 'default' => 'SIN-LOTE'],
            'fecha_vencimiento' => ['requerido' => false, 'tipo' => 'fecha'],
            'motivo'            => ['requerido' => false, 'default' => 'Carga masiva de inventario inicial'],
        ],
    ],
];
