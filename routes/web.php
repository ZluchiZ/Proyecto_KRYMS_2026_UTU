<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordRecoveryController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RegistrationVerificationController;
use App\Http\Controllers\RepartidorController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [StorefrontController::class, 'loginForm'])->name('login');

Route::get('/recuperar-contrasena', [PasswordRecoveryController::class, 'create'])
    ->name('password-recovery.request');
Route::post('/recuperar-contrasena', [PasswordRecoveryController::class, 'store'])
    ->middleware('throttle:3,10')
    ->name('password-recovery.submit');
Route::get('/restablecer-contrasena/{recovery}/{token}', [PasswordRecoveryController::class, 'resetForm'])
    ->whereNumber('recovery')
    ->name('password-recovery.reset.form');
Route::post('/restablecer-contrasena/{recovery}/{token}', [PasswordRecoveryController::class, 'reset'])
    ->whereNumber('recovery')
    ->middleware('throttle:5,10')
    ->name('password-recovery.reset');

Route::get('/', [StorefrontController::class, 'home'])->name('home');

Route::get('/buscar-productos', [StorefrontController::class, 'searchProducts'])->name('search.products');

Route::get('/restaurantes', [StorefrontController::class, 'restaurants'])->name('restaurants');

Route::get('/productos', [StorefrontController::class, 'products'])->name('products');

Route::get('/local/historial-ventas', [LocalController::class, 'salesHistory'])
    ->middleware('user.type:comercio')
    ->name('local.sales-history');

Route::get('/local/{rut}', [StorefrontController::class, 'localProfile'])->name('local.profile');

Route::post('/local/{rut}/resenas', [ReviewController::class, 'store'])
    ->middleware('user.type:cliente')
    ->name('local.reviews.store');

Route::get('/dashboard-local', [DashboardController::class, 'commerce'])
    ->middleware('user.type:comercio')
    ->name('dashboard.local');

Route::get('/dashboard-admin', [DashboardController::class, 'administrator'])
    ->middleware('user.type:administrador')
    ->name('dashboard.admin');

Route::patch('/admin/adhesiones/{tipo}/{email}', [AdminController::class, 'updateAdhesion'])
    ->middleware('user.type:administrador')
    ->name('admin.adhesiones.update');
Route::patch('/admin/usuarios/{tipo}/{email}', [AdminController::class, 'updateUserStatus'])
    ->middleware('user.type:administrador')
    ->name('admin.usuarios.estado');
Route::patch('/admin/reportes/{report}', [AdminController::class, 'updateReport'])
    ->middleware('user.type:administrador')
    ->name('admin.reportes.update');
Route::patch('/admin/resenas/{review}', [AdminController::class, 'moderateReview'])
    ->middleware('user.type:administrador')
    ->name('admin.resenas.moderate');
Route::post('/admin/recuperaciones/{recovery}/aprobar', [AdminController::class, 'approvePasswordRecovery'])
    ->whereNumber('recovery')
    ->middleware('user.type:administrador')
    ->name('admin.recuperaciones.approve');
Route::post('/admin/recuperaciones/{recovery}/rechazar', [AdminController::class, 'rejectPasswordRecovery'])
    ->whereNumber('recovery')
    ->middleware('user.type:administrador')
    ->name('admin.recuperaciones.reject');

Route::get('/dashboard-repartidor', [DashboardController::class, 'courier'])
    ->middleware('user.type:repartidor')
    ->name('dashboard.repartidor');

Route::patch('/repartidor/ubicacion', [RepartidorController::class, 'updateLocation'])
    ->middleware('user.type:repartidor')
    ->name('repartidor.ubicacion');
Route::patch('/repartidor/pedidos/{pedido}/estado', [RepartidorController::class, 'updatePedidoStatus'])
    ->middleware('user.type:repartidor')
    ->name('repartidor.pedidos.estado');
Route::patch('/repartidor/pedidos/{pedido}/progreso', [RepartidorController::class, 'updateDeliveryProgress'])
    ->whereNumber('pedido')
    ->middleware('user.type:repartidor')
    ->name('repartidor.pedidos.progreso');
Route::post('/repartidor/pedidos/{pedido}/confirmar-entrega', [RepartidorController::class, 'confirmDelivery'])
    ->whereNumber('pedido')
    ->middleware('user.type:repartidor')
    ->name('repartidor.pedidos.confirmar-entrega');

Route::get('/register', [StorefrontController::class, 'registerForm'])->name('register');
Route::get('/registerLocal', [StorefrontController::class, 'commerceRegistrationForm'])->name('registerLocal');
Route::get('/registerRepartidor', [StorefrontController::class, 'courierRegistrationForm'])->name('registerRepartidor');

