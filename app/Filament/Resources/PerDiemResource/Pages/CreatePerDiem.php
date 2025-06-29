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
            ->title('Viático creado correctamente')
            ->icon('heroicon-o-currency-dollar')
            ->body('El viático ha sido registrado y guardado exitosamente.')
            ->success();
    }
}
