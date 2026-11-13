# Proyecto KRYMS 2026 - UTU

## Introducción

El presente documento describe la arquitectura, la estructura funcional y la configuración del proyecto de software desarrollado para la Universidad del Trabajo del Uruguay (UTU). El sistema corresponde a una aplicación web orientada a la gestión de pedidos online, con foco en la relación entre clientes, comercios, repartidores y administradores.

El proyecto se implementa en Laravel 13 y permite la gestión integral de un marketplace de delivery, incluyendo registro de usuarios, autenticación, catálogos de productos, carrito de compras, pedidos, entrega, reseñas y administración.

## Descripción general del sistema

KRYMS es una plataforma web de delivery orientada a conectar clientes con comercios locales y repartidores en un mismo ecosistema digital. La aplicación permite explorar restaurantes o comercios, seleccionar productos, confirmar compras, hacer seguimiento del pedido y gestionar la operación desde distintos perfiles.

La solución está diseñada para funcionar con distintos roles dentro del mismo sistema:

- Cliente: busca productos, arma su carrito, confirma pedidos y recibe actualizaciones del estado.
- Comercio: administra su catálogo, atiende pedidos y actualiza el estado del local y de las ventas.
- Repartidor: recibe entregas, actualiza ubicación y confirma la finalización del servicio.
- Administrador: supervisa usuarios, solicitudes, reportes, reseñas y validaciones del sistema.

Esta plataforma es útil tanto para una operación local como para un prototipo de marketplace digital con lógica realista de ventas y logística.

Para acceder a la guía completa de uso, consultar el archivo [MANUAL_DE_USUARIO.md](MANUAL_DE_USUARIO.md).

## 1. Objetivo general

Desarrollar una plataforma web que permita:

- registrar y autenticar distintos perfiles de usuario;
- gestionar locales comerciales y sus productos;
- facilitar la compra por parte de clientes;
- coordinar la preparación y entrega de pedidos;
- monitorear el funcionamiento del sistema mediante roles y permisos diferenciados;
- ofrecer una base extensible para futuras mejoras funcionales y operativas.

## 2. Alcance del sistema

El sistema contempla cuatro roles principales:

- Cliente: realiza búsquedas, agrega productos al carrito, confirma pedidos y gestiona su historial.
- Comercio: administra sus productos, estado del local y atención de pedidos.
- Repartidor: gestiona disponibilidad, ubicación y entrega del pedido.
- Administrador: supervisa usuarios, reportes, reseñas y solicitudes administrativas.

## 3. Stack tecnológico

- PHP 8.3
- Laravel Framework 13.x
- MySQL
- Blade Templates
- Vite + Tailwind CSS
- Sanctum para autenticación API
- Socialite para autenticación con Google
- SMTP para envío de correos
- PHPUnit para pruebas automatizadas

## 4. Arquitectura del proyecto

La aplicación sigue el patrón MVC de Laravel, con integración de vistas, controladores, modelos, migraciones y rutas. La organización principal del repositorio es la siguiente:

```text
app/
  Exceptions/
  Http/
    Controllers/
    Middleware/
  Mail/
  Models/
  Providers/
  Services/
bootstrap/
config/
  auth.php
  services.php
  ...
database/
  migrations/
  seeders/
public/
resources/
  css/
  js/
  views/
routes/
  api.php
  web.php
storage/
  app/
  framework/
  logs/
tests/
```

### 4.1 Componentes principales

- `app/Models/Usuario`: entidad central de autenticación y acceso al sistema.
- `app/Models/Cliente`, `Comercio`, `Repartidor`, `Administrador`: perfiles específicos por tipo de usuario.
- `app/Models/Producto`, `Pedido`, `Subpedido`, `Tarjeta`, `Resena`, `Reporte`: entidades del dominio del negocio.
- `app/Http/Controllers`: lógica de presentación y flujo de negocio para cada módulo.
- `app/Services`: servicios de correo, verificación y notificaciones.
- `routes/web.php`: rutas de la aplicación web.
- `routes/api.php`: puntos de acceso de la API.
- `database/migrations`: definición y evolución del esquema de base de datos.

## 5. Modelo de autenticación y autorización

La identidad del sistema está representada por el modelo `Usuario`, configurado como proveedor de autenticación en `config/auth.php`. Este modelo se utiliza como base para todos los perfiles del sistema, en lugar de depender de la tabla estándar `users` generada por Laravel.

### 5.1 Rol y perfiles

El modelo `Usuario` contiene la columna `Tipo_Usuario`, la cual permite distinguir los perfiles dentro de la plataforma. Los perfiles asociados se gestionan mediante tablas específicas:

