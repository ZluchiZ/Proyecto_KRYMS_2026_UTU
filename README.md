<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Verificación por correo

Los registros de Cliente, Local y Repartidor se completan luego de introducir el código de seis dígitos enviado por correo. El código vence a los 15 minutos; se puede solicitar otro desde el formulario de verificación. Los Locales y Repartidores mantienen además la aprobación administrativa antes de poder iniciar sesión.

Configurá un proveedor SMTP en el archivo `.env` (`MAIL_MAILER`, `MAIL_SCHEME`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` y `MAIL_FROM_ADDRESS`) antes de registrar usuarios. Los valores de `.env.example` son marcadores de posición y deben reemplazarse por los datos del proveedor.

Después de copiar `.env.example` a `.env`, ejecutá `php artisan migrate` para crear la tabla que conserva temporalmente los registros pendientes.

## Seguridad de credenciales

Las contraseñas se almacenan mediante hash seguro con los mecanismos de Laravel (`Hash::make`) y se verifican con `Hash::check`. Un hash no es cifrado: no se revierte para recuperar la contraseña original. Los datos personales como cédula, teléfono y dirección no son contraseñas y no se protegen convirtiéndolos en hashes; su tratamiento depende de controles de acceso, minimización y protección de la base de datos.

## Identidad y autenticación

La identidad única de la aplicación es el modelo `App\Models\Usuario`, respaldado por la tabla `usuario`. Su clave es `Email` y su contraseña se almacena en `Contraseña`. Las tablas `cliente`, `comercio`, `repartidor` y `administrador` contienen perfiles asociados a esa identidad; no son sistemas de autenticación independientes. El proveedor `usuarios` de Laravel también apunta a `Usuario`. El inicio de sesión web conserva el correo, el tipo de usuario y el identificador del perfil en la sesión; la API emite tokens de Sanctum para el mismo modelo.

La tabla estándar `users` pertenece al scaffolding inicial de Laravel y no representa identidades de esta aplicación. La migración base todavía la crea y puede contener registros de instalaciones anteriores, pero no está asociada a un modelo ni a un proveedor de autenticación y la aplicación no la consulta. Se conserva sin una migración destructiva para no borrar datos existentes. Los nuevos usuarios deben registrarse en `usuario`.

La ruta de prueba `/test-cliente` no está registrada y debe responder 404. La prueba `IdentityConfigurationTest` protege este comportamiento para evitar exponer registros de clientes por una ruta de depuración.

<<<<<<< HEAD
Los errores inesperados se reportan en los registros de Laravel y se muestran al usuario con mensajes controlados, sin detalles de consultas o excepciones. En cualquier despliegue accesible desde fuera del equipo local, configurá `APP_ENV=production` y `APP_DEBUG=false` para evitar que Laravel renderice trazas de errores no capturados.

## Métodos de pago del prototipo

En el carrito, el cliente puede seleccionar **efectivo contra entrega** o **tarjeta**. El pago con tarjeta se simula sin cargo y solo acepta los datos `0000 0000 0000 0000`, vencimiento `12/30` y código `000`. La casilla **Guardar método de pago** crea una referencia de tarjeta para el cliente y la asocia al pedido; en carritos futuros esa referencia puede seleccionarse y se muestra enmascarada con sus últimos cuatro dígitos. Si se elige efectivo o no se marca la casilla, no se crea ni asocia una referencia nueva. Solo se guardan los últimos cuatro dígitos, no el número completo, vencimiento ni código; los datos ingresados tampoco se recuperan como datos anteriores si falla la validación. Solo se guarda `tarjeta_simulada` en `Pedido.Metodo_de_pago`. No ingreses datos de tarjetas reales.

Esto no es una pasarela de pago: no hay conexión con un banco o proveedor, ni autorización, cargo, captura o liquidación de dinero. La tabla `Tarjeta` conserva únicamente una referencia al cliente; no almacena número, vencimiento, titular ni código de seguridad. Para procesar pagos electrónicos en una versión futura habrá que integrar un proveedor de pagos y usar su tokenización, sin almacenar credenciales de tarjeta en la aplicación.
=======
## Métodos de pago del prototipo

En el carrito, el cliente puede seleccionar **efectivo contra entrega** o **tarjeta contra entrega**. La selección se guarda en `Pedido.Metodo_de_pago`; es una simulación del método elegido y no procesa un pago. El proyecto no integra una pasarela, autorización, captura ni liquidación de transacciones.

La tabla `Tarjeta` se conserva como referencia técnica del prototipo y no almacena número, vencimiento, titular ni código de seguridad. El checkout crea únicamente una referencia asociada al cliente, sin datos bancarios. No ingreses tarjetas reales. Para procesar pagos electrónicos en una versión futura habrá que integrar un proveedor de pagos y usar su tokenización, sin almacenar credenciales de tarjeta en la aplicación.
>>>>>>> fb7fec2dc5bd3e54183f89cede23e4c7f423b0a3

## Pruebas automatizadas

Ejecutá la suite con `php artisan test`. Las pruebas feature cubren registro y verificación, login, permisos por rol e identidad, carrito y creación de pedidos, decisiones de comercios y clientes, estados de preparación y entrega, asignación de repartidores y selección simulada del método de pago.
