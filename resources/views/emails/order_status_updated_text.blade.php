GRAFIKA SOUTH - ORDER STATUS UPDATE

Hello {{ $order->customer_name }},

Your {{ $order->isSubOrder() ? 'sub-order' : 'order' }} status has been updated!

{{ $order->isSubOrder() ? 'SUB-ORDER' : 'ORDER' }} NUMBER: #{{ $order->order_number }}
CURRENT STATUS: {{ ucwords(str_replace('_', ' ', $newStatus)) }}
FULFILLMENT TYPE: {{ $order->fulfillment_type === 'delivery' ? 'Delivery' : 'Store Pickup' }}
PAYMENT STATUS: {{ ucwords(str_replace('_', ' ', $order->payment_status)) }}
