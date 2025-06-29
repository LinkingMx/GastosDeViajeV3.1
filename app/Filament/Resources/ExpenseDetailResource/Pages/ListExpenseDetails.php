<?php

namespace App\Filament\Resources\ExpenseDetailResource\Pages;

use App\Filament\Resources\ExpenseDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenseDetails extends ListRecords
{
    protected static string $resource = ExpenseDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
