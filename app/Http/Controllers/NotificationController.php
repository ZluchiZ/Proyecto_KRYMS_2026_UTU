<?php

namespace App\Http\Controllers;

use App\Services\InAppNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(InAppNotificationService $notifications): View
    {
        return view('notifications.index', [
            'notifications' => $notifications->paginateForCurrentRecipient(),
        ]);
    }

    public function open(int $notification, InAppNotificationService $notifications): RedirectResponse
    {
        $notifications->markRead($notification);
        $item = \Illuminate\Support\Facades\DB::table('app_notifications')
            ->where('id', $notification)
            ->first(['order_id']);

        abort_unless($item, 404);
        $target = $item->order_id
            ? $notifications->redirectForCurrentRecipient((int) $item->order_id)
            : route('notifications.index');

        return redirect()->to($target);
    }

    public function markRead(int $notification, InAppNotificationService $notifications): RedirectResponse
    {
        $notifications->markRead($notification);

        return back()->with('success', 'Notificación marcada como leída.');
    }

    public function markAllRead(InAppNotificationService $notifications): RedirectResponse
    {
        $notifications->markAllRead();

        return back()->with('success', 'Notificaciones marcadas como leídas.');
    }
}
