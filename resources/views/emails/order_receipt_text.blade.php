GRAFIKA SOUTH - ORDER RECEIPT

Hello {{ $customerName }},

Thank you for your order! We have received your order and started processing it.

RECEIPT DETAILS:
Order Tracking #: #{{ $orderNumber }}
Order Date: {{ $order->created_at->format('F d, Y h:i A') }}
Payment Method: {{ $paymentMethod }}
Payment Plan: {{ $order->formatted_payment_plan }}
Fulfillment: {{ $order->fulfillment_type === 'delivery' ? 'Delivery' : 'Store Pickup' }}
Billing/Delivery Address: {{ $billingAddress }}

PURCHASED ITEMS:
@foreach($order->items as $item)
- {{ $item->quantity }}x {{ $item->product ? $item->product->name : 'Custom Item' }} (₱{{ number_format($item->unit_price, 2) }}) = ₱{{ number_format($item->subtotal ?: ($item->unit_price * $item->quantity), 2) }}
@if(!empty($item->options_selected))
  Options: @foreach($item->options_selected as $optName => $optVal){{ $optName }}: {{ $optVal }}; @endforeach

@endif
@endforeach

Total Order Value: ₱{{ number_format($totalAmount, 2) }}
@if($order->is_downpayment)
50% Downpayment (Payable Now / Paid): ₱{{ number_format($order->downpayment_amount, 2) }}
Remaining Balance (Due upon Collection): ₱{{ number_format($order->remaining_balance, 2) }}
@endif
