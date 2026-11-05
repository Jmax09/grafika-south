<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Order Receipt — Grafika South</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F0F4F8; font-family: 'Segoe UI', Arial, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">

    <!-- Outer wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color: #F0F4F8; padding: 40px 16px;">
        <tr>
            <td align="center">

                <!-- Email card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width: 600px; background: #FFFFFF; border-radius: 16px; overflow: hidden;
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
                            <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 800;
                                       color: #0F172A; line-height: 1.3;">
                                ORDER RECEIPT
                            </h2>

                            <p style="margin: 0 0 24px; font-size: 15px; color: #475569; line-height: 1.6;">
                                Hello <strong style="color: #0F172A;">{{ $customerName }}</strong>,
                                we have received your order and our team has started processing it. Below is your detailed receipt:
                            </p>

                            <!-- ── RECEIPT META CARD ──────────────────── -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;
                                          padding: 18px 20px; margin-bottom: 24px; font-size: 13.5px;">
                                <tr>
                                    <td style="padding: 5px 0; color: #64748B; width: 42%;">
                                        <strong>Order Tracking #:</strong>
                                    </td>
                                    <td style="padding: 5px 0; font-family: monospace; font-weight: 800; color: #0099FF; font-size: 14px;">
                                        #{{ $orderNumber }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; color: #64748B;">
                                        <strong>Order Date:</strong>
                                    </td>
                                    <td style="padding: 5px 0; font-weight: 600; color: #0F172A;">
                                        {{ $order->created_at->format('F d, Y h:i A') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; color: #64748B;">
                                        <strong>Payment Method:</strong>
                                    </td>
                                    <td style="padding: 5px 0; font-weight: 700; color: #0F172A;">
                                        {{ $paymentMethod }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; color: #64748B;">
                                        <strong>Payment Plan:</strong>
                                    </td>
                                    <td style="padding: 5px 0; font-weight: 700; color: {{ $order->is_downpayment ? '#0284C7' : '#059669' }};">
                                        {{ $order->formatted_payment_plan }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; color: #64748B;">
                                        <strong>Fulfillment Type:</strong>
                                    </td>
                                    <td style="padding: 5px 0; font-weight: 600; color: #0F172A;">
                                        {{ $order->fulfillment_type === 'delivery' ? '🚚 Delivery' : '🏪 Store Pickup' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; color: #64748B; vertical-align: top;">
                                        <strong>Billing / Delivery Address:</strong>
                                    </td>
                                    <td style="padding: 5px 0; color: #0F172A;">
                                        {{ $billingAddress }}
                                    </td>
                                </tr>
                            </table>

                            <!-- ── ITEMS TABLE ───────────────────────── -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 13.5px;">
                                <thead>
                                    <tr style="background: #F1F5F9; border-bottom: 2px solid #CBD5E1; color: #475569; font-size: 12px; text-transform: uppercase;">
                                        <th style="padding: 10px 12px; text-align: left;">Item Description</th>
                                        <th style="padding: 10px 12px; text-align: center; width: 12%;">Qty</th>
                                        <th style="padding: 10px 12px; text-align: right; width: 22%;">Price</th>
                                        <th style="padding: 10px 12px; text-align: right; width: 24%;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr style="border-bottom: 1px solid #E2E8F0;">
                                            <td style="padding: 12px; color: #0F172A; vertical-align: top;">
                                                <strong style="display: block; font-size: 14px; color: #0F172A;">
                                                    {{ $item->product ? $item->product->name : 'Custom Item' }}
                                                </strong>
                                                @if(!empty($item->options_selected))
                                                    <div style="margin-top: 4px; font-size: 12px; color: #64748B; line-height: 1.4;">
                                                        @foreach($item->options_selected as $optName => $optVal)
                                                            <span><strong>{{ $optName }}:</strong> {{ $optVal }}</span><br>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if(!empty($item->special_instructions))
                                                    <div style="font-size: 11.5px; color: #94A3B8; font-style: italic; margin-top: 3px;">
                                                        Note: "{{ $item->special_instructions }}"
                                                    </div>
                                                @endif
                                            </td>
                                            <td style="padding: 12px; text-align: center; font-weight: 700; color: #0F172A; vertical-align: top;">
                                                {{ $item->quantity }}
                                            </td>
                                            <td style="padding: 12px; text-align: right; color: #475569; vertical-align: top;">
                                                ₱{{ number_format($item->unit_price, 2) }}
                                            </td>
                                            <td style="padding: 12px; text-align: right; font-weight: 700; color: #0F172A; vertical-align: top;">
                                                ₱{{ number_format($item->subtotal ?: ($item->unit_price * $item->quantity), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <!-- TOTAL ROW -->
                                    <tr style="background: #F8FAFC; border-top: 2px solid #CBD5E1;">
                                        <td colspan="3" style="padding: 12px; text-align: right; font-weight: 800; color: #0F172A; font-size: 14px;">
                                            Total Order Value:
                                        </td>
                                        <td style="padding: 12px; text-align: right; font-weight: 900; color: #0099FF; font-size: 17px;">
                                            ₱{{ number_format($totalAmount, 2) }}
                                        </td>
                                    </tr>

                                    @if($order->is_downpayment)
                                        <tr style="background: #F0F9FF;">
                                            <td colspan="3" style="padding: 10px 12px; text-align: right; font-weight: 700; color: #0284C7; font-size: 13px;">
                                                50% Downpayment (Paid / Payable Now):
                                            </td>
                                            <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #0284C7; font-size: 14px;">
                                                ₱{{ number_format($order->downpayment_amount, 2) }}
                                            </td>
                                        </tr>
                                        <tr style="background: #FEF2F2;">
                                            <td colspan="3" style="padding: 10px 12px; text-align: right; font-weight: 700; color: #DC2626; font-size: 13px;">
                                                Remaining Balance (Due upon Collection):
                                            </td>
                                            <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #DC2626; font-size: 14px;">
                                                ₱{{ number_format($order->remaining_balance, 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
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
