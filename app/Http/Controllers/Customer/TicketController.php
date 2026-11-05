<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Display a listing of customer tickets.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Ticket::with(['order', 'latestMessage'])->where('user_id', $user->id)->latest();

        if ($request->filled('status')) {
            if (in_array($request->status, ['pending', 'open'])) {
                $query->whereIn('status', ['pending', 'open']);
            } elseif (in_array($request->status, ['in_progress', 'resolved', 'closed'])) {
                $query->where('status', $request->status);
            }
        }

        $tickets = $query->paginate(5)->appends($request->query());

        $pendingCount = Ticket::where('user_id', $user->id)->whereIn('status', ['pending', 'open'])->count();

        $counts = [
            'all' => Ticket::where('user_id', $user->id)->count(),
            'pending' => $pendingCount,
            'open' => $pendingCount,
            'in_progress' => Ticket::where('user_id', $user->id)->where('status', 'in_progress')->count(),
            'resolved' => Ticket::where('user_id', $user->id)->where('status', 'resolved')->count(),
            'closed' => Ticket::where('user_id', $user->id)->where('status', 'closed')->count(),
        ];

        return view('customer.tickets.index', compact('tickets', 'counts'));
    }

    /**
     * Show the form for creating a new support ticket.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->latest()->get();
        $selectedOrderId = $request->query('order_id');

        return view('customer.tickets.create', compact('orders', 'selectedOrderId'));
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'order_id' => 'nullable|exists:orders,id',
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,mov,avi,webm,mkv,wmv,pdf,doc,docx,zip|max:51200',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,mov,avi,webm,mkv,wmv,pdf,doc,docx,zip|max:51200',
        ]);

        $user = Auth::user();

        // Verify order belongs to user if order_id is provided
        if ($request->filled('order_id')) {
            $orderExists = Order::where('id', $request->order_id)->where('user_id', $user->id)->exists();
            if (!$orderExists) {
                return back()->withInput()->with('error', 'The selected order does not belong to your account.');
            }
        }

        // Generate Ticket Number (e.g. TKT-20260825-A1B2)
        $datePrefix = date('Ymd');
        $randomCode = strtoupper(Str::random(4));
        $ticketNumber = 'TKT-' . $datePrefix . '-' . $randomCode;

        while (Ticket::where('ticket_number', $ticketNumber)->exists()) {
            $ticketNumber = 'TKT-' . $datePrefix . '-' . strtoupper(Str::random(4));
        }

        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $user->id,
            'order_id' => $request->order_id ?: null,
            'subject' => $request->subject,
            'category' => $request->category,
            // Urgency / priority is triaged by support staff in the Admin inbox
            'priority' => 'medium',
            'status' => 'pending',
            'last_replied_at' => now(),
        ]);

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
            'user_id' => $user->id,
            'message' => $request->message,
            'attachment_path' => $attachmentPath,
            'is_admin_reply' => false,
        ]);

        // Notify Admins about new ticket
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'new_support_ticket',
                'message' => "New Support Ticket #{$ticket->ticket_number} created by {$user->name}: {$ticket->subject}",
                'link' => route('admin.tickets.show', $ticket->id),
                'is_read' => false,
            ]);
        }

        return redirect()->route('customer.tickets.show', $ticket->id)
            ->with('success', "Support Ticket #{$ticket->ticket_number} created successfully. Our team will review your inquiry shortly.");
    }

    /**
     * Display the specified ticket discussion.
     */
    public function show($id)
    {
        $user = Auth::user();

        $ticket = Ticket::with(['order', 'messages.user'])->where('user_id', $user->id)->findOrFail($id);

        return view('customer.tickets.show', compact('ticket'));
    }

    /**
     * Submit a customer reply to the ticket.
     */
    public function reply(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::where('user_id', $user->id)->findOrFail($id);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'This ticket is closed. Please submit a new ticket if you need further assistance.');
        }

        $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,mov,avi,webm,mkv,wmv,pdf,doc,docx,zip|max:51200',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,mov,avi,webm,mkv,wmv,pdf,doc,docx,zip|max:51200',
        ]);

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
            'user_id' => $user->id,
            'message' => $request->message,
            'attachment_path' => $attachmentPath,
            'is_admin_reply' => false,
        ]);

        // Update ticket status back to pending and set last_replied_at
        $ticket->update([
            'status' => 'pending',
            'last_replied_at' => now(),
        ]);

        // Notify Admins of customer reply
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'ticket_customer_reply',
                'message' => "Customer {$user->name} replied to Ticket #{$ticket->ticket_number}",
                'link' => route('admin.tickets.show', $ticket->id),
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Your reply has been sent to our support team.');
    }
}
