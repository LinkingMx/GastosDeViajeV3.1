<?php

namespace App\Filament\Resources\PerDiemResource\Pages;

use App\Filament\Resources\PerDiemResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePerDiem extends CreateRecord
{
    protected static string $resource = PerDiemResource::class;

    public function getTitle(): string
    {
        return 'Crear Viático';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->icon('heroicon-o-currency-dollar')
            ->title('Viático creado correctamente')
            ->body('El viático ha sido registrado y guardado exitosamente.');
    }

    public static function getCreateButtonLabel(): string
    {
        return 'Nuevo viático';
    }

    public static function getCreateButtonIcon(): ?string
    {
        return 'heroicon-o-plus';
    }
}
