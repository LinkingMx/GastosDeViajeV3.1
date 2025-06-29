<?php

namespace App\Filament\Resources\ExpenseDetailResource\Pages;

use App\Filament\Resources\ExpenseDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpenseDetail extends EditRecord
{
    protected static string $resource = ExpenseDetailResource::class;

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
            ->icon('heroicon-o-list-bullet')
            ->title('Detalle actualizado correctamente')
            ->body('Los datos del detalle de gasto han sido actualizados exitosamente.');
    }
}
