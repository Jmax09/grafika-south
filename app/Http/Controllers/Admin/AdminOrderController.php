<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\OrderMessage;
use App\Models\Payment;
use App\Services\InventoryAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusUpdatedMail;

class AdminOrderController extends Controller
{
    protected InventoryAutomationService $inventoryService;

    public function __construct(InventoryAutomationService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display listing of orders for operational staff.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'items.files', 'payment', 'payments'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('fulfillment_type')) {
            $query->where('fulfillment_type', $request->fulfillment_type);
        }

        // Order type: mirrors Order::getWorkflowTypeAttribute() (no dedicated column).
        if ($request->filled('order_type')) {
            if ($request->order_type === 'custom') {
                $query->where(function ($q) {
                    $q->where('is_custom_order', true)
                      ->orWhereIn('status', ['design_review', 'in_production', 'printing']);
                });
            } elseif ($request->order_type === 'regular') {
                $query->where('is_custom_order', false)
                      ->whereNotIn('status', ['design_review', 'in_production', 'printing']);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhere('walkin_first_name', 'like', "%{$search}%")
                  ->orWhere('walkin_last_name', 'like', "%{$search}%")
                  ->orWhere('walkin_email', 'like', "%{$search}%")
                  ->orWhere('walkin_contact_number', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display order detail page.
     */
    public function show($id)
    {
        $order = Order::with([
            'user',
            'items.product.materials',
            'items.files.uploader',
            'messages.sender',
            'materialLogs.material',
            'subOrders.items.product',
            'subOrders.items.files',
            'subOrders.messages.sender',
            'subOrders.payment',
            'subOrders.materialLogs.material',
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order status (Operational staff: pending -> design_review -> approved -> printing -> ready/shipped -> completed).
     * Automatically triggers raw material deduction on approval/printing transition!
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => ['required', 'in:pending,design_review,approved,printing,ready,shipped,completed,cancelled'],
            'payment_status' => ['nullable', 'in:unpaid,partially_paid,paid,refunded,cancelled'],
        ]);

        $previousStatus = $order->status;
        $newStatus = $request->status;

        // A cancelled order is closed for operations: only a Super Admin may reopen it.
        if ($previousStatus === 'cancelled' && $newStatus !== 'cancelled' && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', "Order #{$order->order_number} is cancelled — its status is locked. Only a Super Admin can reopen it.");
        }

        $updateData = ['status' => $newStatus];
        if ($request->filled('payment_status')) {
            $updateData['payment_status'] = $request->payment_status;
        }

        // Marking the payment as "refunded" here should settle the refund record too,
        // otherwise the customer still sees "Refund: None" beside a refunded payment.
        if ($request->payment_status === 'refunded' && $order->refund_status !== 'refunded') {
            $updateData['refund_status'] = 'refunded';
            $updateData['refunded_at'] = $order->refunded_at ?: now();
        }

        // Keep the outstanding balance consistent with the payment status so the order
        // reflects correctly under "Downpayment Balances" / "Uncollected total" — even
        // when its plan is "full" (which never carries a checkout downpayment balance):
        //   paid           → nothing owed
        //   partially_paid → assume a 50% downpayment was collected, half still owed
        if ($request->payment_status === 'paid') {
            $updateData['remaining_balance'] = 0;
        } elseif ($request->payment_status === 'partially_paid') {
            $orderTotal = (float) ($order->total_price ?: $order->total_amount);
            $updateData['remaining_balance'] = round($orderTotal * 0.5, 2);
        }

        $order->update($updateData);

        // ── BOM Deduction: convert reservation into actual deduction on approval ──
        $postDeductionStatuses = ['approved', 'printing', 'ready', 'shipped', 'completed'];
        $statusChanged = $previousStatus !== $newStatus;

        if ($statusChanged
            && in_array($newStatus, ['approved', 'printing', 'ready', 'shipped', 'completed'])
            && !in_array($previousStatus, $postDeductionStatuses)) {
            try {
                // Converts the pending order's reservation into a real stock
                // deduction (falls back to direct deduction for legacy orders).
                $this->inventoryService->convertReservationToDeduction($order);
            } catch (InsufficientStockException $e) {
                // Revert order status — deduction failed, transaction was rolled back
                $order->update(['status' => $previousStatus]);

                Log::warning("BOM deduction failed for Order #{$order->order_number}: " . $e->getMessage());

                $errorMessage = "Cannot approve Order #{$order->order_number} — insufficient raw material stock:\n" . $e->getSummary();

                return back()->with('error', $errorMessage);
            }
        }

        // ── BOM Reversal / Reservation Release on cancellation ──
        // Only on the actual move into cancelled — re-submitting the form on an
        // already cancelled order must not restore the same stock twice.
        if ($statusChanged && $newStatus === 'cancelled') {
            if (in_array($previousStatus, $postDeductionStatuses)) {
                // Order was already approved → restore the deducted physical stock.
                $this->inventoryService->reverseOrderDeduction($order);
            } else {
                // Order was still pending — simply release the stock reservation.
                $this->inventoryService->releaseReservation($order);
            }
        }

        // Send in-app notification to registered customer
        if ($statusChanged && $order->user_id) {
            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'order_status_change',
                'message' => "Your Order #{$order->order_number} status has been updated to: " . ucwords(str_replace('_', ' ', $newStatus)),
                'is_read' => false,
                'link' => route('customer.orders.show', $order->id),
            ]);
        }

        // Send automated email notification to customer (walk-in orders are served at the counter — no email)
        $recipientEmail = $statusChanged && !$order->is_walkin ? $order->customer_email : null;
        if (!empty($recipientEmail)) {
            try {
                // If a background queue worker isn't active or sync is requested, sendNow sends directly without waiting on worker
                $mailable = new OrderStatusUpdatedMail($order, $previousStatus, $newStatus);
                if (config('queue.default') === 'sync') {
                    Mail::to($recipientEmail)->send($mailable);
                } else {
                    // Send directly using sendNow or queue safely
                    Mail::to($recipientEmail)->sendNow($mailable);
                }
            } catch (\Throwable $e) {
                Log::error("Failed to send order status update email for Order #{$order->order_number}: " . $e->getMessage(), [
                    'order_id'       => $order->id,
                    'order_number'   => $order->order_number,
                    'customer_email' => $recipientEmail,
                    'new_status'     => $newStatus,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', $statusChanged
            ? "Order #{$order->order_number} status updated to " . ucwords(str_replace('_', ' ', $newStatus)) . " successfully!"
            : "Order #{$order->order_number} payment details updated successfully!");
    }

    /**
     * Staff-uploaded payment screenshot: lets admin/super admin attach the online
     * payment receipt when the customer paid out-of-band (walk-in counter, GCash
     * sent later, etc.). Creates the payment record too when none exists yet.
     */
    public function uploadProof(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'proof_of_payment' => ['required', 'file', 'mimes:jpeg,jpg,jfif,png,gif,webp,bmp', 'max:10240'],
        ], [
            'proof_of_payment.required' => 'Please choose a payment screenshot file to upload.',
        ]);

        $path = $request->file('proof_of_payment')->store('payment_proofs', 'public');

        // Replace any previously stored screenshot so storage does not leak old files.
        $oldPath = optional($order->payment)->proof_of_payment_path;

        $payment = $order->payment ?: new Payment([
            'order_id'       => $order->id,
            'payment_method' => $order->effective_payment_method,
            'amount'         => $order->total_price ?: $order->total_amount,
            'payment_plan'   => $order->is_downpayment ? 'downpayment' : 'full',
            'status'         => 'verified',
        ]);

        $payment->proof_of_payment_path = $path;
        // Staff is the uploader, so the receipt is treated as verified.
        $payment->status = 'verified';
        $payment->verified_by = Auth::id();
        $payment->save();

        if ($oldPath && $oldPath !== $path && \Storage::disk('public')->exists($oldPath)) {
            \Storage::disk('public')->delete($oldPath);
        }

        return back()->with('success', "Payment screenshot uploaded for Order #{$order->order_number}.");
    }

    /**
     * Review customer artwork file: send admin reply and set status (needs_revision or pending).
     * Note: Operational staff can set "needs_revision" or reply. Final print approval override is available to Super Admin.
     */
    public function reviewFile(Request $request, $fileId)
    {
        $file = OrderFile::with('orderItem.order')->findOrFail($fileId);

        $request->validate([
            'status' => ['required', 'in:pending,needs_revision,approved,rejected'],
            'admin_reply' => ['required', 'string', 'max:2000'],
        ]);

        // Operational staff cannot give final print approval unless allowed; rule enforced in UI and policy
        if ($request->status === 'approved' && Auth::user()->role === 'admin') {
            return back()->with('error', 'Operational Admin Staff cannot grant final print approval. Final approval is reserved for Super Admin.');
        }

        $file->update([
            'status' => $request->status,
            'admin_reply' => $request->admin_reply,
        ]);

        // If file needs revision, set order status to design_review
        if ($request->status === 'needs_revision') {
            $file->orderItem->order->update(['status' => 'design_review']);
        }

        // Notify customer (skip for walk-in orders)
        if ($file->orderItem->order->user_id) {
            Notification::create([
                'user_id' => $file->orderItem->order->user_id,
                'type' => 'design_review_feedback',
                'message' => "Staff feedback on artwork for Order #{$file->orderItem->order->order_number}: " . Str::limit($request->admin_reply, 100),
                'is_read' => false,
                'link' => route('customer.orders.show', $file->orderItem->order->id),
            ]);
        }

        return back()->with('success', 'Artwork review feedback saved and sent to customer.');
    }

    /**
     * Send message in order-specific chat.
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => ['nullable', 'string', 'max:2000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf,zip', 'max:10240'],
        ]);

        $order = Order::findOrFail($id);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat_attachments', 'public');
        }

        $orderMessage = OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'attachment_path' => $attachmentPath,
        ]);

        // Notify customer (skip for walk-in orders)
        if ($order->user_id) {
            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'order_chat',
                'message' => "Grafika South Staff replied to Order #{$order->order_number}",
                'is_read' => false,
                'link' => route('customer.orders.show', $order->id),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Message sent to customer.',
                'data' => [
                    'id' => $orderMessage->id,
                    'message' => $orderMessage->message,
                    'user_id' => $orderMessage->user_id,
                    'sender_name' => Auth::user()->name . ' (' . Auth::user()->role . ')',
                    'is_staff' => true,
                    'formatted_time' => $orderMessage->created_at->timezone('Asia/Manila')->format('M d, h:i A'),
                    'attachment_url' => $orderMessage->attachment_path ? asset('storage/' . $orderMessage->attachment_path) : null,
                ]
            ]);
        }

        return back()->with('success', 'Message sent to customer.');
    }

    /**
     * Download / view printable invoice receipt for operational staff.
     */
    public function invoice($id)
    {
        $order = Order::with(['items.product', 'user'])->findOrFail($id);

        return view('customer.invoice', compact('order'));
    }
}
