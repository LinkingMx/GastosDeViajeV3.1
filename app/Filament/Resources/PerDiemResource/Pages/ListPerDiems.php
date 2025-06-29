<?php

namespace App\Filament\Resources\PerDiemResource\Pages;

use App\Filament\Resources\PerDiemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerDiems extends ListRecords
{
    protected static string $resource = PerDiemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo Viático')
                ->color('primary'),
        ];
    }

    public function getTitle(): string
    {
        return 'Viáticos';
    }
}
