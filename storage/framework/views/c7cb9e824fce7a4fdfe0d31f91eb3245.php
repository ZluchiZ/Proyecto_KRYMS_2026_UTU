<section class="results-section search-results-section" id="search-results" aria-live="polite">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Encontramos</p>
            <h2>Resultados para “<?php echo e($busqueda); ?>”</h2>
        </div>
        <span class="result-count"><?php echo e($productos->count()); ?> productos</span>
    </div>
    <?php if($productos->isNotEmpty()): ?>
        <div class="product-grid">
            <?php $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('home-product-card', ['producto' => $producto], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <p class="empty-state">No encontramos productos con esa búsqueda.</p>
    <?php endif; ?>
</section>
<?php /**PATH /opt/lampp/htdocs/Proyecto_KRYMS_2026_UTU-main/resources/views/partials/home-search-results.blade.php ENDPATH**/ ?>