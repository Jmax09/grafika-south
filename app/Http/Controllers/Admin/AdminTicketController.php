<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTicketController extends Controller
{
    /**
     * Display a listing of support tickets in Admin Inbox.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'order', 'latestMessage'])->latest('last_replied_at');

        if ($request->filled('status')) {
            if (in_array($request->status, ['pending', 'open'])) {
                $query->whereIn('status', ['pending', 'open']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->paginate(15);

        $pendingCount = Ticket::whereIn('status', ['pending', 'open'])->count();

        $counts = [
            'total' => Ticket::count(),
            'pending' => $pendingCount,
            'open' => $pendingCount,
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'resolved' => Ticket::where('status', 'resolved')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
        ];

        return view('admin.tickets.index', compact('tickets', 'counts'));
    }

    /**
     * Display detailed ticket view in Admin Inbox.
     */
    public function show($id)
    {
        $ticket = Ticket::with(['user', 'order.items.product', 'messages.user'])->findOrFail($id);

        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Post a staff reply to the customer ticket.
     */
    public function reply(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'message' => 'required|string',
            'status' => 'nullable|in:pending,open,in_progress,resolved,closed',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,mov,avi,webm,mkv,wmv,pdf,doc,docx,zip|max:51200',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,mov,avi,webm,mkv,wmv,pdf,doc,docx,zip|max:51200',
        ]);

        $admin = Auth::user();

        $storedPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $storedPaths[] = $file->store('ticket_attachments', 'public');
                }
            }
        } elseif ($request->hasFile('attachment')) {
            if ($request->file('attachment')->isValid()) {
                $storedPaths[] = $request->file('attachment')->store('ticket_attachments', 'public');
            }
        }

        $attachmentPath = null;
        if (!empty($storedPaths)) {
            $attachmentPath = count($storedPaths) === 1 ? $storedPaths[0] : json_encode($storedPaths);
        }

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'message' => $request->message,
            'attachment_path' => $attachmentPath,
            'is_admin_reply' => true,
        ]);

        // Default to in_progress if currently pending/open and admin replies
        $newStatus = $request->status ?: (($ticket->status === 'pending' || $ticket->status === 'open') ? 'in_progress' : $ticket->status);

        $ticket->update([
            'status' => $newStatus,
            'last_replied_at' => now(),
        ]);

        // Notify customer
        Notification::create([
            'user_id' => $ticket->user_id,
            'type' => 'ticket_reply',
            'message' => "Grafika Support replied to your Ticket #{$ticket->ticket_number}: {$ticket->subject}",
            'link' => route('customer.tickets.show', $ticket->id),
            'is_read' => false,
        ]);

        return back()->with('success', 'Staff reply posted successfully and customer has been notified.');
    }

    /**
     * Update ticket status and priority (priority is staff-assigned only).
     */
    public function updateStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,open,in_progress,resolved,closed',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $oldStatus = $ticket->status;
        $oldPriority = $ticket->priority;

        $ticket->update([
            'status' => $request->status,
            'priority' => $request->priority,
        ]);

        if ($oldStatus !== $request->status) {
            $formattedStatus = ucwords(str_replace('_', ' ', $request->status));
            Notification::create([
                'user_id' => $ticket->user_id,
                'type' => 'ticket_status_update',
                'message' => "Your Support Ticket #{$ticket->ticket_number} status was updated to '{$formattedStatus}'",
                'link' => route('customer.tickets.show', $ticket->id),
                'is_read' => false,
            ]);
        }

        $changed = [];
        if ($oldStatus !== $request->status) {
            $changed[] = 'status';
        }
        if ($oldPriority !== $request->priority) {
            $changed[] = 'priority';
        }

        $message = empty($changed)
            ? 'No changes were saved.'
            : 'Ticket ' . implode(' and ', $changed) . ' updated successfully.';

        return back()->with('success', $message);
    }
}
