<?php

namespace App\Filament\Resources\MailConfigurationResource\Pages;

use App\Filament\Resources\MailConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMailConfigurations extends ListRecords
{
    protected static string $resource = MailConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
