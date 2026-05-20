<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeBackMail extends Mailable
{
    use Queueable, SerializesModels;
    public string $msg;
    public string $sub;
    /**
     * Create a new message instance.
     */
    public function __construct(string $message, string $subject)
    {
        $this->msg = $message;
        $this->sub = $subject;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->sub,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.welcomeBack',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
