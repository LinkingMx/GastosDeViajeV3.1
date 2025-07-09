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
}
