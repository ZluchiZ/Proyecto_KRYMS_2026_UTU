<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/internal.css')); ?>">
    <title>Mis reportes | El Gaucho Va</title>
</head>
<body class="internal-page customer-profile-page">
    <?php echo $__env->make('partials.internal-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="customer-profile-main">
        <a class="profile-back-link" href="<?php echo e(route('cliente.profile')); ?>">&larr; Volver a mi cuenta</a>
        <section class="customer-profile-card">
            <header class="customer-profile-cover">
                <span class="customer-avatar" aria-hidden="true">!</span>
                <div>
                    <span class="profile-kicker">Ayuda y convivencia</span>
                    <h1>Mis reportes</h1>
                    <p>Enviá una situación para que el equipo de administración la revise.</p>
                </div>
            </header>

            <?php if(session('success')): ?>
                <p class="profile-form-message success" role="status"><?php echo e(session('success')); ?></p>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="profile-form-message error" role="alert">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <section class="customer-reports-section" aria-labelledby="customer-reports-title">
                <header class="customer-reports-heading">
                    <h2 id="customer-reports-title">Enviar un reporte</h2>
                    <p>El detalle debe tener un máximo de 500 caracteres.</p>
                </header>
                <form class="customer-report-form" method="POST" action="<?php echo e(route('cliente.reportes.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="customer-report-field">
                        <label for="report-category">Categoría</label>
                        <select id="report-category" name="categoria" required>
                            <option value="">Seleccionar categoría</option>
                            <?php $__currentLoopData = ['local' => 'Local', 'repartidor' => 'Repartidor', 'aplicacion' => 'Aplicación', 'administracion' => 'Administración', 'otro' => 'Otro']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $etiqueta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($valor); ?>" <?php if(old('categoria') === $valor): echo 'selected'; endif; ?>><?php echo e($etiqueta); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="customer-report-field">
                        <label for="report-description">Detalle</label>
                        <textarea id="report-description" name="comentario" rows="4" maxlength="500" required><?php echo e(old('comentario')); ?></textarea>
                    </div>
                    <button class="customer-report-submit" type="submit">Enviar reporte</button>
                </form>

                <div class="customer-report-history">
                    <h2>Historial</h2>
                    <?php if($reportes->isEmpty()): ?>
                        <p class="customer-report-empty">Todavía no enviaste reportes.</p>
                    <?php else: ?>
                        <?php $__currentLoopData = $reportes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reporte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($categoriasReporte = ['local' => 'Local', 'repartidor' => 'Repartidor', 'aplicacion' => 'Aplicación', 'administracion' => 'Administración', 'otro' => 'Otro']); ?>
                            <article class="customer-report-item">
                                <header>
                                    <strong><?php echo e($categoriasReporte[$reporte->Categoria ?? 'local'] ?? 'Otro'); ?></strong>
                                    <span><?php echo e($reporte->Estado_Gestion ?? 'Pendiente'); ?></span>
                                </header>
                                <p><?php echo e($reporte->Comentario); ?></p>
                                <?php if($reporte->Resolucion): ?>
                                    <p class="customer-report-resolution"><strong>Respuesta:</strong> <?php echo e($reporte->Resolucion); ?></p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
            </section>
        </section>
    </main>
</body>
</html><?php /**PATH /var/home/gsivoplas/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/Cliente/Reportes.blade.php ENDPATH**/ ?>