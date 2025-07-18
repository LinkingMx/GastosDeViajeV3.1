<?php

namespace App\Filament\Resources\MailConfigurationResource\Pages;

use App\Filament\Resources\MailConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMailConfiguration extends EditRecord
{
    protected static string $resource = MailConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
