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
}
