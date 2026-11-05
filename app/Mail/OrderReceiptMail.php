<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $trackingUrl;
    public string $orderNumber;
    public string $customerName;
    public ?string $customerEmail;
    public ?string $billingAddress;
    public string $paymentMethod;
    public float $totalAmount;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        // Load items with product and payment relations
        $this->order = $order->loadMissing(['items.product', 'payment', 'payments', 'subOrders.items.product', 'user']);

        $this->orderNumber    = $order->order_number;
        $this->customerName   = $order->customer_name;
        $this->customerEmail  = $order->customer_email;
        $this->billingAddress = $order->customer_address ?: 'San Pedro, Laguna';
        $this->paymentMethod  = $order->effective_payment_method;
        $this->totalAmount    = (float) ($order->total_price ?: $order->total_amount);

        $this->trackingUrl    = route('orders.track', [
            'order_number' => $order->order_number,
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address', 'grafikasouth69@gmail.com');
        $fromName    = config('mail.from.name', 'Grafika South');

        $orderTypeLabel = $this->order->isSubOrder() ? 'Sub-Order' : 'Order';

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [
                new Address('grafikasouth69@gmail.com', 'Grafika South Support'),
            ],
            subject: "Your Grafika South Order Receipt #{$this->orderNumber}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_receipt',
            text: 'emails.order_receipt_text',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
