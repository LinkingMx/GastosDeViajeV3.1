<?php

namespace App\Filament\Resources\AttachmentTypeResource\Pages;

use App\Filament\Resources\AttachmentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttachmentType extends EditRecord
{
    protected static string $resource = AttachmentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