| Rol | Descripción |
| --- | --- |
| Cliente | Compra, consulta historial y realiza pedidos |
| Comercio | Gestiona catálogo y pedidos |
| Repartidor | Atiende entregas y actualiza estado |
| Administrador | Administra operación general y validaciones |

El sistema usa middleware con reglas de acceso por tipo de usuario para restringir el acceso a rutas y paneles según el perfil.

## 6. Flujo funcional principal

### 6.1 Registro y validación

El proceso de registro contempla la creación del usuario y la generación de una validación por correo electrónico o código de verificación. Los comercios y repartidores además requieren aprobación administrativa para acceder plenamente al sistema.

El proyecto incluye mecanismos para:

- registro de clientes, comercios y repartidores;
- verificación por correo;
- recuperación de contraseña;
- validación de datos sensibles;
- control de solicitudes repetidas mediante throttling.

### 6.2 Gestión de productos y locales

Los comercios pueden registrar información del negocio, cargar productos, actualizar inventario y operar su disponibilidad. La aplicación también permite mostrar los locales en una vista comercial y gestionar las reseñas asociadas.

### 6.3 Compras y pedidos

El cliente puede navegar por locales y productos, agregar artículos al carrito, definir la dirección de entrega, seleccionar un método de pago simulado y confirmar la compra. El pedido queda asociado a un estado dentro del flujo de preparación y entrega.

### 6.4 Entrega y seguimiento

Los repartidores actualizan disponibilidad, ubican el pedido y reportan avances hasta la confirmación de entrega. El sistema permite registrar datos de geolocalización y progreso del transporte.

### 6.5 Administración

El administrador tiene acceso a herramientas para:

- aprobar o rechazar adhesiones;
- gestionar usuarios;
- moderar reseñas y reportes;
- revisar solicitudes de recuperación de contraseña;
- resaltar comercios mediante suscripción o relevancia.

## 7. Rutas principales

### 7.1 Rutas web

El sistema define rutas para:

- autenticación: `/login`, `/register`, `/logout`
- perfil: `/mi-cuenta`, `/mi-perfil/editar`
- storefront: `/`, `/restaurantes`, `/productos`, `/local/{rut}`
- carrito y pedidos: `/carrito`, `/mis-pedidos`, `/pedidos/{pedido}/estado`
- dashboards: `/dashboard-local`, `/dashboard-repartidor`, `/dashboard-admin`
- notificaciones: `/notificaciones`, `/notificaciones/{notification}/abrir`
- recuperación de contraseña: `/recuperar-contrasena`, `/restablecer-contrasena/{recovery}/{token}`

### 7.2 Rutas API

La API implementada en `routes/api.php` incluye endpoints de registro y login, además de rutas protegidas por `auth:sanctum`.

Endpoints básicos:

- `POST /api/registro`
- `POST /api/login`
- `POST /api/logout`
- `GET /api/usuario`
- `GET /api/user`

## 8. Base de datos y modelo de dominio

El proyecto utiliza un esquema de datos orientado a un marketplace de delivery. Las migraciones incluyen tablas como:

- `Usuario`
- `Cliente`
- `Comercio`
- `Repartidor`
- `Producto`
- `Pedido`
- `Subpedido`
- `Tarjeta`
- `Detalle_de_pedido`
- `Reporte`
- `Reseña`
- `Vehículo`
- `pedido_repartidor_estado`
- `registration_verifications`
- `email_verifications`
- `password_recovery_requests`
- `app_notifications`

Estas tablas reflejan la lógica de negocio del sistema y contienen campos relacionados con identidad, tipo de usuario, estado del pedido, ubicación, pagos simulados, calificaciones y reportes.

## 9. Configuración del entorno

Antes de ejecutar la aplicación, debe configurarse el archivo `.env` a partir del ejemplo provisto:

```bash
cp .env.example .env
```

Variables principales:

```env
APP_NAME=KRYMS
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=el_gaucho_va
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="KRYMS"

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=
```

Es importante configurar correctamente SMTP para que los procesos de verificación y recuperación de contraseña funcionen de forma completa.

## 10. Instalación y ejecución

### 10.1 Dependencias

```bash
composer install
npm install
```

### 10.2 Generación de clave y migración

```bash
php artisan key:generate
php artisan migrate
```

### 10.3 Ejecutar la aplicación

Modo de desarrollo:

```bash
composer run dev
```

También puede ejecutarse por separado:

```bash
php artisan serve
npm run dev
```

