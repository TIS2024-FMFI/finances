<?php

namespace App\Mail;

use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * An email containing a login link.
 */
class LoginLink extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The login link.
     * 
     * @var string
     */
    private string $url;

    /**
     * The date and time until which the link is valid.
     * 
     * @var DateTimeInterface
     */
    private DateTimeInterface $validUntil;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $token, DateTimeInterface $validUntil)
    {
        $this->validUntil = $validUntil;
        $this->url = env('APP_URL') . '/login/' . $token;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: trans(
                'emails.login-link.subject',
                [ 'appName' => config('app.name') ]
            ),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        $formattedValidUntil =
            $this->validUntil->format('d. m. Y H:i:s ')
            . $this->validUntil->getTimezone()->getName();
        
        return new Content(
            view: 'emails.login_link',
            with: [
                'validUntil' => $formattedValidUntil,
                'url' => $this->url,
            ]
        );
    }
}
