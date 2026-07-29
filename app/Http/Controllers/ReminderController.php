<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reminder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReminderController extends Controller
{
    /**
     * Display a listing of the reminders.
     */
    public function index()
    {
        $userId = Auth::id();
        
        $reminders = Reminder::where('user_id', $userId)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('reminders.index', compact('reminders'));
    }

    /**
     * Store a newly created reminder in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'type' => 'nullable|string|in:text,list',
            'items' => 'nullable|array',
            'color' => 'nullable|string|max:100',
            'remind_at' => 'nullable|date',
            'image' => 'nullable|image|max:5120', // 5MB max
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reminders', 'public');
        }

        $items = $request->items;
        if (is_array($items)) {
            foreach ($items as &$item) {
                if (isset($item['checked'])) {
                    $item['checked'] = filter_var($item['checked'], FILTER_VALIDATE_BOOLEAN);
                }
            }
        }

        $reminder = Reminder::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type ?? 'text',
            'items' => $items,
            'color' => $request->color ?? 'bg-white border-slate-200 text-slate-700',
            'is_pinned' => $request->has('is_pinned') ? (bool)$request->is_pinned : false,
            'is_archived' => $request->has('is_archived') ? (bool)$request->is_archived : false,
            'remind_at' => $request->remind_at,
            'image_path' => $imagePath,
            'sort_order' => Reminder::where('user_id', Auth::id())->count() + 1
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lembrete criado com sucesso!',
                'reminder' => $reminder
            ]);
        }

        return redirect()->back()->with('success', 'Lembrete criado com sucesso!');
    }

    /**
     * Update the specified reminder in storage.
     */
    public function update(Request $request, Reminder $reminder)
    {
        if ($reminder->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'type' => 'nullable|string|in:text,list',
            'items' => 'nullable|array',
            'remind_at' => 'nullable|date',
            'image' => 'nullable|image|max:5120',
            'remove_image' => 'nullable|boolean'
        ]);

        $items = $request->items;
        if (is_array($items)) {
            foreach ($items as &$item) {
                if (isset($item['checked'])) {
                    $item['checked'] = filter_var($item['checked'], FILTER_VALIDATE_BOOLEAN);
                }
            }
        }

        $data = [
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type ?? $reminder->type,
            'items' => $items,
            'remind_at' => $request->remind_at,
        ];

        if ($request->remove_image && $reminder->image_path) {
            Storage::disk('public')->delete($reminder->image_path);
            $data['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($reminder->image_path) {
                Storage::disk('public')->delete($reminder->image_path);
            }
            $data['image_path'] = $request->file('image')->store('reminders', 'public');
        }

        $reminder->update($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lembrete atualizado com sucesso!',
                'reminder' => $reminder
            ]);
        }

        return redirect()->back()->with('success', 'Lembrete atualizado com sucesso!');
    }

    /**
     * Toggle the pinned status.
     */
    public function togglePin(Request $request, Reminder $reminder)
    {
        if ($reminder->user_id !== Auth::id()) {
            abort(403);
        }

        $reminder->is_pinned = !$reminder->is_pinned;
        $reminder->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_pinned' => $reminder->is_pinned,
                'message' => $reminder->is_pinned ? 'Lembrete fixado!' : 'Lembrete desafixado.'
            ]);
        }

        return redirect()->back();
    }

    /**
     * Toggle the archived status.
     */
    public function toggleArchive(Request $request, Reminder $reminder)
    {
        if ($reminder->user_id !== Auth::id()) {
            abort(403);
        }

        $reminder->is_archived = !$reminder->is_archived;
        $reminder->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_archived' => $reminder->is_archived,
                'message' => $reminder->is_archived ? 'Lembrete arquivado!' : 'Lembrete desarquivado.'
            ]);
        }

        return redirect()->back();
    }

    /**
     * Update the background color class of the note.
     */
    public function updateColor(Request $request, Reminder $reminder)
    {
        if ($reminder->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'color' => 'required|string|max:100',
        ]);

        $reminder->color = $request->color;
        $reminder->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'color' => $reminder->color,
                'message' => 'Cor atualizada!'
            ]);
        }

        return redirect()->back();
    }

    /**
     * Reorder reminders.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:reminders,id',
            'orders.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->orders as $order) {
            $reminder = Reminder::where('user_id', Auth::id())->find($order['id']);
            if ($reminder) {
                $reminder->sort_order = $order['sort_order'];
                $reminder->save();
            }
        }

        return response()->json(['success' => true, 'message' => 'Ordenação atualizada!']);
    }

    /**
     * Remove the specified reminder from storage.
     */
    public function destroy(Request $request, Reminder $reminder)
    {
        if ($reminder->user_id !== Auth::id()) {
            abort(403);
        }

        if ($reminder->image_path) {
            Storage::disk('public')->delete($reminder->image_path);
        }

        $reminder->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lembrete excluído com sucesso!'
            ]);
        }

        return redirect()->back()->with('success', 'Lembrete excluído com sucesso!');
    }

    /**
     * Get global notifications for the logged in user.
     */
    /**
     * Get global notifications for the logged in user.
     */
    public function getGlobalNotifications(Request $request)
    {
        $userId = Auth::id();
        
        // 1. Get persistent dismissed notification keys from DB
        $dismissedTitles = \App\Models\Notification::where('user_id', $userId)
            ->whereIn('type', ['bill_dismissed', 'reminder_dismissed'])
            ->pluck('title')
            ->toArray();

        // 2. Get unread database notifications (Proposal approvals, file downloads, contacts)
        $dbNotifications = \App\Models\Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->whereNotIn('type', ['bill_dismissed', 'reminder_dismissed'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Get active reminders not dismissed
        $activeReminders = Reminder::where('user_id', $userId)
            ->whereNotNull('remind_at')
            ->where('remind_at', '<=', now())
            ->where('is_archived', false)
            ->get()
            ->reject(function ($r) use ($dismissedTitles) {
                return in_array('reminder_' . $r->id, $dismissedTitles);
            });

        // 4. Get pending outbound transactions (bills) near expiry not dismissed
        $expiringBills = \App\Models\Transaction::where('user_id', $userId)
            ->where('type', 'saida')
            ->where('status', 'pendente')
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->get()
            ->reject(function ($b) use ($dismissedTitles) {
                return in_array('bill_' . $b->id, $dismissedTitles);
            });

        $notifications = [];

        // Add db notifications
        foreach ($dbNotifications as $dn) {
            $badge = '📢 Notificação';
            if ($dn->type === 'proposal') {
                $badge = '💼 Orçamento';
            } elseif ($dn->type === 'share') {
                $badge = '📂 Compartilhamento';
            } elseif ($dn->type === 'contact') {
                $badge = '✉️ Contato';
            } elseif ($dn->type === 'bill') {
                $badge = '💸 Financeiro';
            }

            $notifications[] = [
                'id' => 'db_' . $dn->id,
                'db_id' => $dn->id,
                'title' => $dn->title,
                'content' => $dn->content,
                'type' => $dn->type,
                'badge' => $badge
            ];
        }

        // Add reminders
        foreach ($activeReminders as $r) {
            $notifications[] = [
                'id' => 'reminder_' . $r->id,
                'reminder_id' => $r->id,
                'title' => $r->title ?: 'Lembrete',
                'content' => $r->content ?: 'Prazo ou alarme definido atingido.',
                'type' => 'reminder',
                'badge' => '⏰ Lembrete'
            ];
        }

        // Add bills
        foreach ($expiringBills as $b) {
            $formattedDate = $b->due_date ? $b->due_date->format('d/m/Y') : '';
            $notifications[] = [
                'id' => 'bill_' . $b->id,
                'bill_id' => $b->id,
                'title' => 'Conta a Vencer',
                'content' => "A conta '" . $b->description . "' de R$ " . number_format($b->amount, 2, ',', '.') . " vence em " . $formattedDate . ".",
                'type' => 'bill',
                'badge' => '💸 Financeiro'
            ];
        }

        return response()->json($notifications);
    }

    /**
     * Mark notification as read / dismissed persistently in DB.
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        $userId = Auth::id();

        if (str_starts_with($id, 'db_')) {
            $dbId = (int) substr($id, 3);
            $dn = \App\Models\Notification::where('user_id', $userId)->find($dbId);
            if ($dn) {
                $dn->update(['read_at' => now()]);
            }
        } elseif (str_starts_with($id, 'reminder_')) {
            \App\Models\Notification::firstOrCreate([
                'user_id' => $userId,
                'type' => 'reminder_dismissed',
                'title' => $id,
            ], [
                'content' => 'Lembrete dispensado',
                'read_at' => now(),
            ]);
        } elseif (str_starts_with($id, 'bill_')) {
            \App\Models\Notification::firstOrCreate([
                'user_id' => $userId,
                'type' => 'bill_dismissed',
                'title' => $id,
            ], [
                'content' => 'Conta dispensada',
                'read_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
