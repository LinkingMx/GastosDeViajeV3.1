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
            ->icon('heroicon-o-briefcase')
            ->title('Posición creada correctamente')
            ->body('La posición ha sido registrada y guardada exitosamente.');
    }

    public static function getCreateButtonLabel(): string
    {
        return 'Nueva posición';
    }

    public static function getCreateButtonIcon(): ?string
    {
        return 'heroicon-o-plus';
    }
}
