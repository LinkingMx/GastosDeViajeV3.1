<?php

namespace App\Notifications;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelRequestNotification extends Notification
{
    use Queueable;

    public string $title;
    public string $message;
    public TravelRequest $travelRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, TravelRequest $travelRequest)
    {
        $this->title = $title;
        $this->message = $message;
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
        return [
            'title' => $this->title,
            'body' => $this->message,
            'icon' => 'heroicon-o-bell',
            'iconColor' => 'primary',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'Ver Solicitud',
                    'url' => route('filament.admin.resources.travel-requests.view', $this->travelRequest),
                ]
            ],
            'format' => 'filament',
            'travel_request_id' => $this->travelRequest->id,
            'folio' => $this->travelRequest->folio,
        ];
    }
}
