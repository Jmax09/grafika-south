<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }} - Grafika South</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            color: var(--text-main); 
            background: var(--bg-page); 
            padding: 40px 20px; 
            line-height: 1.5; 
        }
        
        .top-bar { 
            max-width: 800px; 
            margin: 0 auto 16px auto; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        .back-btn { 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: 600; 
            padding: 8px 16px; 
            background: var(--bg-surface); 
            border: 1px solid var(--border-color); 
            border-radius: 6px; 
            transition: all 0.2s; 
        }
        .back-btn:hover { 
            color: var(--text-accent); 
            border-color: var(--accent-color); 
        }

        .print-btn { 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            background: var(--accent-color); 
            color: var(--text-inverse); 
            border: none; 
            padding: 9px 18px; 
            border-radius: 6px; 
            font-size: 14px; 
            font-weight: 700; 
            cursor: pointer; 
            transition: background 0.2s; 
        }
        .print-btn:hover { background: var(--accent-color); }

        .invoice-box { 
            max-width: 800px; 
            margin: 0 auto; 
            border: 1px solid var(--border-color); 
            padding: 40px; 
            border-radius: 10px; 
            background: var(--bg-surface); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
        }

        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            border-bottom: 2px solid var(--accent-color); 
            padding-bottom: 20px; 
            margin-bottom: 24px; 
        }

        .brand { 
            font-size: 26px; 
            font-weight: 800; 
            color: var(--text-main); 
            letter-spacing: -0.5px; 
            line-height: 1.1; 
        }
        .brand span { color: var(--text-accent); }
        .brand-sub { 
            font-size: 12px; 
            color: var(--text-muted); 
            margin-top: 4px; 
            font-weight: 500; 
        }

        .invoice-title { 
            text-align: right; 
        }
        .invoice-title h2 { 
            font-size: 20px; 
            font-weight: 800; 
            color: var(--text-accent); 
            letter-spacing: -0.3px; 
            margin: 0; 
        }
        .invoice-number { 
            font-size: 14px; 
            color: var(--text-muted); 
            font-weight: 600; 
            margin-top: 4px; 
        }

        .details-grid { 
            display: grid; 
            grid-template-columns: 1.2fr 1fr; 
            gap: 24px; 
            margin-bottom: 28px; 
            font-size: 14px; 
        }

        .meta-title { 
            font-size: 11px; 
            font-weight: 700; 
            color: var(--text-subtle); 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            margin-bottom: 6px; 
        }
        .meta-body { 
            color: var(--text-muted); 
            line-height: 1.6; 
        }
        .meta-body strong { 
            color: var(--text-main); 
        }

        .meta-table-right { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .meta-table-right td { 
            padding: 3px 0; 
            font-size: 14px; 
        }
        .meta-table-right td:first-child { 
            color: var(--text-muted); 
            font-weight: 500; 
            width: 45%; 
        }
        .meta-table-right td:last-child { 
            text-align: right; 
            color: var(--text-main); 
            font-weight: 700; 
        }

        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 24px; 
        }
        .table th { 
            background: var(--bg-page); 
            color: var(--text-muted); 
            font-size: 12px; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            padding: 12px 14px; 
            border-top: 1px solid var(--border-color); 
            border-bottom: 1px solid var(--border-color); 
        }
        .table td { 
            padding: 14px; 
            text-align: left; 
            border-bottom: 1px solid var(--border-color); 
            font-size: 14px; 
            vertical-align: top; 
        }

        .spec-item { 
            font-size: 12px; 
            color: var(--text-muted); 
            line-height: 1.4; 
        }
        .spec-item strong { 
            color: var(--text-muted); 
        }

        .total-row td { 
            padding-top: 18px; 
            font-size: 16px; 
            font-weight: 800; 
            color: var(--text-main); 
            border-top: 2px solid var(--border-color); 
            border-bottom: none; 
        }
        .total-row .total-amount { 
            font-size: 18px; 
            color: var(--text-accent); 
            font-weight: 900; 
        }

        .footer { 
            font-size: 12px; 
            color: var(--text-muted); 
            text-align: center; 
            border-top: 1px solid var(--border-color); 
            padding-top: 20px; 
            line-height: 1.5; 
        }

        /* Mobile: stack action buttons, single-column meta, scrollable items table */
        @media (max-width: 640px) {
            body { padding: 16px 10px; }

            .top-bar { flex-direction: column; align-items: stretch; gap: 10px; }
            .back-btn, .print-btn { width: 100%; justify-content: center; }

            .invoice-box { padding: 24px 16px; }

            .header { flex-direction: column; align-items: flex-start; gap: 14px; }
            .invoice-title { text-align: left; }

            .details-grid { grid-template-columns: minmax(0, 1fr); gap: 18px; }
            .meta-table-right td { font-size: 13px; }

            /* 5-column items table scrolls horizontally instead of blowing out the page */
            .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .table-scroll .table { min-width: 600px; margin-bottom: 0; }
            .table th, .table td { padding: 10px 8px; font-size: 13px; }
        }

        @media print {
            body { 
                background: var(--bg-surface); 
                padding: 0; 
                color: var(--text-main); 
            }
            .top-bar { 
                display: none; 
            }
            /* Undo the mobile scroll wrapper so the full table prints */
            .table-scroll { 
                overflow: visible; 
            }
            .table-scroll .table { 
                min-width: 0; 
                margin-bottom: 24px; 
            }
            .invoice-box { 
                border: none; 
                box-shadow: none; 
                padding: 0; 
                max-width: 100%; 
            }
            .table th { 
                background: var(--bg-page) !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
            }
            .header { 
                border-bottom: 2px solid var(--accent-color) !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
            }
        }
    </style>
