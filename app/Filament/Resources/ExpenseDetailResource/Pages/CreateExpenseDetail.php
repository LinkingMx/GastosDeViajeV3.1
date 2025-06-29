<?php

namespace App\Filament\Resources\ExpenseDetailResource\Pages;

use App\Filament\Resources\ExpenseDetailResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExpenseDetail extends CreateRecord
{
    protected static string $resource = ExpenseDetailResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->title('Detalle creado correctamente')
            ->icon('heroicon-o-list-bullet')
            ->body('El detalle de gasto ha sido registrado y guardado exitosamente.')
            ->success();
    }
}