Route::post('/Cliente', [ClienteController::class, 'store'])->name('cliente.store');
Route::post('/local', [LocalController::class, 'store'])->name('local.store');
Route::post('/repartidor', [RepartidorController::class, 'store'])->name('repartidor.store');
Route::get('/verificar-correo', [EmailVerificationController::class, 'show'])->name('email-verification.show');
Route::post('/verificar-correo', [EmailVerificationController::class, 'verify'])->name('email-verification.verify');
Route::post('/verificar-correo/reenviar', [EmailVerificationController::class, 'resend'])->name('email-verification.resend');
Route::get('/registro/verificar', [RegistrationVerificationController::class, 'show'])->name('registration.verify.form');
Route::post('/registro/verificar', [RegistrationVerificationController::class, 'verify'])->middleware('throttle:10,10')->name('registration.verify');
Route::post('/registro/verificar/reenviar', [RegistrationVerificationController::class, 'resend'])->middleware('throttle:3,10')->name('registration.verify.resend');
Route::get('/mi-cuenta', [ClienteController::class, 'profile'])->middleware('user.type:cliente')->name('cliente.profile');
Route::get('/mis-reportes', [ReportController::class, 'index'])->middleware('user.type:cliente')->name('cliente.reportes.index');
Route::post('/mis-reportes', [ReportController::class, 'store'])->middleware('user.type:cliente')->name('cliente.reportes.store');
Route::get('/mi-perfil/editar', [ProfileController::class, 'edit'])->middleware('user.type:cliente,comercio,repartidor')->name('profile.edit');
Route::patch('/mi-perfil', [ProfileController::class, 'update'])->middleware('user.type:cliente,comercio,repartidor')->name('profile.update');
Route::post('/productos', [LocalController::class, 'storeProducto'])->middleware('user.type:comercio')->name('productos.store');
Route::patch('/comercio/estado', [LocalController::class, 'updateStatus'])->middleware('user.type:comercio')->name('comercio.status');
Route::patch('/productos/{producto}', [LocalController::class, 'updateProducto'])->middleware('user.type:comercio')->name('productos.update');
Route::delete('/productos/{producto}', [LocalController::class, 'destroyProducto'])->middleware('user.type:comercio')->name('productos.destroy');
Route::post('/carrito/agregar', [PedidoController::class, 'addToCart'])->middleware('user.type:cliente')->name('carrito.add');
Route::get('/cliente/ubicacion', [PedidoController::class, 'clientLocationForm'])->middleware('user.type:cliente')->name('cliente.location.form');
Route::patch('/cliente/ubicacion', [PedidoController::class, 'updateClientLocation'])->middleware('user.type:cliente')->name('cliente.location.update');
Route::get('/carrito', [PedidoController::class, 'cart'])->middleware('user.type:cliente')->name('carrito');
Route::get('/mis-pedidos', [PedidoController::class, 'orderHistory'])->middleware('user.type:cliente')->name('cliente.pedidos');
Route::post('/pedidos/{pedido}/resena-repartidor', [ReviewController::class, 'storeCourierReview'])
    ->whereNumber('pedido')
    ->middleware('user.type:cliente')
    ->name('pedidos.reviews.courier.store');
Route::delete('/carrito/{item}', [PedidoController::class, 'removeFromCart'])->middleware('user.type:cliente')->name('carrito.remove');
Route::post('/carrito/confirmar', [PedidoController::class, 'confirmCart'])->middleware('user.type:cliente')->name('carrito.confirm');
Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'updateStatus'])->middleware('user.type:comercio')->name('pedidos.status');
Route::patch('/pedidos/{pedido}/decision-cliente', [PedidoController::class, 'customerDecision'])
    ->whereNumber('pedido')
    ->middleware('user.type:cliente')
    ->name('pedidos.client-decision');
Route::get('/notificaciones', [NotificationController::class, 'index'])->middleware('user.type:cliente,comercio,repartidor,administrador')->name('notifications.index');
Route::patch('/notificaciones/leer-todas', [NotificationController::class, 'markAllRead'])->middleware('user.type:cliente,comercio,repartidor,administrador')->name('notifications.read-all');
Route::patch('/notificaciones/{notification}/leer', [NotificationController::class, 'markRead'])
    ->whereNumber('notification')
    ->middleware('user.type:cliente,comercio,repartidor,administrador')
    ->name('notifications.read');
Route::get('/notificaciones/{notification}/abrir', [NotificationController::class, 'open'])
    ->whereNumber('notification')
    ->middleware('user.type:cliente,comercio,repartidor,administrador')
    ->name('notifications.open');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
Route::get('/auth/google/complete-register', [GoogleController::class, 'completeRegisterForm'])->name('google.complete.form');
Route::post('/auth/google/complete-register', [GoogleController::class, 'completeRegister'])->name('google.complete.register');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
