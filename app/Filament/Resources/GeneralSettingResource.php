<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeneralSettingResource\Pages;
use App\Models\GeneralSetting;
use Filament\Resources\Resource;

class GeneralSettingResource extends Resource
{
    protected static ?string $model = GeneralSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Configuraciones Generales';

    protected static ?string $modelLabel = 'Configuración General';

    protected static ?string $pluralModelLabel = 'Configuraciones Generales';

    protected static ?int $navigationSort = 100;

    public static function getPages(): array
    {
        return [
            'index' => Pages\EditGeneralSetting::route('/'),
        ];
    }
}
