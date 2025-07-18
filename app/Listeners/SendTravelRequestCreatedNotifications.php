<?php

namespace App\Listeners;

use App\Events\TravelRequestCreated;
use App\Mail\TravelRequestCreated as TravelRequestCreatedMail;
use App\Notifications\TravelRequestCreatedNotification;
use Illuminate\Support\Facades\Mail;

class SendTravelRequestCreatedNotifications
{
    /**
     * Handle the event.
     */
    public function handle(TravelRequestCreated $event): void
    {
        $travelRequest = $event->travelRequest;
        $user = $travelRequest->user;

        // Create a unique key for this email to prevent duplicates
        $emailKey = "travel_request_email_sent_{$travelRequest->id}";
        
        // Check if email was already sent for this request
        if (cache()->has($emailKey)) {
            \Log::info("Email already sent for solicitud: {$travelRequest->folio}, skipping duplicate");
            return;
        }

        // Mark email as sent (expires in 1 hour)
        cache()->put($emailKey, true, now()->addHour());

        // Add debugging to see if this is being called multiple times
        \Log::info("TravelRequestCreated event handler called for solicitud: {$travelRequest->folio}", [
            'event_id' => spl_object_id($event),
            'request_id' => $travelRequest->id,
        ]);

        try {
            // Enviar correo electrónico al usuario (sin queue - inmediato)
            Mail::to($user->email)->send(new TravelRequestCreatedMail($travelRequest));
            
            // Log para debugging
            \Log::info("Correo enviado exitosamente para solicitud: {$travelRequest->folio}");
            
        } catch (\Exception $e) {
            \Log::error("Error enviando correo para solicitud {$travelRequest->folio}: " . $e->getMessage());
            // Remove cache key if email failed
            cache()->forget($emailKey);
        }

        try {
            // Crear notificación en la base de datos usando Laravel estándar con formato Filament
            $user->notify(new TravelRequestCreatedNotification($travelRequest));
            
            // Verificar si se guardó
            $count = \DB::table('notifications')->where('notifiable_id', $user->id)->count();
            \Log::info("Notificación creada exitosamente para solicitud: {$travelRequest->folio}. Total notificaciones en BD: {$count}");
            
        } catch (\Exception $e) {
            \Log::error("Error creando notificación para solicitud {$travelRequest->folio}: " . $e->getMessage());
        }
    }
}