<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->icon('heroicon-o-building-office')
            ->iconColor('primary')
            ->title('Sucursal creada correctamente')
            ->body('La sucursal ha sido registrada y guardada exitosamente.');
    }

    public static function getCreateButtonLabel(): string
    {
        return 'Nueva sucursal';
    }

    public static function getCreateButtonIcon(): ?string
    {
        return 'heroicon-o-plus';
    }
}
