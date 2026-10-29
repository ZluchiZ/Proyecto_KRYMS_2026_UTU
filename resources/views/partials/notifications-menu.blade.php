@if (session('tipo_usuario') && session('usuario_id'))
    <div class="notifications" data-notifications>
        <button class="notifications-toggle" type="button" aria-label="Abrir notificaciones" aria-expanded="false" aria-controls="notifications-panel" data-notifications-toggle>
            <span aria-hidden="true">🔔</span>
            <span>Notificaciones</span>
            @if (($notificationSummary['unread'] ?? 0) > 0)
                <span class="notifications-count">{{ $notificationSummary['unread'] }}</span>
            @endif
        </button>
        <div class="notifications-panel" id="notifications-panel" data-notifications-panel hidden>
            <div class="notifications-heading">
                <strong>Notificaciones</strong>
                @if (($notificationSummary['unread'] ?? 0) > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit">Marcar todas leídas</button>
                    </form>
                @endif
            </div>
            @forelse (($notificationSummary['items'] ?? collect()) as $notification)
                <a class="notification-item {{ $notification->read_at ? '' : 'unread' }}" href="{{ route('notifications.open', $notification->id) }}">
                    <span>{{ $notification->message }}</span>
                    <time datetime="{{ $notification->created_at }}">{{ \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() }}</time>
                </a>
            @empty
                <p class="notifications-empty">No tenés notificaciones nuevas.</p>
            @endforelse
            <a class="notifications-all" href="{{ route('notifications.index') }}">Ver todas las notificaciones</a>
        </div>
    </div>
@endif
