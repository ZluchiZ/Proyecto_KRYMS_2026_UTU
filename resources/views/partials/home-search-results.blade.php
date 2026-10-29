<section class="results-section search-results-section" id="search-results" aria-live="polite">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Encontramos</p>
            <h2>Resultados para “{{ $busqueda }}”</h2>
        </div>
        <span class="result-count">{{ $productos->count() }} productos</span>
    </div>
    @if ($productos->isNotEmpty())
        <div class="product-grid">
            @foreach ($productos as $producto)
                @include('home-product-card', ['producto' => $producto])
            @endforeach
        </div>
    @else
        <p class="empty-state">No encontramos productos con esa búsqueda.</p>
    @endif
</section>
