<?php

namespace App\Mail;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class TeamFileUploadedMail extends Mailable
{
    use SerializesModels;

    public TravelRequest $travelRequest;

    public string $attachmentType;

    public string $fileName;

    public string $uploaderName;

    public string $uploaderTeam;

    public ?string $filePath;

    /**
     * Create a new message instance.
     */
    public function __construct(
        TravelRequest $travelRequest,
        string $attachmentType,
        string $fileName,
        string $uploaderName,
        string $uploaderTeam,
        ?string $filePath = null
    ) {
        $this->travelRequest = $travelRequest;
        $this->attachmentType = $attachmentType;
        $this->fileName = $fileName;
        $this->uploaderName = $uploaderName;
        $this->uploaderTeam = $uploaderTeam;
        $this->filePath = $filePath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📄 Nuevo Archivo Adjunto - '.$this->travelRequest->folio,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.team-file-uploaded',
            with: [
                'travelRequest' => $this->travelRequest,
                'attachmentType' => $this->attachmentType,
                'fileName' => $this->fileName,
                'uploaderName' => $this->uploaderName,
                'uploaderTeam' => $this->uploaderTeam,
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
        $attachments = [];

        \Log::info("TeamFileUploadedMail - filePath recibido: " . ($this->filePath ?? 'NULL'));
        
        if ($this->filePath && Storage::disk('public')->exists($this->filePath)) {
            \Log::info("TeamFileUploadedMail - Archivo existe en disco public, adjuntando: " . $this->filePath);
            $attachments[] = Attachment::fromPath(Storage::disk('public')->path($this->filePath))
                ->as($this->fileName);
        } else {
            \Log::warning("TeamFileUploadedMail - Archivo NO existe o filePath es NULL");
            if ($this->filePath) {
                \Log::warning("TeamFileUploadedMail - Ruta completa disco public: " . Storage::disk('public')->path($this->filePath));
                \Log::warning("TeamFileUploadedMail - Existe en disco public: " . (Storage::disk('public')->exists($this->filePath) ? 'TRUE' : 'FALSE'));
            }
        }

        return $attachments;
    }
}
