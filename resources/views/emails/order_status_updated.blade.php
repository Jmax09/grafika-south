<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Order Status Update — Grafika South</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F0F4F8; font-family: 'Segoe UI', Arial, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">

    @php
        $statusColors = [
            'pending'       => ['bg' => '#FEF9C3', 'border' => '#FDE047', 'text' => '#854D0E', 'badge' => '#CA8A04'],
            'design_review' => ['bg' => '#EDE9FE', 'border' => '#DDD6FE', 'text' => '#5B21B6', 'badge' => '#7C3AED'],
            'approved'      => ['bg' => '#DBEAFE', 'border' => '#BFDBFE', 'text' => '#1D4ED8', 'badge' => '#2563EB'],
            'printing'      => ['bg' => '#E0F2FE', 'border' => '#BAE6FD', 'text' => '#0369A1', 'badge' => '#0099FF'],
            'ready'         => ['bg' => '#D1FAE5', 'border' => '#A7F3D0', 'text' => '#065F46', 'badge' => '#10B981'],
            'shipped'       => ['bg' => '#CCFBF1', 'border' => '#99F6E4', 'text' => '#0F766E', 'badge' => '#14B8A6'],
            'completed'     => ['bg' => '#DCFCE7', 'border' => '#BBF7D0', 'text' => '#14532D', 'badge' => '#16A34A'],
            'cancelled'     => ['bg' => '#FEE2E2', 'border' => '#FECACA', 'text' => '#991B1B', 'badge' => '#EF4444'],
        ];

        $sc = $statusColors[$newStatus] ?? ['bg' => '#F1F5F9', 'border' => '#E2E8F0', 'text' => '#334155', 'badge' => '#64748B'];

        $statusHumanNames = [
            'pending'       => 'Pending',
            'design_review' => 'Design Review',
            'approved'      => 'Approved for Production',
            'printing'      => 'Printing in Progress',
            'ready'         => $order->fulfillment_type === 'delivery' ? 'Ready for Delivery' : 'Ready for Pickup',
            'shipped'       => 'Shipped / Out for Delivery',
            'completed'     => 'Completed',
            'cancelled'     => 'Cancelled',
        ];
        $displayStatus = $statusHumanNames[$newStatus] ?? ucwords(str_replace('_', ' ', $newStatus));
        $isSubOrder = $order->isSubOrder();
        $orderLabel = $isSubOrder ? 'Sub-Order' : 'Order';
    @endphp

    <!-- Outer wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color: #F0F4F8; padding: 40px 16px;">
        <tr>
            <td align="center">

                <!-- Email card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width: 580px; background: #FFFFFF; border-radius: 16px; overflow: hidden;
                              border: 1px solid #E2E8F0; box-shadow: 0 8px 24px rgba(0,0,0,0.07);">

                    <!-- ── HEADER ────────────────────────────────────────── -->
                    <tr>
                        <td style="background: #0F172A; padding: 32px 36px 28px; text-align: center;
                                   border-bottom: 4px solid #0099FF;">

                            <!-- Brand wordmark -->
                            <div style="display: inline-block; margin-bottom: 10px;">
                                <span style="font-size: 26px; font-weight: 900; color: #FFFFFF;
                                             letter-spacing: -0.5px; font-family: 'Segoe UI', Arial, sans-serif;">
                                    GRAFIKA
                                    <span style="color: #0099FF;">SOUTH</span>
                                </span>
                            </div>
                        </td>
                    </tr>

                    <!-- ── BODY ─────────────────────────────────────────── -->
                    <tr>
                        <td style="padding: 36px 36px 28px;">

                            <!-- Greeting -->
                            <h2 style="margin: 0 0 12px; font-size: 20px; font-weight: 800;
                                       color: #0F172A; line-height: 1.3;">
                                Your order has moved to the next step!
                            </h2>

                            <p style="margin: 0 0 20px; font-size: 15px; color: #475569; line-height: 1.6;">
                                Hello <strong style="color: #0F172A;">{{ $order->customer_name }}</strong>,
                                your {{ strtolower($orderLabel) }} with Grafika South has been updated to:
                            </p>

                            <!-- ── STATUS HIGHLIGHT CARD ──────────────── -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="margin-bottom: 24px;">
                                <tr>
                                    <td style="background: {{ $sc['bg'] }}; border: 1.5px solid {{ $sc['border'] }};
                                               border-radius: 12px; padding: 22px 24px; text-align: center;">

                                        <p style="margin: 0 0 6px; font-size: 11px; font-weight: 800;
                                                  text-transform: uppercase; letter-spacing: 1px; color: {{ $sc['text'] }};">
                                            Current Status
                                        </p>

                                        <p style="margin: 0; font-size: 22px; font-weight: 900;
                                                  color: {{ $sc['text'] }}; line-height: 1.2;">
                                            {{ $displayStatus }}
                                        </p>

                                        @if($order->is_custom_order)
                                            <p style="margin: 8px 0 0; font-size: 11px; font-weight: 700; color: #BE185D;">
                                                🎨 Custom Print Order
                                            </p>
                                        @endif

                                    </td>
                                </tr>
                            </table>
                            <!-- ── /STATUS HIGHLIGHT CARD ─────────────── -->

                            <!-- ── ORDER DETAILS SUMMARY ──────────────── -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;
                                          padding: 16px 20px; margin-bottom: 24px; font-size: 14px;">
                                <tr>
                                    <td style="padding: 6px 0; color: #64748B; width: 40%;">
                                        <strong>{{ $orderLabel }} Number:</strong>
                                    </td>
                                    <td style="padding: 6px 0; font-family: monospace; font-weight: 800; color: #0F172A;">
                                        #{{ $order->order_number }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; color: #64748B;">
                                        <strong>Fulfillment Type:</strong>
                                    </td>
                                    <td style="padding: 6px 0; font-weight: 700; color: #0F172A;">
                                        {{ $order->fulfillment_type === 'delivery' ? '🚚 Delivery' : '🏪 Store Pickup' }}
                                    </td>
                                </tr>
                                @if($order->items && $order->items->isNotEmpty())
                                <tr>
                                    <td style="padding: 6px 0; color: #64748B; vertical-align: top;">
                                        <strong>Item(s):</strong>
                                    </td>
                                    <td style="padding: 6px 0; color: #0F172A;">
                                        @foreach($order->items as $item)
                                            <div style="margin-bottom: 3px;">
                                                <strong>{{ $item->quantity }}x</strong> {{ $item->product ? $item->product->name : 'Custom Item' }}
                                            </div>
                                        @endforeach
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding: 6px 0; color: #64748B;">
                                        <strong>Payment Status:</strong>
                                    </td>
                                    <td style="padding: 6px 0; font-weight: 700; color: {{ $order->payment_status === 'paid' ? '#059669' : ($order->payment_status === 'partially_paid' ? '#0284C7' : ($order->payment_status === 'cancelled' ? '#64748B' : '#D97706')) }};">
                                        {{ ucwords(str_replace('_', ' ', $order->payment_status)) }}
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- ── DIVIDER ───────────────────────────────────────── -->
                    <tr>
                        <td style="padding: 0 36px;">
                            <hr style="border: none; border-top: 1px solid #E2E8F0; margin: 0;">
                        </td>
                    </tr>

                    <!-- ── FOOTER ────────────────────────────────────────── -->
                    <tr>
                        <td style="background: #F8FAFC; padding: 22px 36px; text-align: center;
                                   border-radius: 0 0 16px 16px;">

                            <p style="margin: 0; font-size: 13px; font-weight: 800;
                                      color: #0F172A; letter-spacing: 0.2px;">
                                GRAFIKA <span style="color: #0099FF;">SOUTH</span>
                            </p>

                        </td>
                    </tr>

                </table>
                <!-- /Email card -->

            </td>
        </tr>
    </table>

</body>
</html>
