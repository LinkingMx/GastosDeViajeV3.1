<?php

namespace App\Http\Controllers;

use App\Models\TravelRequestAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Download a travel request attachment.
     */
    public function download(TravelRequestAttachment $attachment): StreamedResponse
    {
        // Check if user has permission to download this attachment
        $user = auth()->user();
        $travelRequest = $attachment->travelRequest;

        // Permission check: user must be related to the travel request or have admin permissions
        if (! $this->canDownloadAttachment($user, $travelRequest, $attachment)) {
            abort(403, 'No tienes permiso para descargar este archivo.');
        }

        // Determine which disk to use based on file path
        $disk = $this->getDiskForAttachment($attachment);

        // Check if file exists
        if (! Storage::disk($disk)->exists($attachment->file_path)) {
            abort(404, 'El archivo no existe.');
        }

        // Get file content and info
        $fileContent = Storage::disk($disk)->get($attachment->file_path);
        $mimeType = Storage::disk($disk)->mimeType($attachment->file_path);

        // Return the file as a download
        return response()->streamDownload(
            function () use ($fileContent) {
                echo $fileContent;
            },
            $attachment->file_name,
            [
                'Content-Type' => $mimeType,
                'Content-Length' => strlen($fileContent),
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }

    /**
     * Check if user can download the attachment.
     */
    private function canDownloadAttachment($user, $travelRequest, $attachment): bool
    {
        if (! $user) {
            return false;
        }

        // Travel request owner can always download
        if ($travelRequest->user_id === $user->id) {
            return true;
        }

        // Authorizer can download
        if ($travelRequest->authorizer_id === $user->id) {
            return true;
        }

        // Travel team members can download in appropriate states
        if ($user->isTravelTeamMember() && in_array($travelRequest->status, ['travel_review', 'travel_approved', 'travel_rejected'])) {
            return true;
        }

        // Treasury team members can download deposit receipts and other attachments in appropriate states
        if ($user->isTreasuryTeamMember() && in_array($travelRequest->status, ['approved', 'travel_review', 'travel_approved'])) {
            return true;
        }

        // Admins or users with special permissions (you can extend this)
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }

        if (method_exists($user, 'can') && $user->can('download_all_attachments')) {
            return true;
        }

        return false;
    }

    /**
     * Determine which disk to use for the attachment.
     */
    private function getDiskForAttachment($attachment): string
    {
        // Deposit receipts are stored in local (private) disk
        if (str_starts_with($attachment->file_path, 'deposit-receipts/')) {
            return 'local';
        }

        // Other attachments are stored in public disk
        return 'public';
    }
}
