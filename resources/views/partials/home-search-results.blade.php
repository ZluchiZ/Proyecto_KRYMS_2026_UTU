<section class="results-section search-results-section" id="search-results" aria-live="polite">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Encontramos</p>
            <h2>{{ $busqueda !== '' ? 'Resultados para “'.$busqueda.'”' : 'Resultados filtrados' }}</h2>
        </div>
        <span class="result-count">{{ $productos->count() }} productos · {{ $locales->count() }} locales</span>
    </div>
    @if ($productos->isNotEmpty())
        <div class="product-grid search-result-group">
            @foreach ($productos as $producto)
                @include('home-product-card', ['producto' => $producto])
            @endforeach
        </div>
    @endif
    @if ($locales->isNotEmpty())
        <h3 class="search-results-subheading">Locales</h3>
        <div class="product-grid search-store-grid">
            @foreach ($locales as $local)
                <a class="local-card" href="{{ route('local.profile', $local->RUT) }}">
                    <div class="local-image">
                        <img src="{{ $local->Logo ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=700&q=80' }}" alt="{{ $local->Nombre_Comercio ?: 'Local' }}" loading="lazy" decoding="async">
                        <span class="status-pill {{ $local->Abierto ? '' : 'closed-status' }}">● {{ $local->Abierto ? 'Abierto' : 'Cerrado' }}</span>
                    </div>
                    <div class="local-card-body">
                        <h3>{{ $local->Nombre_Comercio ?: 'Local sin nombre' }}</h3>
                        <p>{{ $local->Horario ?: 'Horario no informado' }}</p>
                        <div class="card-meta"><span>Ver perfil</span></div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
    @if ($productos->isEmpty() && $locales->isEmpty())
        <p class="empty-state">No encontramos productos ni locales con esos criterios.</p>
    @endif
</section>
