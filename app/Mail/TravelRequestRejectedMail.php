<?php

namespace App\Mail;

use App\Models\TravelRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TravelRequestRejectedMail extends Mailable
{
    use SerializesModels;

    public TravelRequest $travelRequest;
    public string $rejectionReason;
    public string $rejectorName;

    /**
     * Create a new message instance.
     */
    public function __construct(TravelRequest $travelRequest, string $rejectionReason, string $rejectorName)
    {
        $this->travelRequest = $travelRequest;
        $this->rejectionReason = $rejectionReason;
        $this->rejectorName = $rejectorName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitud de Viaje Rechazada - ' . $this->travelRequest->folio,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.travel-request-rejected',
            with: [
                'travelRequest' => $this->travelRequest,
                'rejectionReason' => $this->rejectionReason,
                'rejectorName' => $this->rejectorName,
                'viewUrl' => route('filament.admin.resources.travel-requests.view', $this->travelRequest),
            ],
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
