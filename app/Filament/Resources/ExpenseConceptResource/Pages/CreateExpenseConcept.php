<?php

namespace App\Filament\Resources\ExpenseConceptResource\Pages;

use App\Filament\Resources\ExpenseConceptResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExpenseConcept extends CreateRecord
{
    protected static string $resource = ExpenseConceptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->title('Concepto creado correctamente')
            ->icon('heroicon-o-clipboard-document-list')
            ->body('El concepto de gasto ha sido registrado y guardado exitosamente.')
            ->success();
    }
}
