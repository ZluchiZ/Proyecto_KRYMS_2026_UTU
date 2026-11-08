<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Notificaciones | El Gaucho Va</title>
    <link rel="stylesheet" href="{{ asset('css/visual-refresh.css') }}">
</head>
<body class="internal-page notification-history-page">
    @include('partials.internal-header')
    <main class="notification-list-page">
        <header class="notification-history-heading">
            <span class="notification-history-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                    <path d="M10 21h4" />
                </svg>
            </span>
            <div>
                <p class="notification-history-kicker">Centro de actividad</p>
                <h1>Tus notificaciones</h1>
                <p class="notification-history-description">Novedades sobre tu cuenta y tus pedidos, en un solo lugar.</p>
            </div>
        </header>

        @if (session('success'))
            <p class="notification-history-feedback" role="status">{{ session('success') }}</p>
        @endif
        @if ($notifications->isEmpty())
            <section class="notification-history-empty">
                <span aria-hidden="true">✓</span>
                <h2>Todo al día</h2>
                <p>Cuando haya novedades, las vas a encontrar acá.</p>
            </section>
        @else
            <div class="notification-list">
                @foreach ($notifications as $notification)
                    <article class="notification-record {{ $notification->read_at ? '' : 'unread' }}">
                        <span class="notification-record-icon" aria-hidden="true">
                            @if ($notification->order_id)
                                <span>↗</span>
                            @else
                                <span>•</span>
                            @endif
                        </span>
                        <div class="notification-record-content">
                        @if ($notification->order_id)
                            <a class="notification-record-message" href="{{ route('notifications.open', $notification->id) }}">
                                <span>{{ $notification->message }}</span>
                                @if (! empty($notification->data))
                                    <small>
                                        @foreach ($notification->data as $key => $value)
                                            @if (is_scalar($value))
                                                {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}@if (! $loop->last) · @endif
                                            @endif
                                        @endforeach
                                    </small>
                                @endif
                            </a>
                        @else
                            <span class="notification-record-message">{{ $notification->message }}</span>
                        @endif
                            <div class="notification-record-footer">
                                <time datetime="{{ $notification->created_at }}">{{ \Illuminate\Support\Carbon::parse($notification->created_at)->format('d/m/Y · H:i') }}</time>
                                @if (! $notification->read_at)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit">Marcar como leída</button>
                                    </form>
                                @else
                                    <span class="notification-read-label">Leída</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="notification-pagination">
                {{ $notifications->links() }}
            </div>
        @endif
    </main>
</body>
</html>
