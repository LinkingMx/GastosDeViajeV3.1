<?php

namespace App\Filament\Resources\PerDiemResource\Pages;

use App\Filament\Resources\PerDiemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerDiem extends EditRecord
{
    protected static string $resource = PerDiemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->color('danger'),
        ];
    }

    public function getTitle(): string
    {
        return 'Editar Viático';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->title('Viático actualizado correctamente')
            ->icon('heroicon-o-currency-dollar')
            ->body('Los datos del viático han sido actualizados exitosamente.')
            ->success();
    }
}
