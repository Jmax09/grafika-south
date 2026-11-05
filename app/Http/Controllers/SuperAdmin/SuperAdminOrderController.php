<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderFile;
use App\Services\InventoryAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\OrderStatusUpdatedMail;

class SuperAdminOrderController extends Controller
{
    protected InventoryAutomationService $inventoryService;

    public function __construct(InventoryAutomationService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display all orders for Super Admin.
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
        return view('super_admin.orders.index', compact('orders'));
    }

    /**
     * Give final print approval/rejection on customer design artwork (Super Admin Override).
     */
    public function finalApproveFile(Request $request, $fileId)
    {
        $file = OrderFile::with('orderItem.order')->findOrFail($fileId);

        $request->validate([
            'status' => ['required', 'in:approved,rejected,needs_revision,pending'],
            'admin_reply' => ['nullable', 'string', 'max:2000'],
        ]);

        $file->update([
            'status' => $request->status,
            'admin_reply' => $request->admin_reply ?: "Final approval granted by Super Admin.",
        ]);

        $order = $file->orderItem->order;

        if ($request->status === 'approved') {
            $prev = $order->status;
            // Check if all files in the order are approved
            $order->update(['status' => 'approved']);
            // Convert the pending order's stock reservation into an actual deduction.
            // If the shelf cannot cover it, the deduction is rolled back and the
            // order stays unapproved — stock can never go negative.
            try {
                $this->inventoryService->convertReservationToDeduction($order);
            } catch (\App\Exceptions\InsufficientStockException $e) {
                $order->update(['status' => $prev]);
                $file->update(['status' => 'pending']);

                Log::warning("BOM deduction failed for Order #{$order->order_number}: " . $e->getMessage());

                return back()->with(
                    'error',
                    "Cannot approve Order #{$order->order_number} — insufficient raw material stock:\n" . $e->getSummary()
                );
            }

            // Send automated email notification to customer (walk-in orders get no email)
            $recipientEmail = $order->is_walkin ? null : $order->customer_email;
            if (!empty($recipientEmail)) {
                try {
                    Mail::to($recipientEmail)->queue(new OrderStatusUpdatedMail($order, $prev, 'approved'));
                } catch (\Throwable $e) {
                    Log::warning("Failed to queue approved email for Order #{$order->order_number}: " . $e->getMessage());
                }
            }
        }

        // Notify customer (skip for walk-in orders)
        if ($order->user_id) {
            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'final_design_approval',
                'message' => "Super Admin gave final {$request->status} status for Order #{$order->order_number} artwork design.",
                'is_read' => false,
                'link' => route('customer.orders.show', $order->id),
            ]);
        }

        return back()->with('success', "Final artwork status updated to '{$request->status}' for Order #{$order->order_number}.");
    }

    /**
     * Process order cancellation / refund.
     */
    public function processRefund(Request $request, $id)
    {
        $order = Order::with('payment')->findOrFail($id);
        $previousStatus = $order->status;

        $request->validate([
            'action' => ['required', 'in:cancel,refund'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'refund_method' => ['nullable', 'string', 'max:255'],
            'refund_reference' => ['nullable', 'string', 'max:255'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        // ── BOM stock handling on cancellation ──
        if (in_array($previousStatus, ['approved', 'printing', 'ready', 'shipped', 'completed'])) {
            // Order was already approved → restore the deducted physical stock.
            $this->inventoryService->reverseOrderDeduction($order);
        } else {
            // Order was still pending — simply release the stock reservation.
            $this->inventoryService->releaseReservation($order);
        }

        if ($request->action === 'refund') {
            $refundMethod = $request->input('refund_method') ?: ($order->refund_method ?: ($order->payment ? $order->payment->payment_method : 'Original Payment Method'));
            $refundAmount = $request->filled('refund_amount') ? (float) $request->input('refund_amount') : ($order->refund_amount ?: (float) $order->payable_now_amount);

            $order->update([
                'status' => 'cancelled',
                'cancellation_status' => 'approved',
                'cancelled_at' => $order->cancelled_at ?: now(),
                'payment_status' => 'refunded',
                'refund_status' => 'refunded',
                'refund_method' => $refundMethod,
                'refund_reference' => $request->input('refund_reference'),
                'refund_amount' => $refundAmount,
                'refunded_at' => now(),
                'cancellation_admin_notes' => $request->notes,
                'notes' => ($order->notes ? $order->notes . " | " : "") . "Refund Processed: " . ($request->notes ?? "Full refund of ₱" . number_format($refundAmount, 2) . " issued via {$refundMethod}."),
            ]);
            $msg = "Order #{$order->order_number} marked as Refunded (₱" . number_format($refundAmount, 2) . " via {$refundMethod}) and Cancelled.";
        } else {
            // Read the payment state before it is closed out as "cancelled".
            $refundStatus = in_array($order->payment_status, ['paid', 'partially_paid']) ? 'in_progress' : 'not_applicable';

            $order->update([
                'status' => 'cancelled',
                'cancellation_status' => 'approved',
                'cancelled_at' => $order->cancelled_at ?: now(),
                'payment_status' => 'cancelled',
                'refund_status' => $refundStatus,
                'cancellation_admin_notes' => $request->notes,
                'notes' => ($order->notes ? $order->notes . " | " : "") . "Cancelled by Super Admin: " . ($request->notes ?? ''),
            ]);
            $msg = "Order #{$order->order_number} Cancelled.";
        }

        // Notify customer (skip for walk-in orders)
        if ($order->user_id) {
            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'order_cancellation',
                'message' => $msg,
                'is_read' => false,
                'link' => route('customer.orders.show', $order->id),
            ]);
        }

        // Send automated cancellation email to customer (walk-in orders get no email)
        $recipientEmail = $order->is_walkin ? null : $order->customer_email;
        if (!empty($recipientEmail)) {
            try {
                Mail::to($recipientEmail)->queue(new OrderStatusUpdatedMail($order, $previousStatus, 'cancelled'));
            } catch (\Throwable $e) {
                Log::warning("Failed to queue cancellation email for Order #{$order->order_number}: " . $e->getMessage());
            }
        }

        return back()->with('success', $msg);
    }

    /**
     * Full Order Delete (Super Admin only).
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        // Guard: only settled/closed orders (completed, cancelled, fully paid, or refunded)
        // may be permanently deleted. In-progress or partially paid orders are protected.
        if (!$order->canBeDeleted()) {
            return redirect()->route('super-admin.orders.index')
                ->with('error', "Order #{$order->order_number} cannot be deleted. Only completed, cancelled, paid, or refunded orders can be deleted.");
        }

        $orderNumber = $order->order_number;
        $order->delete();

        return redirect()->route('super-admin.orders.index')
            ->with('success', "Order #{$orderNumber} deleted permanently.");
    }
}
