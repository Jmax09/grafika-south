<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $otp;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
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
            subject: "Your Grafika South Password Reset Code: {$this->otp}",
        );
    }

    /**
     * Get the message content definition with HTML and plain-text fallback.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password_reset_otp',
            text: 'emails.password_reset_otp_text',
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
