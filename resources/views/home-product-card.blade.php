<article class="product-card">
    <div class="product-image-wrap">
        <img src="{{ $producto->Foto_Producto ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=700&q=80' }}" alt="{{ $producto->Nombre_Producto }}">
        @if ((float) ($producto->Descuento_Porcentaje ?? 0) > 0)<span class="discount-badge">{{ number_format($producto->Descuento_Porcentaje, 0) }}% OFF</span>@endif
    </div>
    <div class="product-card-body">
        <div>
            <span class="product-category">{{ $producto->Categoria ?: 'Producto' }}</span>
            <h3>{{ $producto->Nombre_Producto }}</h3>
            <p class="product-store">{{ $producto->Nombre_Comercio ?: 'Local adherido' }}</p>
        </div>
        <div class="product-footer">
            @php
                $descuentoProducto = (float) ($producto->Descuento_Porcentaje ?? 0);
                $precioFinalProducto = $descuentoProducto > 0 ? $producto->Precio * (1 - ($descuentoProducto / 100)) : $producto->Precio;
            @endphp

            @if ($descuentoProducto > 0)
                <span class="product-price-discounted">$U {{ number_format($precioFinalProducto, 2, ',', '.') }}</span>
                <span class="product-price-original">$U {{ number_format($producto->Precio, 2, ',', '.') }}</span>
            @else
                <strong>$U {{ number_format($producto->Precio, 2, ',', '.') }}</strong>
            @endif

            @if (!$producto->Abierto)
                <span class="unavailable-label closed-status">Cerrado</span>
            @elseif ($producto->Disponible && $esClienteRegistrado)
                <button type="button" class="purchase-trigger add-button" data-product-id="{{ $producto->ID_Producto }}" data-product-name="{{ $producto->Nombre_Producto }}" data-product-price="$U {{ number_format($precioFinalProducto, 2, ',', '.') }}" data-product-local="{{ $producto->Nombre_Comercio ?: 'Local adherido' }}" aria-label="Agregar {{ $producto->Nombre_Producto }}">+</button>
            @elseif ($producto->Disponible)
                <a class="add-button" href="{{ route('login') }}" aria-label="Iniciar sesión para comprar">+</a>
            @else
                <span class="unavailable-label">No disponible</span>
            @endif
        </div>
    </div>
</article>
