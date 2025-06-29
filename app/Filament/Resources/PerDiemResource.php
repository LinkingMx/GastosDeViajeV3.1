<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerDiemResource\Pages;
use App\Models\PerDiem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PerDiemResource extends Resource
{
    protected static ?string $model = PerDiem::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Viáticos';

    protected static ?string $modelLabel = 'Viático';

    protected static ?string $pluralModelLabel = 'Viáticos';

    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Viático')
                    ->schema([
                        Forms\Components\Select::make('position_id')
                            ->label('Posición')
                            ->relationship('position', 'name')
                            ->searchable()
                            ->required()
                            ->helperText('Posición laboral a la que aplica este viático'),

                        Forms\Components\Select::make('detail_id')
                            ->label('Detalle de Gasto')
                            ->relationship('detail', 'name')
                            ->searchable()
                            ->required()
                            ->helperText('Detalle específico de gasto asociado'),

                        Forms\Components\Select::make('scope')
                            ->label('Alcance')
                            ->options([
                                'domestic' => 'Nacional',
                                'foreign' => 'Extranjero',
                            ])
                            ->required()
                            ->default('domestic')
                            ->helperText('Alcance geográfico del viático'),

                        Forms\Components\Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'MXN' => 'MXN - Peso Mexicano',
                                'USD' => 'USD - Dólar Estadounidense',
                                'EUR' => 'EUR - Euro',
                                'CAD' => 'CAD - Dólar Canadiense',
                                'GBP' => 'GBP - Libra Esterlina',
                            ])
                            ->required()
                            ->default('MXN')
                            ->helperText('Moneda en la que se pagará el viático'),

                        Forms\Components\TextInput::make('amount')
                            ->label('Monto Diario')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->prefix('$')
                            ->helperText('Cantidad diaria del viático'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Vigencia')
                    ->schema([
                        Forms\Components\DatePicker::make('valid_from')
                            ->label('Válido Desde')
                            ->required()
                            ->default(now())
                            ->helperText('Fecha de inicio de validez'),

                        Forms\Components\DatePicker::make('valid_to')
                            ->label('Válido Hasta')
                            ->after('valid_from')
                            ->helperText('Fecha de fin de validez (opcional para vigencia indefinida)'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('position.name')
                    ->label('Posición')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('detail.name')
                    ->label('Detalle')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('scope')
                    ->label('Alcance')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'domestic' => 'Nacional',
                        'foreign' => 'Extranjero',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'domestic',
                        'primary' => 'foreign',
                    ]),

                Tables\Columns\TextColumn::make('currency')
                    ->label('Moneda')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto Diario')
                    ->money('MXN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Válido Desde')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('valid_to')
                    ->label('Válido Hasta')
                    ->date()
                    ->placeholder('Indefinido')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('position_id')
                    ->label('Posición')
                    ->relationship('position', 'name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('detail_id')
                    ->label('Detalle')
                    ->relationship('detail', 'name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('scope')
                    ->label('Alcance')
                    ->options([
                        'domestic' => 'Nacional',
                        'foreign' => 'Extranjero',
                    ]),

                Tables\Filters\SelectFilter::make('currency')
                    ->label('Moneda')
                    ->options([
                        'MXN' => 'MXN',
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                        'CAD' => 'CAD',
                        'GBP' => 'GBP',
                    ]),

                Tables\Filters\Filter::make('current_valid')
                    ->label('Vigentes Actualmente')
                    ->query(fn ($query) => $query->current()),

                Tables\Filters\Filter::make('expired')
                    ->label('Expirados')
                    ->query(fn ($query) => $query->expired()),

                Tables\Filters\Filter::make('future')
                    ->label('Futuros')
                    ->query(fn ($query) => $query->future()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('gray'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('position.name', 'asc');
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
            'index' => Pages\ListPerDiems::route('/'),
            'create' => Pages\CreatePerDiem::route('/create'),
            'edit' => Pages\EditPerDiem::route('/{record}/edit'),
        ];
    }
}
