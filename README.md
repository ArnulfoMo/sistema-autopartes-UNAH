# Sistema Web de Autopartes y Repuestos «Don Pancho»

**Proyecto académico — Universidad Nacional Autónoma de Honduras (UNAH)**  
**Tecnologías:** PHP, MySQL, HTML, CSS, JavaScript y Bootstrap 5.3.3.  
**Entorno de ejecución:** XAMPP (Apache y MySQL), con administración de la base de datos mediante phpMyAdmin.

## Descripción del proyecto

Sistema web para la empresa ficticia **Autopartes y Repuestos «Don Pancho»**, orientado a la consulta y compra de repuestos automotrices y a la administración de productos e inventario. Integra un portal para clientes y talleres con un área de acceso restringido para personal administrativo y de bodega.

## Funcionalidades

- **Portal público:** página de inicio, catálogo de repuestos, consulta de sucursales y navegación por productos.
- **Catálogo y carrito:** exploración de productos y preparación de pedidos mediante carrito de compras.
- **Arma tu Kit:** módulo para seleccionar repuestos destinados al mantenimiento preventivo.
- **Proceso de compra:** checkout, registro de facturación e historial de compras.
- **Panel administrativo y bodega:** inicio de sesión, control de acceso por roles y gestión del inventario de repuestos.
- **Imágenes de productos:** archivos de fotografías de repuestos almacenados en `uploads/productos/`.
- **Base de datos relacional:** usuarios, categorías, sucursales, productos, inventario, clientes, facturas y detalle de facturas.

## Requisitos previos

1. **XAMPP** instalado, con los servicios **Apache** y **MySQL** disponibles.
2. **PHP con PDO y el controlador `pdo_mysql`** habilitados (incluidos normalmente en XAMPP).
3. Un navegador web actualizado (Chrome, Edge o Firefox).
4. Conexión a Internet para cargar **Bootstrap 5.3.3** y **Bootstrap Icons 1.11.3** desde CDN.
5. Los archivos completos del proyecto, incluidos los scripts SQL de la carpeta `database/`.

> No es necesario ejecutar Composer ni `npm install` para la estructura entregada: los recursos propios se encuentran en `assets/` y Bootstrap se carga por CDN.

## Instalación y puesta en marcha con XAMPP

### 1. Ubicar el proyecto en `htdocs`

Copiar la carpeta del proyecto a la carpeta `htdocs` de XAMPP. En una instalación estándar de Windows, la ruta debe quedar así:

```text
C:\xampp\htdocs\sistema-autopartes-UNAH\
```

**Importante:** mantener exactamente el nombre `sistema-autopartes-UNAH`, ya que el proyecto utiliza esa ruta en `includes/header.php` e `includes/auth.php`. Si se cambia el nombre, también habrá que actualizar las rutas correspondientes.

### 2. Iniciar los servicios

Abrir **XAMPP Control Panel** y presionar **Start** en **Apache** y **MySQL**. Apache ejecuta el código PHP y MySQL almacena la información de la aplicación.

### 3. Importar la estructura y crear automáticamente la base de datos

1. Abrir **http://localhost/phpmyadmin/**.
2. En la página principal de phpMyAdmin, **sin seleccionar una base de datos**, abrir la pestaña **Importar**.
3. Pulsar **Seleccionar archivo** y elegir `database/don_pancho.sql` desde la carpeta del proyecto.
4. Pulsar **Importar** (o **Continuar**, según la versión) y comprobar que la ejecución termina sin errores.

**No es necesario crear manualmente la base de datos**: el script contiene estas instrucciones:

```sql
CREATE DATABASE don_pancho;
USE don_pancho;
```

Por tanto, al ejecutarlo, MySQL crea y selecciona `don_pancho`, y después crea sus tablas y relaciones: `usuarios`, `categorias`, `sucursales`, `productos`, `inventario`, `clientes`, `facturas` y `detalle_factura`.

> **Importante:** el comando `CREATE DATABASE don_pancho;` presupone que la base de datos todavía no existe. Si ya existe, la importación puede producir el error `#1007 - Can't create database`. Para una instalación limpia, importar el archivo solo una vez; no eliminar una base existente si contiene información que se desea conservar. Cambiarlo a `CREATE DATABASE IF NOT EXISTS don_pancho;` evita únicamente ese error, pero no los derivados de tablas preexistentes.

### 4. Importar los datos iniciales

1. En el panel izquierdo de phpMyAdmin, seleccionar la base de datos **`don_pancho`**, creada en el paso anterior.
2. Abrir la pestaña **Importar**.
3. Elegir el archivo `database/DMLDonPancho.sql`.
4. Pulsar **Importar** y comprobar que finaliza sin errores.

El archivo `DMLDonPancho.sql` carga los datos de prueba previstos en el proyecto, incluidos los usuarios de evaluación. **Se debe importar después de `don_pancho.sql`**, ya que necesita que las tablas existan previamente.