### 10.4 Build de producción

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 11. Comandos útiles

```bash
php artisan test
php artisan migrate
php artisan migrate:fresh
php artisan queue:listen
php artisan cache:clear
php artisan config:clear
```

## 12. Pruebas automatizadas

El proyecto cuenta con pruebas automatizadas ejecutadas con PHPUnit mediante el siguiente comando:

```bash
php artisan test
```

Las pruebas abarcan funcionalidades clave como:

- registro y validación de usuarios;
- login y permisos por rol;
- autenticación y configuración de identidad;
- carrito y creación de pedidos;
- decisiones del comercio y del cliente;
- estados de preparación y entrega;
- asignación de repartidores;
- simulación del método de pago.

## 13. Seguridad y buenas prácticas

- Las contraseñas se manejan mediante hash de Laravel.
- La autenticación web usa sesión y la API usa tokens de Sanctum.
- La identidad central del sistema es `Usuario`, no la tabla `users` del scaffold base.
- En entornos productivos, se recomienda mantener `APP_DEBUG=false` y `APP_ENV=production`.
- El flujo de pagos es una simulación; no debe utilizarse información bancaria real.

## 14. Nota sobre el prototipo de pagos

El sistema no integra una pasarela real de pagos ni un procesamiento bancario. El campo `Metodo_de_pago` del pedido registra la opción elegida por el cliente, como efectivo o tarjeta simulada, pero no ejecuta una transacción real. La tabla `Tarjeta` se usa únicamente como referencia del prototipo y no debe almacenar datos reales de tarjetas.

## 15. Manual de instalación y mantenimiento

### 15.1 Requisitos del sistema

Para instalar y operar la aplicación correctamente se requiere:

- PHP 8.3 o superior
- Composer
- Node.js y npm
- MySQL 8.0 o compatible
- Apache o Nginx
- Git
- Acceso a un proveedor SMTP para envío de correos de verificación y recuperación

### 15.2 Preparación del entorno local

1. Clonar el repositorio:

```bash
git clone https://github.com/ZluchiZ/Proyecto_KRYMS_2026_UTU.git
cd Proyecto_KRYMS_2026_UTU-main
```

2. Instalar dependencias de PHP y Node:

```bash
composer install
npm install
```

3. Crear el archivo de entorno:

```bash
cp .env.example .env
```

4. Generar la clave de la aplicación:

```bash
php artisan key:generate
```

5. Configurar la base de datos en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=el_gaucho_va
DB_USERNAME=root
DB_PASSWORD=
```

6. Ejecutar migraciones y preparar la base de datos:

```bash
php artisan migrate
```

7. Ejecutar la aplicación en modo de desarrollo:

```bash
composer run dev
```

### 15.3 Ejecución en producción

Para un entorno productivo se recomienda:

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

Luego se debe servir la aplicación mediante un servidor web (Apache o Nginx) apuntando al directorio `public/` del proyecto.

### 15.4 Configuración de correo y notificaciones

El sistema depende de un proveedor SMTP para:

- verificación de correo,
- recuperación de contraseña,
- notificaciones internas.

Debe configurarse en `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="KRYMS"
```

Si no se configura correctamente, algunas funcionalidades del flujo de usuario quedarán inhabilitadas.

### 15.5 Mantenimiento de la base de datos

Se recomienda realizar mantenimiento periódico con los siguientes pasos:

```bash
php artisan migrate
php artisan db:seed
php artisan optimize
```

Para recrear la base de datos en un entorno de pruebas o desarrollo:

```bash
php artisan migrate:fresh
```

> Esta acción elimina los datos actuales. Debe utilizarse con precaución.

### 15.6 Copias de seguridad

Se recomienda hacer respaldo de la base de datos y del proyecto antes de cada despliegue o actualización importante:

```bash
mysqldump -u root -p el_gaucho_va > backup_el_gaucho_va.sql
```

También es conveniente conservar una copia del contenido del directorio del proyecto y de los archivos de configuración `.env`.

### 15.7 Actualización del sistema

Para actualizar la aplicación suavemente:

1. Descargar o integrar los cambios del repositorio.
2. Ejecutar la instalación de dependencias:

```bash
composer install
npm install
```

3. Aplicar migraciones pendientes:

```bash
php artisan migrate
```

4. Ejecutar limpieza y optimización:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

5. Reiniciar el servicio web y el proceso de colas si corresponde.

### 15.8 Monitoreo y resolución de incidencias

Para operar la aplicación en forma estable se recomienda:

- revisar los logs de Laravel en `storage/logs/`;
- verificar la salud de la base de datos y la conexión SMTP;
- confirmar el estado de los workers de cola cuando existan tareas en segundo plano;
- revisar errores de validación de usuarios, pedidos o autenticación tras cada cambio funcional.

Comandos útiles:

```bash
php artisan test
php artisan queue:listen
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 15.9 Buenas prácticas operativas

