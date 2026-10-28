<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración | KRYMS</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/dashboard-admin.css')); ?>">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <a class="brand" href="<?php echo e(route('home')); ?>"><span class="brand-mark">K</span><span>KRYMS<small>ADMINISTRACIÓN</small></span></a>
            <p class="nav-label">GESTIÓN</p>
            <nav aria-label="Secciones administrativas">
                <?php $__currentLoopData = [
                    'adhesiones' => 'Adhesiones',
                    'usuarios' => 'Usuarios',
                    'reportes' => 'Reportes',
                    'resenas' => 'Reseñas',
                    'recuperaciones' => 'Recuperaciones',
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clave => $titulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a class="nav-link <?php echo e($seccion === $clave ? 'active' : ''); ?>" href="<?php echo e(route('dashboard.admin', ['seccion' => $clave])); ?>" <?php if($seccion === $clave): ?> aria-current="page" <?php endif; ?>>
                        <span><?php echo e($titulo); ?></span>
                        <span class="nav-badge" aria-label="<?php echo e($titulo); ?>: <?php echo e($notificaciones[$clave] ?? 0); ?> elementos"><?php echo e($notificaciones[$clave] ?? 0); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>
            <div class="sidebar-bottom">
                <div class="admin-identity"><span class="avatar"><?php echo e(strtoupper(substr(session('email', 'A'), 0, 1))); ?></span><span><strong>Administrador</strong><small><?php echo e(session('email')); ?></small></span></div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="logout" type="submit">Cerrar sesión <span aria-hidden="true">↗</span></button>
                </form>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar"><span>Panel de control <span class="crumb">/ <?php echo e(ucfirst($seccion)); ?></span></span><span class="live-indicator">Sistema operativo</span></header>
            <div class="page-wrap">
                <section class="page-heading">
                    <div>
                        <p class="eyebrow">KRYMS / ADMINISTRACIÓN</p>
                        <h1><?php echo e(['adhesiones' => 'Adhesiones', 'usuarios' => 'Usuarios', 'reportes' => 'Reportes', 'resenas' => 'Reseñas', 'recuperaciones' => 'Recuperaciones'][$seccion]); ?></h1>
                        <p class="heading-copy">Supervisa las cuentas y el contenido de la plataforma.</p>
                    </div>
                    <span class="date-stamp"><?php echo e(now()->locale('es')->translatedFormat('d M Y')); ?></span>
                </section>

                <section class="metrics" aria-label="Resumen">
                    <article class="metric"><span class="metric-label">Usuarios registrados</span><strong><?php echo e($totalUsuarios); ?></strong><small>Clientes, locales y repartidores</small></article>
                    <article class="metric"><span class="metric-label">Adhesiones pendientes</span><strong><?php echo e($solicitudes->count()); ?></strong><small>Locales y repartidores</small></article>
                    <article class="metric"><span class="metric-label">Reportes recibidos</span><strong><?php echo e($reportes->count()); ?></strong><small>Reportes registrados</small></article>
                    <article class="metric"><span class="metric-label">Reseñas publicadas</span><strong><?php echo e($notificaciones['resenas']); ?></strong><small>Reseñas visibles para clientes</small></article>
                </section>

                <section class="data-panel">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow"><?php echo e(['adhesiones' => 'REVISIÓN DE CUENTAS', 'usuarios' => 'DIRECTORIO', 'reportes' => 'CENTRO DE INCIDENCIAS', 'resenas' => 'CONTENIDO DE LA COMUNIDAD', 'recuperaciones' => 'SEGURIDAD DE CUENTAS'][$seccion]); ?></p>
                            <h2><?php echo e(['adhesiones' => 'Solicitudes de adhesión', 'usuarios' => 'Cuentas registradas', 'reportes' => 'Reportes recibidos', 'resenas' => 'Reseñas registradas', 'recuperaciones' => 'Solicitudes de recuperación'][$seccion]); ?></h2>
                        </div>
                        <?php if($seccion === 'usuarios'): ?>
                            <form class="search-form" method="GET" action="<?php echo e(route('dashboard.admin')); ?>">
                                <input type="hidden" name="seccion" value="usuarios">
                                <label class="sr-only" for="user-search">Buscar usuario</label>
                                <input id="user-search" name="q" type="search" value="<?php echo e($busqueda); ?>" placeholder="Nombre, correo o documento">
                                <button type="submit" aria-label="Buscar">Buscar</button>
                            </form>
                        <?php else: ?>
                            <span class="record-count"><?php echo e(['adhesiones' => $solicitudes->count(), 'reportes' => $reportes->count(), 'resenas' => $resenas->count(), 'recuperaciones' => $recuperaciones->count()][$seccion]); ?> registros</span>
                        <?php endif; ?>
                    </div>

                    <?php if(session('success')): ?>
                        <div class="feedback success"><?php echo e(session('success')); ?></div>
                    <?php endif; ?>
                    <?php if(session('warning')): ?>
                        <div class="feedback warning"><?php echo e(session('warning')); ?></div>
                    <?php endif; ?>
                    <?php if($errors->any()): ?>
                        <div class="feedback warning"><?php echo e($errors->first()); ?></div>
                    <?php endif; ?>

                    <?php if($seccion === 'adhesiones'): ?>
                        <?php if($solicitudes->isEmpty()): ?>
                            <div class="empty-state"><span class="empty-icon">✓</span><strong>No hay adhesiones pendientes</strong><p>Las solicitudes nuevas de locales y repartidores aparecerán aquí.</p></div>
                        <?php else: ?>
                            <div class="table-scroll"><table><thead><tr><th>Solicitante</th><th>Tipo</th><th>Documento</th><th>Correo</th><th>Decisión</th></tr></thead><tbody>
                                <?php $__currentLoopData = $solicitudes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $solicitud): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($solicitud->nombre); ?></td>
                                        <td><span class="type-label"><?php echo e($solicitud->nombre_tipo); ?></span></td>
                                        <td><?php echo e($solicitud->identificador); ?></td>
                                        <td><?php echo e($solicitud->email); ?></td>
                                        <td class="decision-cell">
                                            <form method="POST" action="<?php echo e(route('admin.adhesiones.update', [$solicitud->tipo, $solicitud->email])); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <input type="hidden" name="decision" value="aceptar">
                                                <button class="decision-accept" type="submit">Aceptar</button>
                                            </form>
                                            <details class="reject-details">
                                                <summary>Rechazar</summary>
                                                <form method="POST" action="<?php echo e(route('admin.adhesiones.update', [$solicitud->tipo, $solicitud->email])); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PATCH'); ?>
                                                    <input type="hidden" name="decision" value="rechazar">
                                                    <label class="sr-only" for="motivo-<?php echo e($loop->index); ?>">Motivo del rechazo</label>
                                                    <textarea id="motivo-<?php echo e($loop->index); ?>" name="motivo" placeholder="Indica el motivo" required maxlength="1000"></textarea>
                                                    <button class="decision-reject" type="submit">Confirmar rechazo</button>
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody></table></div>
                        <?php endif; ?>
                    <?php elseif($seccion === 'usuarios'): ?>
                        <?php if($usuarios->isEmpty()): ?>
                            <div class="empty-state"><strong><?php echo e($busqueda ? 'No se encontraron usuarios' : 'Todavía no hay usuarios registrados'); ?></strong><p><?php echo e($busqueda ? 'Prueba con otro nombre, correo o documento.' : 'Las cuentas registradas aparecerán en este listado.'); ?></p></div>
                        <?php else: ?>
                            <div class="table-scroll"><table><thead><tr><th>Usuario</th><th>Tipo de cuenta</th><th>Identificador</th><th>Correo</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
                                <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="user-cell"><span class="table-avatar"><?php echo e(strtoupper(substr($usuario->nombre, 0, 1))); ?></span><?php echo e($usuario->nombre); ?></td>
                                        <td><span class="type-label"><?php echo e($usuario->tipo); ?></span></td>
                                        <td><?php echo e($usuario->identificador); ?></td>
                                        <td><?php echo e($usuario->email); ?></td>
                                        <td><span class="status <?php echo e(strtolower($usuario->estado) === 'activa' ? 'active' : 'suspended'); ?>"><?php echo e($usuario->estado); ?></span></td>
                                        <td>
                                            <form method="POST" action="<?php echo e(route('admin.usuarios.estado', [$usuario->tipo_ruta, $usuario->email])); ?>" onsubmit="return confirm('¿Confirmas el cambio de estado de esta cuenta?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <input type="hidden" name="accion" value="<?php echo e(strtolower($usuario->estado) === 'activa' ? 'suspender' : 'activar'); ?>">
                                                <button class="account-action <?php echo e(strtolower($usuario->estado) === 'activa' ? 'suspend' : 'reactivate'); ?>" type="submit"><?php echo e(strtolower($usuario->estado) === 'activa' ? 'Suspender' : 'Reactivar'); ?></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody></table></div>
                        <?php endif; ?>
                    <?php elseif($seccion === 'reportes'): ?>
                        <?php if($reportes->isEmpty()): ?>
                            <div class="empty-state"><strong>No hay reportes registrados</strong><p>Los reportes enviados aparecerán aquí para su revisión.</p></div>
                        <?php else: ?>
                            <div class="table-scroll"><table><thead><tr><th>Reporte</th><th>Cliente</th><th>Comercio</th><th>Detalle</th><th>Estado</th><th>Gestión</th></tr></thead><tbody>
                                <?php $__currentLoopData = $reportes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reporte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>#<?php echo e($reporte->ID_Reporte); ?></td>
                                        <td><?php echo e($reporte->CI_Cliente); ?></td>
                                        <td><?php echo e($reporte->RUT_Comercio); ?></td>
                                        <td class="detail-cell"><?php echo e($reporte->Comentario ?: 'Sin comentario'); ?></td>
                                        <td><span class="status <?php echo e($reporte->Estado_Gestion === 'Resuelto' ? 'active' : 'pending'); ?>"><?php echo e($reporte->Estado_Gestion ?? 'Pendiente'); ?></span><?php if($reporte->Resolucion): ?><small class="resolution-note"><?php echo e($reporte->Resolucion); ?></small><?php endif; ?></td>
                                        <td class="management-cell">
                                            <form method="POST" action="<?php echo e(route('admin.reportes.update', $reporte->ID_Reporte)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <label class="sr-only" for="estado-reporte-<?php echo e($reporte->ID_Reporte); ?>">Estado del reporte</label>
                                                <select id="estado-reporte-<?php echo e($reporte->ID_Reporte); ?>" name="estado">
                                                    <?php $__currentLoopData = ['pendiente' => 'Pendiente', 'en_revision' => 'En revisión', 'informacion_solicitada' => 'Solicitar información', 'resuelto' => 'Resuelto']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $etiqueta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($valor); ?>" <?php if(($reporte->Estado_Gestion ?? 'Pendiente') === $etiqueta): echo 'selected'; endif; ?>><?php echo e($etiqueta); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <label class="sr-only" for="resolucion-<?php echo e($reporte->ID_Reporte); ?>">Nota de gestión</label>
                                                <textarea id="resolucion-<?php echo e($reporte->ID_Reporte); ?>" name="resolucion" placeholder="Nota o resolución"><?php echo e($reporte->Resolucion); ?></textarea>
                                                <button class="account-action" type="submit">Guardar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody></table></div>
                            <p class="table-note">Solicitar información o resolver un reporte requiere agregar una nota de gestión.</p>
                        <?php endif; ?>
                    <?php elseif($seccion === 'resenas'): ?>
                        <?php if($resenas->isEmpty()): ?>
                            <div class="empty-state"><strong>No hay reseñas registradas</strong><p>Las reseñas de clientes aparecerán aquí para su revisión.</p></div>
                        <?php else: ?>
                            <div class="table-scroll"><table><thead><tr><th>Reseña</th><th>Cliente</th><th>Destinatario</th><th>Puntuación</th><th>Comentario</th><th>Estado</th><th>Moderación</th></tr></thead><tbody>
                                <?php $__currentLoopData = $resenas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resena): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>#<?php echo e($resena->ID); ?></td>
                                        <td><?php echo e($resena->CI_Cliente); ?></td>
                                        <td><?php echo e($resena->RUT_Comercio ?: ($resena->CI_Repartidor ? 'Repartidor '.$resena->CI_Repartidor : 'Sin destinatario')); ?></td>
                                        <td><span class="rating"><?php echo e($resena->Puntuacion ?? '—'); ?> <span aria-hidden="true">★</span></span></td>
                                        <td class="detail-cell"><?php echo e($resena->Comentario ?: 'Sin comentario'); ?><?php if($resena->Motivo_Retiro): ?><small class="resolution-note"><?php echo e($resena->Motivo_Retiro); ?></small><?php endif; ?></td>
                                        <td><span class="status <?php echo e($resena->Estado_Moderacion === 'Retirada' ? 'suspended' : 'active'); ?>"><?php echo e($resena->Estado_Moderacion ?? 'Visible'); ?></span></td>
                                        <td class="management-cell">
                                            <form method="POST" action="<?php echo e(route('admin.resenas.moderate', $resena->ID)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <?php if($resena->Estado_Moderacion === 'Retirada'): ?>
                                                    <input type="hidden" name="decision" value="mantener">
                                                    <button class="account-action" type="submit">Restaurar</button>
                                                <?php else: ?>
                                                    <label class="sr-only" for="motivo-resena-<?php echo e($resena->ID); ?>">Motivo para retirar la reseña</label>
                                                    <textarea id="motivo-resena-<?php echo e($resena->ID); ?>" name="motivo" placeholder="Motivo de incumplimiento" required maxlength="1000"></textarea>
                                                    <button class="account-action suspend" name="decision" value="retirar" type="submit">Retirar</button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody></table></div>
                            <p class="table-note">Las reseñas se publican automáticamente. Retira una solo si incumple las normas, por ejemplo por acoso, discriminación, datos personales o spam; el motivo es obligatorio. Las retiradas pueden restaurarse.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if($recuperaciones->isEmpty()): ?>
                            <div class="empty-state"><strong>No hay solicitudes pendientes</strong><p>Las solicitudes de recuperación aparecerán aquí para su aprobación.</p></div>
                        <?php else: ?>
                            <div class="table-scroll"><table><thead><tr><th>Cuenta</th><th>Correo</th><th>Cédula</th><th>Fecha</th><th>Decisión</th></tr></thead><tbody>
                                <?php $__currentLoopData = $recuperaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recuperacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><span class="type-label"><?php echo e(ucfirst($recuperacion->account_type)); ?></span></td>
                                        <td><?php echo e($recuperacion->email); ?></td>
                                        <td><?php echo e($recuperacion->identifier); ?></td>
                                        <td><?php echo e(\Illuminate\Support\Carbon::parse($recuperacion->created_at)->format('d/m/Y H:i')); ?></td>
                                        <td class="decision-cell">
                                            <form method="POST" action="<?php echo e(route('admin.recuperaciones.approve', $recuperacion->id)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button class="decision-accept" type="submit">Aceptar</button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('admin.recuperaciones.reject', $recuperacion->id)); ?>" onsubmit="return confirm('¿Rechazar esta solicitud?')">
                                                <?php echo csrf_field(); ?>
                                                <button class="decision-reject" type="submit">Rechazar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody></table></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>
                <footer class="page-footer">KRYMS <span>·</span> Gestión de plataforma</footer>
            </div>
        </main>
    </div>
</body>
</html>
<?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/Administrador/DashboardAdministrador.blade.php ENDPATH**/ ?>