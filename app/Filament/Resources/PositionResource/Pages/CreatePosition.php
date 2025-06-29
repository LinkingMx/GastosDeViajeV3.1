<?php

namespace App\Filament\Resources\PositionResource\Pages;

use App\Filament\Resources\PositionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosition extends CreateRecord
{
    protected static string $resource = PositionResource::class;

    public function getTitle(): string
    {
        return 'Crear Posición';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->title('Posición creada correctamente')
            ->icon('heroicon-o-briefcase')
            ->body('La posición ha sido registrada y guardada exitosamente.')
            ->success();
    }
}
