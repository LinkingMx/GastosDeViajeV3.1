<?php

namespace App\Filament\Resources\ExpenseConceptResource\Pages;

use App\Filament\Resources\ExpenseConceptResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpenseConcept extends EditRecord
{
    protected static string $resource = ExpenseConceptResource::class;

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
            ->title('Concepto actualizado correctamente')
            ->icon('heroicon-o-clipboard-document-list')
            ->body('Los datos del concepto de gasto han sido actualizados exitosamente.')
            ->success();
    }
}
