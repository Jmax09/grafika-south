<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'parent_id',
        'is_parent',
        'status',
        'cancellation_status',
        'cancellation_reason',
        'cancellation_requested_at',
        'cancelled_at',
        'cancellation_admin_notes',
        'payment_status',
        'refund_status',
        'refund_method',
        'refund_reference',
        'refund_amount',
        'refunded_at',
        'payment_plan',
        'downpayment_amount',
        'remaining_balance',
        'is_custom_order',
        'custom_terms_agreed',
        'total_price',
        'total_amount',
        'notes',
        'design_file_path',
        'is_walkin',
        'fulfillment_type',
        'estimated_production_minutes',
        'walkin_first_name',
        'walkin_last_name',
        'walkin_email',
        'walkin_contact_number',
        'walkin_address',
    ];

    protected $casts = [
        'is_parent' => 'boolean',
        'total_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'downpayment_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'is_custom_order' => 'boolean',
        'custom_terms_agreed' => 'boolean',
        'estimated_production_minutes' => 'integer',
        'cancellation_requested_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function getFormattedProductionTimeAttribute(): ?string
    {
        if ($this->estimated_production_minutes === null || $this->estimated_production_minutes <= 0) {
            return null;
        }
        return Product::formatMinutes($this->estimated_production_minutes);
    }


    public function getIsDownpaymentAttribute(): bool
    {
        return $this->payment_plan === 'downpayment';
    }

    public function getFormattedPaymentPlanAttribute(): string
    {
        return $this->payment_plan === 'downpayment' ? '50% Downpayment' : 'Full Payment (100%)';
    }

    public function getPayableNowAmountAttribute(): float
    {
        return $this->is_downpayment ? (float) $this->downpayment_amount : (float) ($this->total_price ?: $this->total_amount);
    }

    public function getPaymentProofPathAttribute(): ?string
    {
        if ($this->payment && !empty($this->payment->proof_of_payment_path)) {
            return $this->payment->proof_of_payment_path;
        }

        if ($this->subOrders && $this->subOrders->isNotEmpty()) {
            foreach ($this->subOrders as $sub) {
                if ($sub->payment && !empty($sub->payment->proof_of_payment_path)) {
                    return $sub->payment->proof_of_payment_path;
                }
            }
        }

        if ($this->parentOrder && $this->parentOrder->payment && !empty($this->parentOrder->payment->proof_of_payment_path)) {
            return $this->parentOrder->payment->proof_of_payment_path;
        }

        return null;
    }

    public function getEffectivePaymentMethodAttribute(): string
    {
        if ($this->payment && !empty($this->payment->payment_method)) {
            return $this->payment->payment_method;
        }

        if ($this->subOrders && $this->subOrders->isNotEmpty()) {
            $methods = [];
            foreach ($this->subOrders as $sub) {
                if ($sub->payment && !empty($sub->payment->payment_method)) {
                    $methods[] = $sub->payment->payment_method;
                }
            }
            if (!empty($methods)) {
                return implode(' + ', array_unique($methods));
            }
        }

        if ($this->parentOrder && $this->parentOrder->payment && !empty($this->parentOrder->payment->payment_method)) {
            return $this->parentOrder->payment->payment_method;
        }

        return 'GCash / Cash';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_id');
    }

    public function subOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'parent_id');
    }

    public function isParent(): bool
    {
        return (bool) $this->is_parent;
    }

    public function isSubOrder(): bool
    {
        return !is_null($this->parent_id);
    }

    public function hasSubOrders(): bool
    {
        return $this->subOrders()->exists();
    }

    /**
     * Determine workflow type: 'regular' vs 'custom'.
     * Determined by the ORDER TYPE (is_custom_order), never the current status —
     * 'approved', 'printing', etc. are shared stages of both workflows.
     * Only 'design_review' is exclusively a custom-order phase.
     */
    public function getWorkflowTypeAttribute(): string
    {
        if ($this->is_custom_order || in_array($this->status, ['design_review', 'in_production', 'printing'])) {
            return 'custom';
        }
        return 'regular';
    }


    /**
     * Get independent status workflow stages.
     * Regular: 3 stages (Pending -> Ready for Pickup / Out for Delivery -> Completed)
     * Custom: 5 stages (Pending Payment Verification -> Design Review -> Production / Printing -> Ready to Ship / Out for Delivery -> Completed)
     */
    public function getWorkflowStagesAttribute(): array
    {
        $isDelivery = $this->fulfillment_type === 'delivery';

        if ($this->workflow_type === 'regular') {
            return [
                'pending' => [
                    'step' => 1,
                    'key' => 'pending',
                    'label' => 'Order Placed',
                    'badge' => 'Pending',
                    'desc' => 'Order received and queued for fulfillment.',
                ],
                'ready' => [
                    'step' => 2,
                    'key' => 'ready',
                    'label' => $isDelivery ? 'Out for Delivery' : 'Ready for Pickup',
                    'badge' => $isDelivery ? 'Out for Delivery' : 'Ready for Pickup',
                    'desc' => $isDelivery ? 'Dispatched via courier to your address.' : 'Packed and ready for pickup at our San Pedro store.',
                ],
                'completed' => [
                    'step' => 3,
                    'key' => 'completed',
                    'label' => 'Completed',
                    'badge' => 'Completed',
                    'desc' => 'Order fulfilled and received by customer.',
                ],
            ];
        }

        return [
            'pending' => [
                'step' => 1,
                'key' => 'pending',
                'label' => 'Payment Verification',
                'badge' => 'Pending Verification',
                'desc' => 'Verifying downpayment or full payment proof.',
            ],
            'design_review' => [
                'step' => 2,
                'key' => 'design_review',
                'label' => 'Design Review',
                'badge' => 'Design Review',
                'desc' => 'Graphic artist inspecting artwork resolution & CMYK plates.',
            ],
            'production' => [
                'step' => 3,
                'key' => 'production',
                'label' => 'Printing',
                'badge' => 'Production / Printing',
                'desc' => 'Printing, cutting, or fabrication in progress.',
            ],
            'ready' => [
                'step' => 4,
                'key' => 'ready',
                'label' => $isDelivery ? 'Ready to Ship' : 'Ready for Pickup',
                'badge' => $isDelivery ? 'Ready to Ship' : 'Ready for Pickup',
                'desc' => $isDelivery ? 'Quality inspected and prepared for courier dispatch.' : 'Quality inspected and ready for store collection.',
            ],
            'completed' => [
                'step' => 5,
                'key' => 'completed',
                'label' => 'Completed',
                'badge' => 'Completed',
                'desc' => 'Delivered and custom balance settled.',
            ],
        ];
    }

    /**
     * Map current order status to workflow step index (1-based).
     */
    public function getCurrentWorkflowStepAttribute(): int
    {
        if ($this->status === 'cancelled') {
            return -1;
        }

        if ($this->workflow_type === 'regular') {
            return match ($this->status) {
                'ready', 'ready_for_pickup', 'shipped' => 2,
                'completed' => 3,
                default => 1,
            };
        }

        return match ($this->status) {
            'design_review' => 2,
            'printing', 'in_production' => 3,
            'ready', 'ready_for_pickup', 'shipped' => 4,
            'completed' => 5,
            default => 1,
        };
    }


    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class)->oldest();
    }

    public function files(): HasManyThrough
    {
        return $this->hasManyThrough(OrderFile::class, OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function materialLogs(): HasMany
    {
        return $this->hasMany(MaterialLog::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getFormattedStatusAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'design_review' => 'bg-purple-100 text-purple-800 border-purple-300',
            'approved' => 'bg-blue-100 text-blue-800 border-blue-300',
            'printing' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'ready', 'shipped' => 'bg-teal-100 text-teal-800 border-teal-300',
            'completed' => 'bg-green-100 text-green-800 border-green-300',
            'cancelled' => 'bg-red-100 text-red-800 border-red-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    public function getCustomerNameAttribute(): string
    {
        if ($this->is_walkin) {
            return trim($this->walkin_first_name . ' ' . $this->walkin_last_name) ?: 'Walk-in Customer';
        }
        return $this->user ? $this->user->name : 'Unknown Customer';
    }

    public function getCustomerEmailAttribute(): ?string
    {
        if ($this->is_walkin) {
            return $this->walkin_email;
        }
        return $this->user ? $this->user->email : null;
    }

    public function getCustomerContactAttribute(): ?string
    {
        if ($this->is_walkin) {
            return $this->walkin_contact_number;
        }
        return $this->user ? ($this->user->contact ?: $this->user->phone) : null;
    }

    public function getCustomerAddressAttribute(): ?string
    {
        if ($this->is_walkin) {
            return $this->walkin_address;
        }
        return $this->user ? $this->user->address : null;
    }

    /**
     * Check if customer can cancel this order.
     * Allowed only in early stages: pending and design_review before production/approval begins.
     */
    public function canBeCancelledByCustomer(): bool
    {
        if ($this->status === 'cancelled') {
            return false;
        }

        return in_array($this->status, ['pending', 'design_review']);
    }

    /**
     * Check if order is in a cancellation or refund state.
     */
    public function isCancellationActive(): bool
    {
        return $this->status === 'cancelled'
            || in_array($this->cancellation_status, ['requested', 'under_review', 'approved'])
            || in_array($this->refund_status, ['in_progress', 'refunded']);
    }
    
    /**
     * An order may only be permanently deleted once it is settled/closed:
     * completed or cancelled, OR fully paid or refunded. In-progress or
     * partially paid orders must not be deletable.
     */
    public function canBeDeleted(): bool
    {
        return in_array($this->status, ['completed', 'cancelled'], true)
            || in_array($this->payment_status, ['paid', 'refunded'], true);
    }

    /**
     * Formatted refund status string.
     */
    public function getFormattedRefundStatusAttribute(): string
    {
        return match ($this->refund_status) {
            'in_progress' => 'Refund in Progress',
            'refunded' => $this->refund_method ? "Refunded via {$this->refund_method}" : 'Refunded',
            'rejected' => 'Refund Rejected',
            'not_applicable' => 'No Refund Required (Unpaid)',
            default => 'None',
        };
    }

    /**
     * Refund status badge CSS class.
     */
    public function getRefundStatusBadgeClassAttribute(): string
    {
        return match ($this->refund_status) {
            'in_progress' => 'bg-amber-100 text-amber-800 border-amber-300',
            'refunded' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'rejected' => 'bg-red-100 text-red-800 border-red-300',
            'not_applicable' => 'bg-slate-100 text-slate-700 border-slate-300',
            default => 'bg-gray-100 text-gray-700 border-gray-300',
        };
    }
}
