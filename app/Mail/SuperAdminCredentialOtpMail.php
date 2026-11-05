<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuperAdminCredentialOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $otp;
    public string $purpose;

    /**
     * Create a new message instance.
     *
     * @param string $purpose 'credentials' (default) or 'password'.
     */
    public function __construct(User $user, string $otp, string $purpose = 'credentials')
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->purpose = in_array($purpose, ['credentials', 'password'], true) ? $purpose : 'credentials';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address', 'grafikasouth69@gmail.com');
        $fromName    = config('mail.from.name', 'Grafika South');

        $subject = $this->purpose === 'password'
            ? "Security Code for Password Change: {$this->otp}"
            : "Security Code for Credential Update: {$this->otp}";

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [
                new Address('grafikasouth69@gmail.com', 'Grafika South Support'),
            ],
            subject: $subject,
        );
    }

    /**
     * Get the message content definition with HTML and plain-text fallback.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.super_admin_credential_otp',
            text: 'emails.super_admin_credential_otp_text',
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
