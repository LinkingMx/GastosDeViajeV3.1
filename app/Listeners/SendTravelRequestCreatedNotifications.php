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

        try {
            // Enviar correo electrónico al usuario (sin queue - inmediato)
            Mail::to($user->email)->send(new TravelRequestCreatedMail($travelRequest));
            
            // Log para debugging
            \Log::info("Correo enviado exitosamente para solicitud: {$travelRequest->folio}");
            
        } catch (\Exception $e) {
            \Log::error("Error enviando correo para solicitud {$travelRequest->folio}: " . $e->getMessage());
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