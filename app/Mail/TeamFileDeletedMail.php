<?php

namespace App\Mail;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamFileDeletedMail extends Mailable
{
    use SerializesModels;

    public TravelRequest $travelRequest;

    public string $attachmentType;

    public string $fileName;

    public string $deleterName;

    public string $deleterTeam;

    public ?string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(
        TravelRequest $travelRequest,
        string $attachmentType,
        string $fileName,
        string $deleterName,
        string $deleterTeam,
        ?string $reason = null
    ) {
        $this->travelRequest = $travelRequest;
        $this->attachmentType = $attachmentType;
        $this->fileName = $fileName;
        $this->deleterName = $deleterName;
        $this->deleterTeam = $deleterTeam;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🗑️ Archivo Eliminado - '.$this->travelRequest->folio,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.team-file-deleted',
            with: [
                'travelRequest' => $this->travelRequest,
                'attachmentType' => $this->attachmentType,
                'fileName' => $this->fileName,
                'deleterName' => $this->deleterName,
                'deleterTeam' => $this->deleterTeam,
                'reason' => $this->reason,
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