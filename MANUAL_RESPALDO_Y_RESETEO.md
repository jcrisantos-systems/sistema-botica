# Manual de Respaldo y Restablecimiento — Mi Botica

Guía rápida para cualquier persona, **sin conocimientos de informática**, sobre cómo funciona la copia de seguridad automática y la opción "Restablecer a Estado de Fábrica".

Si el administrador no está disponible y necesitas encontrar un backup o entender qué pasó, este documento te lo explica todo con manos a la obra, sin tecnicismos.

---

## 1. ¿Qué es "Restablecer a Estado de Fábrica"?

Es un botón especial dentro de **Configuración** que borra los datos del sistema (ventas, productos, clientes, etc.) para dejarlo como recién instalado. Se usa, por ejemplo, cuando se quiere entregar el sistema limpio a un cliente nuevo, o para empezar de cero después de hacer pruebas.

**Es una acción muy fuerte: borra información real y no se puede deshacer desde la pantalla.** Por eso el sistema toma una precaución automática antes de tocar cualquier dato: **crea una copia de seguridad (backup)**.

## 2. La copia de seguridad automática (tu "paracaídas")

Cada vez que alguien usa "Restablecer a Estado de Fábrica", **antes de borrar nada**, el sistema:

1. Genera un archivo con **todos los datos actuales** de la botica.
2. Lo guarda en la carpeta: `app/backups/`
3. Le pone un nombre con la fecha y hora exactas, por ejemplo:
   ```
   backup_pre_reset_2026-08-25_14-30-05.sql
   ```
4. **Solo si ese archivo se creó correctamente**, el sistema continúa y borra los datos.

Si por cualquier motivo el backup **no se pudo crear** (por ejemplo, falta de permisos en la carpeta), el sistema **cancela todo automáticamente** y no borra absolutamente nada. Verás un mensaje de error en pantalla explicándolo. Es imposible que el sistema borre datos sin haber guardado antes una copia.

> La carpeta `app/backups/` no se puede abrir desde el navegador (está protegida). Para ver los archivos, alguien debe entrar directamente a la computadora/servidor donde está instalado el sistema, usando el explorador de archivos de Windows.

## 3. Antes de usar el sistema por primera vez: ejecuta el script de configuración

Esto se hace **una sola vez**, apenas se instala el sistema en una computadora nueva.

1. Abre el explorador de archivos de Windows.
2. Entra a la carpeta del proyecto, luego a la carpeta `setup`.
3. Haz **doble clic** sobre el archivo `configurar_entorno.bat`.
4. Se abrirá una ventana negra (la consola de Windows) que hace el trabajo sola. Espera a que diga `LISTO`.
5. Presiona cualquier tecla para cerrar la ventana.

Eso es todo. Ese script:

- Busca automáticamente la herramienta `mysqldump` (la usa el sistema para hacer copias de seguridad más rápidas y completas).
- Crea la carpeta `app/backups/` si todavía no existe.
- Le da permiso de escritura a las carpetas necesarias, para que Windows no bloquee la creación de los backups.

**¿No se encontró `mysqldump` en tu computadora?** No pasa nada. El sistema tiene un método alterno (hecho en el propio sistema) que funciona igual de bien sin necesitar esa herramienta instalada.

## 4. Cómo hacer un Restablecimiento, paso a paso

1. Inicia sesión como **Administrador**.
2. Ve al menú lateral → **Configuración General**.
3. Baja hasta la sección roja **"Zona de Peligro"**.
4. Lee el recuadro verde: te recuerda que el backup se hará solo, automáticamente.
5. Elige una opción:
   - **Limpieza Total para Producción**: borra absolutamente todo (ventas, productos, clientes, proveedores...) y deja el sistema como recién instalado.
   - **Limpiar Transacciones y Mantener Catálogos**: borra solo las ventas/compras/caja, pero conserva tus productos, clientes y proveedores tal como están.
6. Escribe tu **contraseña actual** (la que usas para entrar al sistema).
7. Escribe la palabra **RESTABLECER** (en mayúsculas, tal cual) en el segundo campo.
8. Presiona el botón **"Restablecer Sistema"**.
9. Aparecerá una ventana de confirmación del navegador preguntando si estás totalmente seguro. Acepta solo si de verdad quieres continuar.
10. El sistema:
    - Crea el backup.
    - Si el backup salió bien, borra los datos.
    - Te cierra la sesión automáticamente.
    - Te lleva de vuelta a la pantalla de inicio de sesión.
11. En la pantalla de inicio de sesión verás dos mensajes:
    - Uno confirmando que el restablecimiento fue exitoso.
    - Otro con la **ruta exacta** donde quedó guardado el backup (por ejemplo `app/backups/backup_pre_reset_2026-08-25_14-30-05.sql`). Anótala o haz una captura de pantalla.

## 5. ¿Dónde encuentro un backup si el administrador no está?

1. En la computadora/servidor donde está instalado el sistema, abre el explorador de archivos de Windows.
2. Entra a la carpeta del proyecto (donde está esta misma guía).
3. Entra a `app` y luego a `backups`.
4. Ahí están todos los archivos `.sql`, ordenados por fecha en el nombre. El más reciente es el que tiene la fecha y hora más nueva.

Cada archivo es un respaldo completo de cómo estaban los datos **justo antes** de ese restablecimiento.

## 6. ¿Cómo se restaura un backup si algo salió mal?

Esto sí requiere a alguien con conocimientos técnicos básicos de bases de datos (o a tu proveedor de soporte), porque implica reemplazar la base de datos actual. En resumen, se hace así:

1. Abrir **HeidiSQL** (viene con Laragon) o **phpMyAdmin**.
2. Conectarse a la base de datos `botica_db`.
3. Usar la opción "Ejecutar archivo SQL" / "Importar" y seleccionar el archivo `.sql` de `app/backups/` que se quiera restaurar.
4. Esperar a que termine de ejecutarse.

Si no te sientes cómodo haciendo esto tú mismo, contacta a tu proveedor de soporte técnico y **entrégale el archivo `.sql` de la carpeta `app/backups/`**: con ese archivo puede devolver el sistema exactamente al estado que tenía antes del restablecimiento.

## 7. Preguntas frecuentes

**¿Puedo perder datos sin darme cuenta?**
No. El sistema jamás borra datos sin haber verificado primero que el backup se guardó correctamente. Si el backup falla, el reseteo se cancela solo.

**¿El backup se sube a internet o a algún servidor externo?**
No. El backup se guarda únicamente en el disco de la computadora/servidor donde corre el sistema, dentro de `app/backups/`. Es responsabilidad del administrador copiarlo a un lugar seguro (una USB, la nube, otro disco) si quiere conservarlo fuera de esa carpeta.

**¿Tengo que ejecutar `configurar_entorno.bat` cada vez que hago un reseteo?**
No, solo una vez por computadora (al instalar el sistema, o si lo mueves a otra máquina).

**¿Qué pasa si mysqldump no está instalado?**
El sistema usa automáticamente su propio método de respaldo (hecho en PHP), que no necesita ningún programa adicional instalado. El resultado es el mismo: un archivo `.sql` completo y funcional.
