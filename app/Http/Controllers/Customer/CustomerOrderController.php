<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\OrderItem;
use App\Models\OrderMessage;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerOrderController extends Controller
{
    protected InventoryAutomationService $inventoryService;

    public function __construct(InventoryAutomationService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }


    /**
     * Customer Dashboard with order summary matching exact UI design.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        $unfilteredOrders = Order::where('user_id', $user->id)->get();
        $activeOrdersCount = $unfilteredOrders->whereIn('status', ['design_review', 'approved', 'printing', 'ready', 'shipped'])->count();
        $completedOrdersCount = $unfilteredOrders->whereIn('status', ['completed', 'cancelled'])->count();
        $pendingOrdersCount = $unfilteredOrders->where('status', 'pending')->count();
        $totalOrdersCount = $unfilteredOrders->count();

        // Build filtered orders query
        $query = Order::where('user_id', $user->id)
            ->with(['items.product', 'items.files', 'subOrders.items.product', 'reviews']);

        if ($search = trim($request->input('search'), " \t\n\r\0\x0B#")) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('items.product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($category = $request->input('category')) {
            if ($category !== 'all') {
                $query->whereHas('items.product', function ($pq) use ($category) {
                    $pq->where('category', $category);
                });
            }
        }

        if ($status = $request->input('status')) {
            if ($status === 'in_production') {
                $query->whereIn('status', ['design_review', 'approved', 'printing']);
            } elseif ($status === 'ready') {
                $query->whereIn('status', ['ready', 'shipped']);
            } elseif ($status === 'active') {
                // Matches the "Active Orders" KPI card set
                $query->whereIn('status', ['design_review', 'approved', 'printing', 'ready', 'shipped']);
            } elseif ($status === 'completed') {
                // Matches the "Completed" KPI card set — closed orders (done or cancelled)
                $query->whereIn('status', ['completed', 'cancelled']);
            } elseif ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $recentOrders = $query->latest()->paginate(5)->withQueryString();
        $allOrders = $recentOrders;

        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->toArray();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        $unreadNotificationsCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        // Items from completed orders the customer has not reviewed yet ("To Rate" section)
        $toRateItems = collect();
        $completedOrdersForReview = Order::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with(['items.product', 'reviews'])
            ->latest()
            ->get();
        foreach ($completedOrdersForReview as $completedOrder) {
            foreach ($completedOrder->items as $reviewItem) {
                if ($reviewItem->product_id && !$completedOrder->reviews->contains('product_id', $reviewItem->product_id)) {
                    $toRateItems->push(['order' => $completedOrder, 'item' => $reviewItem]);
                }
            }
        }

        return view('customer.dashboard', compact(
            'user',
            'allOrders',
            'recentOrders',
            'toRateItems',
            'activeOrdersCount',
            'completedOrdersCount',
            'pendingOrdersCount',
            'totalOrdersCount',
            'categories',
            'notifications',
            'unreadNotificationsCount'
        ));
    }

    /**
     * Customer Order Detail page with real-time tracking timeline, artwork feedback, and chat.
     */
    public function show($id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->with([
                'items.product',
                'items.files',
                'messages.sender',
                'payment',
                'payments',
                'subOrders.items.product',
                'subOrders.items.files',
                'subOrders.payment',
                'parentOrder.subOrders',
                'reviews',
            ])
            ->findOrFail($id);

        $unreadNotificationsCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('customer.order_detail', compact('order', 'unreadNotificationsCount'));
    }

    /**
     * Cancel an order requested by the customer (Allowed for pending / design_review).
     */
    public function cancelOrder(Request $request, $id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->with(['payment', 'items.product'])
            ->findOrFail($id);

        if (!$order->canBeCancelledByCustomer()) {
            $msg = "Order #{$order->order_number} cannot be cancelled because it is already in " . ucwords(str_replace('_', ' ', $order->status)) . " status.";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5', 'max:1000'],
            'refund_method' => ['nullable', 'string', 'max:255'],
        ], [
            'cancellation_reason.required' => 'Please provide a reason for cancelling your order.',
            'cancellation_reason.min' => 'The cancellation reason must be at least 5 characters.',
        ]);

        $reason = trim($request->input('cancellation_reason'));
        $preferredRefundMethod = $request->input('refund_method') 
            ?: ($order->payment ? $order->payment->payment_method : 'Original Payment Method');

        $isPaid = in_array($order->payment_status, ['paid', 'partially_paid']);
        $refundStatus = $isPaid ? 'in_progress' : 'not_applicable';
        $refundAmount = $isPaid ? $order->payable_now_amount : 0.00;

        // ── BOM stock handling on cancellation ──
        // Release the reserved raw materials so they return to available stock.
        // Customer cancellation is only allowed pre-approval (pending / design_review),
        // so the reservation has not yet been converted into a physical deduction.
        $postDeductionStatuses = ['approved', 'printing', 'ready', 'shipped', 'completed'];
        if (in_array($order->status, $postDeductionStatuses)) {
            $this->inventoryService->reverseOrderDeduction($order);
        } else {
            $this->inventoryService->releaseReservation($order);
        }

        $order->update([
            'status' => 'cancelled',
            'cancellation_status' => 'approved',
            'cancellation_reason' => $reason,
            'cancellation_requested_at' => now(),
            'cancelled_at' => now(),
            'payment_status' => 'cancelled',
            'refund_status' => $refundStatus,
            'refund_method' => $isPaid ? $preferredRefundMethod : null,
            'refund_amount' => $isPaid ? $refundAmount : null,
            'notes' => ($order->notes ? $order->notes . "\n" : "") . "Customer Cancellation: " . $reason,
        ]);

        // Post system message in order discussion thread
        OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'message' => "⛔ ORDER CANCELLED BY CUSTOMER.\nReason: {$reason}" . ($isPaid ? "\nRefund Status: Refund in progress (₱" . number_format($refundAmount, 2) . " via {$preferredRefundMethod})." : ""),
        ]);

        // Notify staff and super admins
        $staffAdmins = User::whereIn('role', ['admin', 'super_admin'])->get();
        foreach ($staffAdmins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'order_cancelled_by_customer',
                'message' => "Customer {$user->name} cancelled Order #{$order->order_number}." . ($isPaid ? " Refund required: ₱" . number_format($refundAmount, 2) : ""),
                'is_read' => false,
                'link' => route('admin.orders.show', $order->id),
            ]);
        }

        $successMsg = "Order #{$order->order_number} has been cancelled successfully." . ($isPaid ? " Your refund is now in progress." : "");

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'order_status' => 'cancelled',
                'refund_status' => $refundStatus,
            ]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Lightweight JSON status endpoint for real-time order tracker polling.
     * Used by the customer order detail page to auto-refresh when staff
     * updates the order status (approved / ready / completed, etc.).
     */
    public function status(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        return response()->json([
            'order_id'       => $order->id,
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
            'updated_at'     => optional($order->updated_at)->toIso8601String(),
        ]);
    }

    /**
     * Re-upload design artwork file if staff flagged "needs_revision".
     */
    public function reuploadFile(Request $request, $orderItemId)
    {
        $request->validate([
            'design_file' => ['required', 'file', 'mimes:jpeg,jpg,png,pdf,ai,psd,svg,zip', 'max:20480'],
        ]);

        $orderItem = OrderItem::with('order')->findOrFail($orderItemId);

        if ($orderItem->order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $filePath = $request->file('design_file')->store('order_files', 'public');

        OrderFile::create([
            'order_item_id' => $orderItem->id,
            'file_path' => $filePath,
            'uploaded_by' => Auth::id(),
            'status' => 'pending',
            'admin_reply' => 'Revised file submitted by customer. Awaiting staff review.',
        ]);

        $orderItem->order->update(['status' => 'design_review']);

        $staffAdmins = User::whereIn('role', ['admin', 'super_admin'])->get();
        foreach ($staffAdmins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'design_reuploaded',
                'message' => "Customer " . Auth::user()->name . " re-uploaded revised artwork for Order #" . $orderItem->order->order_number,
                'is_read' => false,
                'link' => route('admin.orders.show', $orderItem->order->id),
            ]);
        }

        return back()->with('success', 'Revised design artwork uploaded successfully! Staff has been notified.');
    }

    /**
     * Download / view printable invoice receipt.
     */
    public function invoice($id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->with(['items.product', 'user'])
            ->findOrFail($id);

        return view('customer.invoice', compact('order'));
    }

    /**
     * Send message in order-specific chat thread.
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => ['nullable', 'string', 'max:2000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:10240', 'required_without:message'],
        ]);

        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat_attachments', 'public');
        }

        $orderMessage = OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => $request->message ?: '',
            'attachment_path' => $attachmentPath,
        ]);

        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'order_chat',
                'message' => "New message on Order #{$order->order_number} from " . Auth::user()->name,
                'is_read' => false,
                'link' => route('admin.orders.show', $order->id),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Message sent to staff.',
                'data' => [
                    'id' => $orderMessage->id,
                    'message' => $orderMessage->message,
                    'user_id' => $orderMessage->user_id,
                    'sender_name' => Auth::user()->name,
                    'is_me' => true,
                    'formatted_time' => $orderMessage->created_at->timezone('Asia/Manila')->format('M d, h:i A'),
                    'attachment_url' => $orderMessage->attachment_path ? asset('storage/' . $orderMessage->attachment_path) : null,
                ]
            ]);
        }

        return back()->with('success', 'Message sent to staff.');
    }

    /**
     * Update customer profile info.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:25'],
            'contact' => ['nullable', 'string', 'regex:/^09\d{9}$/'],
            'region' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:500'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'contact.regex' => 'The phone number must be 11 digits starting with 09 (e.g. 09123456789).',
        ]);

        $contact = $request->contact ?: $request->phone;

        $fullAddressParts = array_filter([
            $request->street,
            $request->barangay,
            $request->city,
            $request->province,
            $request->region,
        ]);
        $fullAddress = implode(', ', $fullAddressParts);

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => ['image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
            ]);
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $fullName = trim(preg_replace('/\s+/', ' ', $request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name));
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->name = $fullName;
        $user->phone = $contact;
        $user->contact = $contact;
        $user->region = $request->region;
        $user->province = $request->province;
        $user->city = $request->city;
        $user->barangay = $request->barangay;
        $user->street = $request->street;
        $user->address = $fullAddress;
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Upload and update customer profile avatar (Instant AJAX or form).
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Delete previous avatar file if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar in avatars folder on public disk
        $avatarPath = $request->file('avatar')->store('avatars', 'public');

        $user->update([
            'avatar' => $avatarPath,
        ]);

        $avatarUrl = asset('storage/' . $avatarPath);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully!',
                'avatar_url' => $avatarUrl,
            ]);
        }

        return back()->with('success', 'Profile picture updated successfully!');
    }

    /**
     * Delete customer profile avatar and revert to default letter initials.
     */
    public function deleteAvatar(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update([
            'avatar' => null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile picture removed successfully.',
                'initials' => strtoupper(substr($user->name, 0, 1)),
            ]);
        }

        return back()->with('success', 'Profile picture removed.');
    }

    /**
     * Permanently delete the authenticated customer's account.
     * Requires current password confirmation for security.
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Please enter your current password to confirm account deletion.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Verify the provided password matches the account password
        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'delete_password' => 'The password you entered is incorrect. Account deletion was cancelled.',
            ])->with('show_delete_modal', true);
        }

        // Clean up avatar file from storage
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Log out and invalidate session before deleting
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Hard-delete the user record
        $user->delete();

        return redirect()->route('landing')->with('success', 'Your account has been permanently deleted. We are sorry to see you go.');
    }

    /**
     * Dedicated Customer Profile & Account Settings Panel.
     */
    public function profile()
    {
        $user = Auth::user();

        $unreadNotificationsCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $totalOrdersCount = Order::where('user_id', $user->id)->count();

        return view('customer.profile', compact('user', 'unreadNotificationsCount', 'totalOrdersCount'));
    }

    /**
     * Dedicated Customer Notifications Center Panel with filtering.
     */
    public function notifications(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter', 'all');

        $query = Notification::where('user_id', $user->id);

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'read') {
            $query->where('is_read', true);
        }

        $notifications = $query->latest()->paginate(5)->appends($request->query());

        $totalNotificationsCount = Notification::where('user_id', $user->id)->count();
        $unreadNotificationsCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        $readNotificationsCount = $totalNotificationsCount - $unreadNotificationsCount;

        return view('customer.notifications', compact(
            'user',
            'notifications',
            'unreadNotificationsCount',
            'totalNotificationsCount',
            'readNotificationsCount',
            'filter'
        ));
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markNotificationsRead(Request $request)
    {
        $user = Auth::user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.',
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Mark a single notification as read.
     */
    public function markSingleNotificationRead(Request $request, $id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);
        $notification->update(['is_read' => true]);

        $unreadCount = Notification::where('user_id', $user->id)->where('is_read', false)->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
                'unread_count' => $unreadCount,
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Delete a single notification.
     */
    public function destroyNotification(Request $request, $id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);
        $notification->delete();

        $unreadCount = Notification::where('user_id', $user->id)->where('is_read', false)->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted.',
                'unread_count' => $unreadCount,
            ]);
        }

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Clear all notifications for the customer.
     */
    public function clearAllNotifications(Request $request)
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications cleared.',
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications cleared.');
    }
}
