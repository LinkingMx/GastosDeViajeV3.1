<?php

namespace App\Filament\Resources\ExpenseVerificationResource\Pages;

use App\Filament\Resources\ExpenseVerificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpenseVerification extends EditRecord
{
    protected static string $resource = ExpenseVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->icon('heroicon-o-clipboard-document-check')
            ->title('Comprobación actualizada')
            ->body('La comprobación de gastos ' . $this->record->folio . ' ha sido actualizada exitosamente.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Para manejar correctamente la eliminación de comprobantes no deducibles
        // Filament no elimina automáticamente los registros relacionados cuando se quitan del repeater
        if ($this->record && isset($data['nonDeductibleReceipts'])) {
            // Obtener IDs de los comprobantes actuales en el formulario
            $currentIds = collect($data['nonDeductibleReceipts'])
                ->filter(fn($item) => isset($item['id']))
                ->pluck('id')
                ->toArray();
            
            // Eliminar comprobantes no deducibles que ya no están en el formulario
            $this->record->nonDeductibleReceipts()
                ->whereNotIn('id', $currentIds)
                ->delete();
        }

        // Para manejar comprobantes fiscales también
        if ($this->record && isset($data['fiscalReceipts'])) {
            $currentFiscalIds = collect($data['fiscalReceipts'])
                ->filter(fn($item) => isset($item['id']))
                ->pluck('id')
                ->toArray();
            
            $this->record->fiscalReceipts()
                ->whereNotIn('id', $currentFiscalIds)
                ->delete();
        }

        return $data;
    }
}
