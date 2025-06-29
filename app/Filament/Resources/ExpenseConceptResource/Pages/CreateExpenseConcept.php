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
            ->icon('heroicon-o-clipboard-document-list')
            ->title('Concepto creado correctamente')
            ->body('El concepto de gasto ha sido registrado y guardado exitosamente.');
    }

    public static function getCreateButtonLabel(): string
    {
        return 'Nuevo concepto';
    }

    public static function getCreateButtonIcon(): ?string
    {
        return 'heroicon-o-plus';
    }
}
