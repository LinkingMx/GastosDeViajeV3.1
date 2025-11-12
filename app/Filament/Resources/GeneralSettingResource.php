<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeneralSettingResource\Pages;
use App\Models\GeneralSetting;
use Filament\Forms;
use Filament\Forms\Form;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración de Viajes')
                    ->description('Configura los parámetros generales para las solicitudes de viaje')
                    ->schema([
                        Forms\Components\TextInput::make('dias_minimos_anticipacion')
                            ->label('Días Mínimos de Anticipación')
                            ->prefixIcon('heroicon-o-calendar')
                            ->placeholder('5')
                            ->helperText('Número de días mínimos de anticipación requeridos para crear una solicitud de viaje')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(30)
                            ->default(5),
                    ])
                    ->columns(1),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\EditGeneralSetting::route('/'),
        ];
    }
}
