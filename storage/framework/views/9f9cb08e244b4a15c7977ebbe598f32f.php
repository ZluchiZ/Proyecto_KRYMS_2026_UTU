<section class="results-section search-results-section" id="search-results" aria-live="polite">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Encontramos</p>
            <h2><?php echo e($busqueda !== '' ? 'Resultados para “'.$busqueda.'”' : 'Resultados filtrados'); ?></h2>
        </div>
        <span class="result-count"><?php echo e($productos->count()); ?> productos · <?php echo e($locales->count()); ?> locales</span>
    </div>
    <?php if($productos->isNotEmpty()): ?>
        <div class="product-grid search-result-group">
            <?php $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('home-product-card', ['producto' => $producto], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    <?php if($locales->isNotEmpty()): ?>
        <h3 class="search-results-subheading">Locales</h3>
        <div class="product-grid search-store-grid">
            <?php $__currentLoopData = $locales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $local): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="local-card" href="<?php echo e(route('local.profile', $local->RUT)); ?>">
                    <div class="local-image">
                        <img src="<?php echo e($local->Logo ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=700&q=80'); ?>" alt="<?php echo e($local->Nombre_Comercio ?: 'Local'); ?>" loading="lazy" decoding="async">
                        <span class="status-pill <?php echo e($local->Abierto ? '' : 'closed-status'); ?>">● <?php echo e($local->Abierto ? 'Abierto' : 'Cerrado'); ?></span>
                    </div>
                    <div class="local-card-body">
                        <h3><?php echo e($local->Nombre_Comercio ?: 'Local sin nombre'); ?></h3>
                        <p><?php echo e($local->Horario ?: 'Horario no informado'); ?></p>
                        <div class="card-meta"><span>Ver perfil</span></div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    <?php if($productos->isEmpty() && $locales->isEmpty()): ?>
        <p class="empty-state">No encontramos productos ni locales con esos criterios.</p>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\Proyecto_KRYMS_2026_UTU-main\resources\views/partials/home-search-results.blade.php ENDPATH**/ ?>