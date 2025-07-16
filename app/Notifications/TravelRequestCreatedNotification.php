<?php

namespace App\Notifications;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelRequestCreatedNotification extends Notification
{
    use Queueable;

    protected TravelRequest $travelRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(TravelRequest $travelRequest)
    {
        $this->travelRequest = $travelRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title('Solicitud de Viaje Creada')
            ->body("Has creado una nueva solicitud de viaje con el folio: {$this->travelRequest->folio}")
            ->icon('heroicon-o-briefcase')
            ->color('success')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Ver Solicitud')
                    ->url(route('filament.admin.resources.travel-requests.view', $this->travelRequest))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }
}