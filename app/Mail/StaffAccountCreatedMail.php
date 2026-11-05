<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $password;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address', 'grafikasouth69@gmail.com');
        $fromName    = config('mail.from.name', 'Grafika South');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [
                new Address('grafikasouth69@gmail.com', 'Grafika South Support'),
            ],
            subject: 'Your Grafika South Staff Account Is Ready',
        );
    }

    /**
     * Get the message content definition with HTML and plain-text fallback.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.staff_account_created',
            text: 'emails.staff_account_created_text',
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
