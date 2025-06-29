<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-americas';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Países';

    protected static ?string $modelLabel = 'País';

    protected static ?string $pluralModelLabel = 'Países';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del País')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('iso2')
                                    ->label('Código ISO2')
                                    ->required()
                                    ->maxLength(2)
                                    ->minLength(2)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Código de 2 letras (ej: MX)'),

                                Forms\Components\TextInput::make('iso3')
                                    ->label('Código ISO3')
                                    ->required()
                                    ->maxLength(3)
                                    ->minLength(3)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Código de 3 letras (ej: MEX)'),

                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('default_currency')
                                    ->label('Moneda por Defecto')
                                    ->maxLength(3)
                                    ->helperText('Código de moneda ISO (ej: USD, MXN)'),

                                Forms\Components\Toggle::make('is_foreign')
                                    ->label('País Extranjero')
                                    ->helperText('Marcar si es un país extranjero'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('iso2')
                    ->label('ISO2')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('iso3')
                    ->label('ISO3')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('default_currency')
                    ->label('Moneda')
                    ->searchable()
                    ->placeholder('Sin moneda'),

                Tables\Columns\IconColumn::make('is_foreign')
                    ->label('Extranjero')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_foreign')
                    ->label('Tipo de País')
                    ->placeholder('Todos')
                    ->trueLabel('Extranjeros')
                    ->falseLabel('Nacional'),

                Tables\Filters\TernaryFilter::make('default_currency')
                    ->label('Moneda')
                    ->nullable()
                    ->trueLabel('Con moneda')
                    ->falseLabel('Sin moneda')
                    ->placeholder('Todos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
