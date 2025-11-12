<?php

use App\Http\Controllers\AttachmentController;
use App\Filament\Resources\ExpenseVerificationResource;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

// Ruta temporal para previsualizar el email (sin autenticación para pruebas)
Route::get('/email-preview/travel-request-created', function () {
    // Crear un objeto de prueba
    $travelRequest = new \App\Models\TravelRequest();
    $travelRequest->folio = 'TR-2024-001';
    $travelRequest->departure_date = now()->addDays(5);
    $travelRequest->return_date = now()->addDays(10);
    $travelRequest->destination_city = 'Ciudad de México';
    $travelRequest->status_display = 'BORRADOR';
    $travelRequest->created_at = now();
    
    // Crear un usuario de prueba
    $user = new \App\Models\User();
    $user->name = 'Juan Pérez';
    $user->email = 'juan.perez@ejemplo.com';
    
    $travelRequest->user = $user;
    
    $viewUrl = url('/admin/travel-requests/1/view');
    
    return view('emails.travel-request-created', compact('travelRequest', 'viewUrl'));
});

// Protected route for downloading attachments
Route::middleware(['auth'])->group(function () {
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');
    
    // Rutas para actualización de conceptos CFDI
    Route::post('/admin/expense-receipts/{receipt}/update-field', function (\App\Models\ExpenseReceipt $receipt, \Illuminate\Http\Request $request) {
        try {
            $field = $request->input('field');
            $value = $request->input('value');
            
            // Validar campos permitidos
            $allowedFields = ['expense_detail_id', 'applied_amount', 'notes'];
            if (!in_array($field, $allowedFields)) {
                return response()->json(['success' => false, 'message' => 'Campo no permitido']);
            }
            
            // Validar valor según el campo
            if ($field === 'expense_detail_id' && !empty($value)) {
                if (!\App\Models\ExpenseDetail::find($value)) {
                    return response()->json(['success' => false, 'message' => 'Detalle de gasto no válido']);
                }
            }
            
            if ($field === 'applied_amount' && !empty($value)) {
                if (!is_numeric($value) || $value < 0) {
                    return response()->json(['success' => false, 'message' => 'Monto no válido']);
                }
            }
            
            $receipt->update([$field => $value]);
            
            \Log::info('Receipt field updated', [
                'receipt_id' => $receipt->id,
                'field' => $field,
                'value' => $value,
                'updated_by' => auth()->id()
            ]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error updating receipt field', [
                'receipt_id' => $receipt->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    });
    
    Route::delete('/admin/expense-receipts/{receipt}/delete', function (\App\Models\ExpenseReceipt $receipt) {
        try {
            $receipt->delete();
            
            \Log::info('Concepto CFDI eliminado correctamente', [
                'receipt_id' => $receipt->id,
                'expense_verification_id' => $receipt->expense_verification_id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar concepto CFDI', [
                'receipt_id' => $receipt->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    });
});
