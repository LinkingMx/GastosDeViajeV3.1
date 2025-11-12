<?php

namespace App\Filament\Resources\GeneralSettingResource\Pages;

use App\Filament\Resources\GeneralSettingResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditGeneralSetting extends EditRecord
{
    protected static string $resource = GeneralSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions needed - just editing
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-o-check-circle')
            ->iconColor('primary')
            ->title('Configuración Actualizada')
            ->body('Los cambios han sido guardados correctamente.');
    }

    protected function getRedirectUrl(): string
    {
        // Stay on the same page after save
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    /**
     * Ensure we always have a record to edit (singleton pattern).
     */
    protected function resolveRecord(int | string $key): \Illuminate\Database\Eloquent\Model
    {
        return \App\Models\GeneralSetting::get();
    }
}
