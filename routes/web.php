<?php

use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

// Protected route for downloading attachments
Route::middleware(['auth'])->group(function () {
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');
});
