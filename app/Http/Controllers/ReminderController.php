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
    public function getGlobalNotifications(Request $request)
    {
        $userId = Auth::id();
        
        // 1. Get database notifications (Proposal approvals, file downloads)
        $notifiedDbIds = session('notified_db_ids', []);
        $dbNotifications = \App\Models\Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->whereNotIn('id', $notifiedDbIds)
            ->orderBy('created_at', 'desc')
            ->get();

        // Save that we have notified the user about these DB IDs in this session
        if ($dbNotifications->count() > 0) {
            $notifiedDbIds = array_merge($notifiedDbIds, $dbNotifications->pluck('id')->toArray());
            session(['notified_db_ids' => $notifiedDbIds]);
        }

        // 2. Get active reminders (whose remind_at is past and not archived, checked against session notifications)
        $notifiedReminderIds = session('notified_reminder_ids', []);
        $activeReminders = Reminder::where('user_id', $userId)
            ->whereNotNull('remind_at')
            ->where('remind_at', '<=', now())
            ->where('is_archived', false)
            ->whereNotIn('id', $notifiedReminderIds)
            ->get();

        // 3. Get pending outbound transactions (bills) near expiry (due date today or in next 3 days)
        $notifiedBillIds = session('notified_bill_ids', []);
        $expiringBills = \App\Models\Transaction::where('user_id', $userId)
            ->where('type', 'saida')
            ->where('status', 'pendente')
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->whereNotIn('id', $notifiedBillIds)
            ->get();

        $notifications = [];

        // Add db notifications
        foreach ($dbNotifications as $dn) {
            $notifications[] = [
                'id' => 'db_' . $dn->id,
                'db_id' => $dn->id,
                'title' => $dn->title,
                'content' => $dn->content,
                'type' => $dn->type,
                'badge' => $dn->type === 'proposal' ? '💼 Orçamento' : '📂 Compartilhamento'
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
     * Mark notification as read / dismissed.
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        $userId = Auth::id();

        if (str_starts_with($id, 'db_')) {
            $dbId = substr($id, 3);
            $dn = \App\Models\Notification::where('user_id', $userId)->find($dbId);
            if ($dn) {
                $dn->update(['read_at' => now()]);
            }
        } elseif (str_starts_with($id, 'reminder_')) {
            $reminderId = (int) substr($id, 9);
            $notifiedReminderIds = session('notified_reminder_ids', []);
            $notifiedReminderIds[] = $reminderId;
            session(['notified_reminder_ids' => $notifiedReminderIds]);
        } elseif (str_starts_with($id, 'bill_')) {
            $billId = (int) substr($id, 5);
            $notifiedBillIds = session('notified_bill_ids', []);
            $notifiedBillIds[] = $billId;
            session(['notified_bill_ids' => $notifiedBillIds]);
        }

        return response()->json(['success' => true]);
    }
}
