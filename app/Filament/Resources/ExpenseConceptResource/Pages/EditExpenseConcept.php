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
            ->icon('heroicon-o-clipboard-document-list')
            ->title('Concepto actualizado correctamente')
            ->body('Los datos del concepto de gasto han sido actualizados exitosamente.');
    }

    public function getHeading(): string
    {
        return 'Editar Concepto de Gasto';
    }

    public function getSubheading(): ?string
    {
        return 'Modifica la información de este concepto de gasto. Los conceptos son categorías que agrupan los gastos generales como Alimentación, Hospedaje, Transporte, etc.';
    }
}
