<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InAppNotificationService
{
    public function currentRecipient(): ?array
    {
        $type = session('tipo_usuario');
        $identifier = session('usuario_id');

        if (! in_array($type, ['cliente', 'comercio', 'repartidor', 'administrador'], true)) {
            return null;
        }

        if ($type === 'administrador') {
            $identifier = session('email') ?: $identifier;
        }

        if ($type === 'comercio' && session('email')) {
            $identifier = DB::table('comercio')
                ->where('Email_Usuario', session('email'))
                ->value('RUT') ?: $identifier;
        }

        if ($identifier === null || $identifier === '') {
            return null;
        }

        return ['type' => $type, 'id' => (string) $identifier];
    }

    public function summary(): array
    {
        $recipient = $this->currentRecipient();
        if (! $recipient || ! Schema::hasTable('app_notifications')) {
            return ['items' => collect(), 'unread' => 0];
        }

        $query = $this->recipientQuery($recipient);

        return [
            'items' => $query->orderByDesc('created_at')->limit(8)->get()->map(
                fn (object $item) => $this->decodeData($item)
            ),
            'unread' => $this->recipientQuery($recipient)->whereNull('read_at')->count(),
        ];
    }

    public function paginateForCurrentRecipient(): LengthAwarePaginator
    {
        return $this->recipientQuery($this->requiredRecipient())
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(fn (object $item) => $this->decodeData($item));
    }

    public function markRead(int $notificationId): void
    {
        $updated = $this->recipientQuery($this->requiredRecipient())
            ->where('id', $notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        if ($updated === 0 && ! $this->recipientQuery($this->requiredRecipient())->where('id', $notificationId)->exists()) {
            abort(404);
        }
    }

    public function markAllRead(): void
    {
        $this->recipientQuery($this->requiredRecipient())
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);
    }

    public function sendTo(
        string $recipientType,
        string|int $recipientId,
        string $type,
        string $message,
        ?int $orderId,
        array $data = [],
    ): void {
        DB::table('app_notifications')->insert([
            'recipient_type' => $recipientType,
            'recipient_id' => (string) $recipientId,
            'type' => $type,
            'message' => $message,
            'data' => json_encode($data, JSON_THROW_ON_ERROR),
            'order_id' => $orderId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function notifyOrderParticipants(
        int $orderId,
        string $type,
        string $message,
        array $data = [],
    ): void {
        $pedido = DB::table('pedido')->where('N_Pedido', $orderId)->first(['CI_Cliente']);
        abort_unless($pedido, 404, 'El pedido no existe.');

        if ($pedido->CI_Cliente) {
            $this->sendTo('cliente', $pedido->CI_Cliente, $type, $message, $orderId, $data);
        }

        DB::table('subpedido')
            ->where('N_Pedido', $orderId)
            ->where(fn ($query) => $query->whereNull('Estado')->orWhere('Estado', '<>', 'omitido'))
            ->distinct()
            ->pluck('RUT_Comercio')
            ->filter()
            ->each(fn ($rut) => $this->sendTo('comercio', $rut, $type, $message, $orderId, $data));
    }

    public function redirectForCurrentRecipient(int $orderId): string
    {
        return match ($this->requiredRecipient()['type']) {
            'cliente' => route('cliente.pedidos')."#pedido-{$orderId}",
            'comercio' => route('dashboard.local')."#pedido-{$orderId}",
            'repartidor' => route('dashboard.repartidor')."#pedido-{$orderId}",
            'administrador' => route('dashboard.admin')."#pedido-{$orderId}",
        };
    }

    private function requiredRecipient(): array
    {
        $recipient = $this->currentRecipient();
        abort_unless($recipient, 403, 'Debes iniciar sesión para ver tus notificaciones.');
        abort_unless(Schema::hasTable('app_notifications'), 503, 'Las notificaciones todavía no están disponibles.');

        return $recipient;
    }

    private function recipientQuery(array $recipient)
    {
        return DB::table('app_notifications')
            ->where('recipient_type', $recipient['type'])
            ->where('recipient_id', $recipient['id']);
    }

    private function decodeData(object $notification): object
    {
        $notification->data = $notification->data
            ? json_decode((string) $notification->data, true, 512, JSON_THROW_ON_ERROR)
            : [];

        return $notification;
    }
}