- Mantener `APP_ENV=production` y `APP_DEBUG=false` en entornos productivos.
- No guardar información bancaria real ni datos sensibles no requeridos.
- Mantener un control de versiones del proyecto y una estrategia de despliegue documentada.
- Realizar pruebas de regresión antes de publicar cambios funcionales.
- Documentar cada ajuste importante de base de datos y configuración.

### 15.10 Instalación en Windows (XAMPP + Composer + Node)

Esta guía está pensada para entornos Windows con XAMPP, y es útil cuando se quiere levantar la aplicación de forma local sin usar Linux o un servidor web externo.

#### 1. Instalar requisitos

Descargar e instalar:

- XAMPP desde https://www.apachefriends.org/
- Git desde https://git-scm.com/
- Composer desde https://getcomposer.org/download/
- Node.js desde https://nodejs.org/

Luego verificar que queden disponibles en el PATH:

```powershell
php -v
composer -V
node -v
npm -v
```

#### 2. Activar MySQL y Apache en XAMPP

1. Abrir XAMPP Control Panel.
2. Iniciar `Apache` y `MySQL`.
3. Verificar que MySQL esté corriendo en el puerto 3306.
4. Crear la base de datos desde phpMyAdmin o desde consola:

```sql
CREATE DATABASE el_gaucho_va;
```

#### 3. Clonar el proyecto

```powershell
git clone https://github.com/ZluchiZ/Proyecto_KRYMS_2026_UTU.git
cd Proyecto_KRYMS_2026_UTU-main
```

#### 4. Instalar dependencias

```powershell
composer install
npm install
```

#### 5. Configurar `.env`

Crear la copia del archivo de entorno:

```powershell
copy .env.example .env
```

Editar el archivo `.env` con el siguiente contenido base:

```env
APP_NAME=KRYMS
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=el_gaucho_va
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="KRYMS"
```

#### 6. Generar la clave de Laravel

```powershell
php artisan key:generate
```

#### 7. Ejecutar migraciones

```powershell
php artisan migrate
```

Si se quiere dejar la base de datos en estado vacío y recrearla desde cero:

```powershell
php artisan migrate:fresh
```

#### 8. Levantar la aplicación

Opción 1: ejecutar todo en modo desarrollo:

```powershell
composer run dev
```

Opción 2: correr por separado:

```powershell
php artisan serve
npm run dev
```

La aplicación quedará normalmente disponible en:

```text
http://localhost:8000
```

#### 9. Si se usa XAMPP como servidor web

También puede servirse desde la carpeta del proyecto dentro de `htdocs` de XAMPP y acceder con:

```text
http://localhost/Proyecto_KRYMS_2026_UTU-main/public
```

En ese caso, la estructura debe quedar dentro de la carpeta `htdocs` del XAMPP, por ejemplo:

```text
C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main
```

#### 10. Problemas comunes en Windows

- `php` no es reconocido: reiniciar la terminal o agregar la ruta de PHP al PATH.
- `composer` no es reconocido: reiniciar la terminal o instalar Composer con la opción de PATH habilitada.
- Error de conexión con MySQL: verificar que XAMPP esté ejecutando MySQL y que la base exista.
- Error de permisos: revisar los permisos del proyecto y que la carpeta no esté bloqueada por antivirus.
- La aplicación no carga assets: ejecutar `npm install` y luego `npm run dev`.

### 15.11 Recomendaciones para entorno Windows

- Mantener XAMPP y Node actualizados.
- No usar rutas con espacios en la carpeta del proyecto.
- Usar `cmd` o PowerShell con permisos normales.
- Ejecutar la aplicación desde la raíz del proyecto para evitar errores de rutas.
- Guardar siempre una copia de `.env` y del respaldo de la base de datos antes de cambios importantes.

## 16. Estado del proyecto

El presente repositorio corresponde a un prototipo funcional de aplicación de delivery con énfasis en la lógica de negocio, la gestión de usuarios y la coordinación operativa del pedido. Su objetivo es demostrar una solución completa y coherente para la gestión del servicio, utilizando Laravel como framework base.

## 17. Licencia

El proyecto se distribuye bajo la licencia MIT de Laravel, salvo que el repositorio o la institución responsable defina una normativa específica adicional.
