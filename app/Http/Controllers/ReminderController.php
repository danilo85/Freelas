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
}
