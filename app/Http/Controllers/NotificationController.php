<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->whereNotIn('type', ['bill_dismissed', 'reminder_dismissed'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Auto mark all unread notifications as read when user visits this log page
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->whereNotIn('type', ['bill_dismissed', 'reminder_dismissed'])
            ->update(['read_at' => now()]);

        return view('notifications.index', compact('notifications'));
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->whereNotIn('type', ['bill_dismissed', 'reminder_dismissed'])
            ->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }

    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->delete();

        return redirect()->back()->with('success', 'Notificação excluída com sucesso.');
    }

    public function destroyAll()
    {
        Notification::where('user_id', Auth::id())
            ->whereNotIn('type', ['bill_dismissed', 'reminder_dismissed'])
            ->delete();

        return redirect()->back()->with('success', 'Histórico de notificações limpo.');
    }
}
