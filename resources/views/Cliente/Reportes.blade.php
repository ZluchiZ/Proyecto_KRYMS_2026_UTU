<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Mis reportes | El Gaucho Va</title>
    <link rel="stylesheet" href="{{ asset('css/visual-refresh.css') }}">
</head>
<body class="internal-page customer-profile-page">
    @include('partials.internal-header')
    <main class="customer-profile-main">
        <a class="profile-back-link" href="{{ route('cliente.profile') }}">&larr; Volver a mi cuenta</a>
        <section class="customer-profile-card">
            <header class="customer-profile-cover">
                <span class="customer-avatar" aria-hidden="true">!</span>
                <div>
                    <span class="profile-kicker">Ayuda y convivencia</span>
                    <h1>Mis reportes</h1>
                    <p>Enviá una situación para que el equipo de administración la revise.</p>
                </div>
            </header>

            @if (session('success'))
                <p class="profile-form-message success" role="status">{{ session('success') }}</p>
            @endif
            @if ($errors->any())
                <div class="profile-form-message error" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <section class="customer-reports-section" aria-labelledby="customer-reports-title">
                <header class="customer-reports-heading">
                    <h2 id="customer-reports-title">Enviar un reporte</h2>
                    <p>El detalle debe tener un máximo de 500 caracteres.</p>
                </header>
                <form class="customer-report-form" method="POST" action="{{ route('cliente.reportes.store') }}">
                    @csrf
                    <div class="customer-report-field">
                        <label for="report-category">Categoría</label>
                        <select id="report-category" name="categoria" required>
                            <option value="">Seleccionar categoría</option>
                            @foreach (['local' => 'Local', 'repartidor' => 'Repartidor', 'aplicacion' => 'Aplicación', 'administracion' => 'Administración', 'otro' => 'Otro'] as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(old('categoria') === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="customer-report-field">
                        <label for="report-description">Detalle</label>
                        <textarea id="report-description" name="comentario" rows="4" maxlength="500" required>{{ old('comentario') }}</textarea>
                    </div>
                    <button class="customer-report-submit" type="submit">Enviar reporte</button>
                </form>

                <div class="customer-report-history">
                    <h2>Historial</h2>
                    @if ($reportes->isEmpty())
                        <p class="customer-report-empty">Todavía no enviaste reportes.</p>
                    @else
                        @foreach ($reportes as $reporte)
                            @php($categoriasReporte = ['local' => 'Local', 'repartidor' => 'Repartidor', 'aplicacion' => 'Aplicación', 'administracion' => 'Administración', 'otro' => 'Otro'])
                            <article class="customer-report-item">
                                <header>
                                    <strong>{{ $categoriasReporte[$reporte->Categoria ?? 'local'] ?? 'Otro' }}</strong>
                                    <span>{{ $reporte->Estado_Gestion ?? 'Pendiente' }}</span>
                                </header>
                                <p>{{ $reporte->Comentario }}</p>
                                @if ($reporte->Resolucion)
                                    <p class="customer-report-resolution"><strong>Respuesta:</strong> {{ $reporte->Resolucion }}</p>
                                @endif
                            </article>
                        @endforeach
                    @endif
                </div>
            </section>
        </section>
    </main>
</body>
</html>