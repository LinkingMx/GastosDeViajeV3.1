<?php

namespace App\Filament\Resources\ExpenseVerificationResource\Pages;

use App\Filament\Resources\ExpenseVerificationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExpenseVerification extends CreateRecord
{
    protected static string $resource = ExpenseVerificationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->icon('heroicon-o-clipboard-document-check')
            ->title('Comprobación creada')
            ->body('La comprobación de gastos ha sido creada exitosamente con folio: ' . $this->record->folio);
    }
}
