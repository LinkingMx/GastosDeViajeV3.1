<?php

namespace App\Filament\Resources\TravelRequestResource\Pages;

use App\Filament\Resources\TravelRequestResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Mail;

class ViewTravelRequest extends ViewRecord
{
    protected static string $resource = TravelRequestResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [Actions\EditAction::make()];
        $record = $this->getRecord();

        if ($record->status === 'draft') {
            $actions[] = Action::make('submitFromView')
                ->label('Enviar a Autorización')
                ->action(function () use ($record) {
                    $record->update(['status' => 'submitted']);

                    // Notify Creator & Authorizer
                    Mail::to($record->user)->send(new \App\Mail\TravelRequestCreated($record));
                    Mail::to($record->authorizer)->send(new \App\Mail\TravelRequestSubmitted($record));
                    Notification::make()
                        ->title('Nueva solicitud de viaje')
                        ->body("{$record->user->name} ha enviado una nueva solicitud para tu autorización.")
                        ->success()
                        ->sendToDatabase($record->authorizer);

                    Notification::make()->title('Solicitud Enviada')->body('Tu solicitud ha sido enviada para autorización.')->success()->send();
                    $this->refresh();
                })
                ->requiresConfirmation();
        }

        return $actions;
    }
}
