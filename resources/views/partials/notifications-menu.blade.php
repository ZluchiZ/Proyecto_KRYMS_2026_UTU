@if (session('tipo_usuario') && session('usuario_id'))
    <div class="notifications" data-notifications>
        <button class="notifications-toggle" type="button" aria-label="Notificaciones" aria-expanded="false" aria-controls="notifications-panel" data-notifications-toggle>
            <svg class="notifications-bell" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                <path d="M10 21h4" />
            </svg>
            <span>Notificaciones</span>
            @if (($notificationSummary['unread'] ?? 0) > 0)
                <span class="notifications-count">{{ $notificationSummary['unread'] }}</span>
            @endif
        </button>
        <div class="notifications-panel" id="notifications-panel" data-notifications-panel aria-label="Notificaciones recientes" hidden>
            <div class="notifications-heading">
                <div class="notifications-heading-copy">
                    <strong>Notificaciones</strong>
                    <span>Lo último sobre tu cuenta y pedidos</span>
                </div>
                @if (($notificationSummary['unread'] ?? 0) > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit">Marcar leídas</button>
                    </form>
                @endif
            </div>
            @forelse (($notificationSummary['items'] ?? collect()) as $notification)
                <a class="notification-item {{ $notification->read_at ? '' : 'unread' }}" href="{{ route('notifications.open', $notification->id) }}">
                    <span>{{ $notification->message }}</span>
                    <time datetime="{{ $notification->created_at }}">{{ \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() }}</time>
                </a>
            @empty
                <div class="notifications-empty">
                    <span aria-hidden="true">✓</span>
                    <strong>Todo al día</strong>
                    <p>No tenés notificaciones nuevas.</p>
                </div>
            @endforelse
            <a class="notifications-all" href="{{ route('notifications.index') }}">Ver todas las notificaciones</a>
        </div>
    </div>
@endif
