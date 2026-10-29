<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/internal.css') }}">
    <title>Notificaciones | El Gaucho Va</title>
    <link rel="stylesheet" href="{{ asset('css/visual-refresh.css') }}">
</head>
<body class="internal-page">
    @include('partials.internal-header')
    <main class="notification-list-page">
        <h1>Todas las notificaciones</h1>
        @if (session('success'))
            <p role="status">{{ session('success') }}</p>
        @endif
        @if ($notifications->isEmpty())
            <p>No tenés notificaciones.</p>
        @else
            <div class="notification-list">
                @foreach ($notifications as $notification)
                    <article class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
                        @if ($notification->order_id)
                            <a href="{{ route('notifications.open', $notification->id) }}">
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
                            <span>{{ $notification->message }}</span>
                        @endif
                        <time datetime="{{ $notification->created_at }}">{{ \Illuminate\Support\Carbon::parse($notification->created_at)->format('d/m/Y H:i') }}</time>
                        @if (! $notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit">Marcar como leída</button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
            {{ $notifications->links() }}
        @endif
    </main>
</body>
</html>