| Orden | Archivo | Función |
|---|---|---|
| 1 | `database/don_pancho.sql` | Crear automáticamente la base de datos `don_pancho` y sus tablas. |
| 2 | `database/DMLDonPancho.sql` | Insertar los registros iniciales para probar el sistema. |

### 5. Verificar la conexión a MySQL

El archivo `config/conexion.php` contiene la configuración de conexión a la base de datos mediante **PDO**. La configuración incluida corresponde a la instalación local habitual de XAMPP:

```text
Servidor: localhost
Base de datos: don_pancho
Usuario MySQL: root
Contraseña MySQL: (vacía)
```

Si el servicio MySQL utiliza otra contraseña, servidor o configuración, ajustar estos valores en `config/conexion.php`. **Las credenciales de MySQL son diferentes de los usuarios que inician sesión en la aplicación.**

### 6. Abrir el sistema

Con Apache y MySQL iniciados, abrir en el navegador:

- **Página de inicio:** http://localhost/sistema-autopartes-UNAH/
- **Inicio de sesión:** http://localhost/sistema-autopartes-UNAH/login.php
- **Panel administrativo/bodega:** http://localhost/sistema-autopartes-UNAH/admin/index.php (requiere iniciar sesión)

## Credenciales de acceso para evaluación

Las siguientes cuentas se incluyen en los datos iniciales del proyecto para facilitar las pruebas del docente:

| Nombre | Usuario | Contraseña | Rol |
|---|---|---|---|
| Administrador Principal | `admin` | `DonPanchoadmin123@` | `admin` |
| Carlos Mendoza | `carlos` | `DonPanchobodega123@` | `bodeguero` |
| Ana López | `ana` | `DonPanchobodega123@` | `bodeguero` |
| José Martínez | `jose` | `DonPanchobodega123@` | `bodeguero` |
| María Flores | `maria` | `DonPanchobodega123@` | `bodeguero` |

**Cómo probar el acceso:** dirigirse a `login.php`, introducir un usuario y su contraseña, e iniciar sesión. Para evaluar distintos perfiles, cerrar la sesión actual antes de ingresar con otra cuenta.

> **Aviso de seguridad:** estas contraseñas se documentan únicamente para la evaluación académica en un entorno local. No deben reutilizarse en un sistema publicado. Las contraseñas se almacenan como hashes en la base de datos y la aplicación comprueba las claves mediante `password_verify()`.

## Estructura del proyecto

```text
sistema-autopartes-UNAH/
├── index.php                 # Página de inicio
├── catalogo.php              # Catálogo de productos
├── kit.php                   # Arma tu Kit
├── carrito.php               # Carrito de compras
├── checkout.php              # Finalización de la compra
├── factura.php               # Facturación
├── historial.php              # Historial de compras
├── sucursales.php             # Información de sucursales
├── login.php                  # Inicio de sesión
├── logout.php                 # Cierre de sesión
├── admin/                     # Panel e inventario
├── assets/
│   ├── css/                  # Estilos
│   ├── js/                   # Scripts del navegador
│   └── img/                  # Imágenes del sitio
├── clases/                    # Clases PHP (Producto, Usuario, etc.)
├── config/
│   └── conexion.php          # Conexión PDO a MySQL
├── database/
│   ├── don_pancho.sql         # Estructura de la base de datos
│   └── DMLDonPancho.sql       # Datos iniciales
├── includes/                  # Cabecera, pie y autenticación
├── uploads/productos/        # Fotografías de productos
└── README.md                  # Esta guía
```

## Verificaciones recomendadas para el docente

1. Confirmar que la página de inicio y el catálogo muestran la información importada.
2. Ingresar con `admin` y comprobar el acceso al panel administrativo.
3. Cerrar sesión e ingresar con `carlos` para evaluar el perfil `bodeguero`.
4. Explorar las opciones de inventario, kit, carrito y checkout.
5. Comprobar que las imágenes de productos se muestran correctamente.

## Solución de problemas comunes

| Problema | Posible causa y solución |
|---|---|
| `localhost` no responde | Verificar que Apache esté iniciado en XAMPP y que su puerto no esté ocupado. |
| Error de conexión a la base de datos | Verificar que MySQL esté iniciado, que se haya creado `don_pancho` con el primer script y que los datos de `config/conexion.php` coincidan con la instalación local. |
| Error al consultar tablas | Revisar que se hayan importado **ambos** scripts SQL en el orden indicado; el primero crea la base y las tablas, y el segundo inserta los datos. |
| Enlaces o estilos no funcionan | Verificar que la carpeta se llame exactamente `sistema-autopartes-UNAH` y esté dentro de `htdocs`. |
| No se puede iniciar sesión | Confirmar que los usuarios estén en la tabla `usuarios`, que se haya importado `DMLDonPancho.sql` y que se introduzca la contraseña correcta. |
| Bootstrap no carga | Verificar la conexión a Internet, ya que se utilizan recursos externos mediante CDN. |

---

**Finalidad:** proyecto desarrollado con fines académicos y de demostración. Las cuentas y datos iniciales se proporcionan exclusivamente para la evaluación local del sistema.
