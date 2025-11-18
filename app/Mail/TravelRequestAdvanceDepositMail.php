<?php

namespace App\Mail;

use App\Models\TravelRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class TravelRequestAdvanceDepositMail extends Mailable
{
    use SerializesModels;

    public TravelRequest $travelRequest;

    /**
     * Create a new message instance.
     */
    public function __construct(TravelRequest $travelRequest)
    {
        $this->travelRequest = $travelRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Anticipo Depositado - ' . $this->travelRequest->folio,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.travel-request-advance-deposit',
            with: [
                'travelRequest' => $this->travelRequest,
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

        \Log::info("TravelRequestAdvanceDepositMail - Buscando archivos de tesorería para folio: " . $this->travelRequest->folio);

        // Obtener archivos relacionados con tesorería (comprobantes de depósito)
        $treasuryAttachments = $this->travelRequest->attachments()
            ->whereHas('attachmentType', function ($query) {
                $query->whereIn('name', ['Comprobante de Depósito', 'Comprobante Bancario', 'Recibo de Depósito']);
            })
            ->orWhere(function ($query) {
                $query->whereHas('uploader', function ($q) {
                    $q->where('team', 'treasury');
                });
            })
            ->get();

        \Log::info("TravelRequestAdvanceDepositMail - Encontrados " . $treasuryAttachments->count() . " archivos de tesorería");

        foreach ($treasuryAttachments as $attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                \Log::info("TravelRequestAdvanceDepositMail - Adjuntando archivo: " . $attachment->file_name);
                $attachments[] = Attachment::fromPath(Storage::disk('public')->path($attachment->file_path))
                    ->as($attachment->file_name);
            } else {
                \Log::warning("TravelRequestAdvanceDepositMail - Archivo no encontrado: " . $attachment->file_path);
            }
        }

        return $attachments;
    }
}