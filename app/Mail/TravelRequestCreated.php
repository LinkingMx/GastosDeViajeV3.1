<?php

namespace App\Mail;

use App\Models\TravelRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Filament\Resources\TravelRequestResource;

class TravelRequestCreated extends Mailable
{
    use SerializesModels;

    /**
     * The travel request instance.
     *
     * @var \App\Models\TravelRequest
     */
    public $travelRequest;

    /**
     * The URL to view the travel request.
     *
     * @var string
     */
    public $viewUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(TravelRequest $travelRequest)
    {
        $this->travelRequest = $travelRequest;
        $this->viewUrl = TravelRequestResource::getUrl('view', ['record' => $travelRequest]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitud de Viaje Creada - Folio: ' . $this->travelRequest->folio,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.travel-request-created',
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
