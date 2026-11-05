<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $previousStatus;
    public string $newStatus;
    public string $trackingUrl;
    public string $orderNumber;
    public string $customerName;
    public ?string $customerEmail;
    public string $statusLabel;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, string $previousStatus, string $newStatus)
    {
        $this->order = $order;
        $this->previousStatus = $previousStatus;
        $this->newStatus = $newStatus;
        $this->orderNumber = $order->order_number;
        $this->customerName = $order->customer_name;
        $this->customerEmail = $order->customer_email;
        $this->statusLabel = ucwords(str_replace('_', ' ', $newStatus));

        // Generate direct track link
        $this->trackingUrl = route('orders.track', [
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
        $statusLabel = ucwords(str_replace('_', ' ', $this->newStatus));

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [
                new Address('grafikasouth69@gmail.com', 'Grafika South Support'),
            ],
            subject: "Update on your {$orderTypeLabel} #{$this->order->order_number}: {$statusLabel}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_status_updated',
            text: 'emails.order_status_updated_text',
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
