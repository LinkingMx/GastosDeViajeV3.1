<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TravelRequestAttachment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'travel_request_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'attachment_type_id',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the travel request that owns the attachment.
     */
    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }

    /**
     * Get the user who uploaded the attachment.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the attachment type.
     */
    public function attachmentType(): BelongsTo
    {
        return $this->belongsTo(AttachmentType::class);
    }

    /**
     * Get the human-readable attachment type.
     */
    public function getAttachmentTypeLabelAttribute(): string
    {
        return $this->attachmentType?->name ?? 'Documento';
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        } else {
            return $bytes.' bytes';
        }
    }

    /**
     * Get the download URL for the attachment.
     */
    public function getDownloadUrlAttribute(): string
    {
        // Use secure download route for all attachments
        return route('attachments.download', $this);
    }

    /**
     * Delete the physical file when the model is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (TravelRequestAttachment $attachment) {
            if (Storage::exists($attachment->file_path)) {
                Storage::delete($attachment->file_path);
            }
        });
    }
}