</head>
<body>

<div class="top-bar">
    <a href="{{ route('customer.orders.show', $order->id) }}" class="back-btn">
        &larr; Back to Order
    </a>
    <button type="button" class="print-btn" onclick="window.print()">
        &#128424; Print Invoice / Download PDF
    </button>
</div>

<div class="invoice-box">
    <div class="header">
        <div>
            <div class="brand">GRAFIKA <span>SOUTH</span></div>
            <div class="brand-sub">Custom Printing & Promotional Merchandise</div>
        </div>
        <div class="invoice-title">
            <h2>RECEIPT</h2>
            <div class="invoice-number">#{{ $order->order_number }}</div>
        </div>
    </div>

    <div class="details-grid">
        <div>
            <div class="meta-title">Billed To</div>
            <div class="meta-body">
                <strong>{{ $order->customer_name }}</strong><br>
                @if($order->customer_email){{ $order->customer_email }}<br>@endif
                @if($order->customer_contact){{ $order->customer_contact }}<br>@endif
                {{ $order->customer_address ?: 'San Pedro, Laguna' }}
            </div>
        </div>
        <div>
            <div class="meta-title" style="text-align: right;">Order Details</div>
            <table class="meta-table-right">
                <tr>
                    <td>Order Date:</td>
                    <td>{{ $order->created_at->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td>Order Status:</td>
                    <td>{{ strtoupper(str_replace('_', ' ', $order->status)) }}</td>
                </tr>
                <tr>
                    <td>Payment Status:</td>
                    <td>{{ strtoupper(str_replace('_', ' ', $order->payment_status)) }}</td>
                </tr>
                <tr>
                    <td>Payment Term:</td>
                    <td>{{ $order->formatted_payment_plan }}</td>
                </tr>
                @if($order->payment)
                <tr>
                    <td>Payment Method:</td>
                    <td>{{ $order->payment->payment_method }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="table-scroll">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 40%; text-align: left;">Item Description</th>
                <th style="width: 25%; text-align: left;">Selected Options</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 12%; text-align: right;">Unit Price</th>
                <th style="width: 13%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <strong style="color: var(--text-main);">{{ $item->product ? $item->product->name : 'Product' }}</strong>
                        @if(!empty($item->special_instructions))
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px; font-style: italic;">
                                Note: "{{ $item->special_instructions }}"
                            </div>
                        @endif
                    </td>
                    <td>
                        @if(!empty($item->options_selected))
                            @foreach($item->options_selected as $k => $v)
                                <div class="spec-item"><strong>{{ $k }}:</strong> {{ $v }}</div>
                            @endforeach
                        @else
                            <span style="color: var(--text-subtle); font-size: 13px;">Standard</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: 600;">{{ $item->quantity }}</td>
                    <td style="text-align: right; color: var(--text-muted);">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right; font-weight: 700; color: var(--text-main);">₱{{ number_format($item->subtotal ?: ($item->unit_price * $item->quantity), 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">Total Order Value:</td>
                <td style="text-align: right;" class="total-amount">₱{{ number_format($order->total_price ?: $order->total_amount, 2) }}</td>
            </tr>
            @if($order->is_downpayment)
                <tr>
                    <td colspan="4" style="text-align: right; color: var(--text-accent); font-weight: 700; border-top: 1px dashed var(--border-color);">50% Downpayment (Payable Now / Paid):</td>
                    <td style="text-align: right; font-weight: 800; color: var(--text-accent); border-top: 1px dashed var(--border-color);">₱{{ number_format($order->downpayment_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: right; color: var(--text-danger); font-weight: 700;">Remaining Balance (Due upon Collection):</td>
                    <td style="text-align: right; font-weight: 800; color: var(--text-danger);">₱{{ number_format($order->remaining_balance, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>
    </div>

    <div class="footer">
        Thank you for choosing Grafika South Print Shop!<br>
        For inquiries or assistance, contact <strong>grafikasouth69@gmail.com</strong> or call <strong>+63 962 292 7736</strong>.
    </div>
</div>

</body>
</html>
