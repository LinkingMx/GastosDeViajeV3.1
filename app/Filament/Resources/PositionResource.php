<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PositionResource\Pages;
use App\Models\Position;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Posiciones';

    protected static ?string $modelLabel = 'Posición';

    protected static ?string $pluralModelLabel = 'Posiciones';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Posición')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Nombre único de la posición laboral')
                            ->autocapitalize('words'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Descripción detallada de las responsabilidades y funciones'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Nombre')
                ->searchable()
                ->sortable()
                ->weight('medium')
                ->copyable()
                ->copyMessage('Nombre copiado')
                ->copyMessageDuration(1500),

            Tables\Columns\TextColumn::make('description')
                ->label('Descripción')
                ->searchable()
                ->limit(50)
                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                    $state = $column->getState();
                    if (strlen($state) <= 50) {
                        return null;
                    }

                    return $state;
                })
                ->wrap(),

            Tables\Columns\TextColumn::make('users_count')
                ->label('Empleados')
                ->counts('users')
                ->sortable()
                ->alignCenter()
                ->badge()
                ->color(function ($state): string {
                    if ($state === 0) {
                        return 'gray';
                    }
                    if ($state <= 5) {
                        return 'success';
                    }
                    if ($state <= 15) {
                        return 'warning';
                    }

                    return 'danger';
                }),

            Tables\Columns\TextColumn::make('per_diems_count')
                ->label('Viáticos')
                ->counts('perDiems')
                ->sortable()
                ->alignCenter()
                ->badge()
                ->color(function ($state): string {
                    if ($state === 0) {
                        return 'gray';
                    }

                    return 'primary';
                }),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Creado')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('updated_at')
                ->label('Actualizado')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])->filters([
            Tables\Filters\Filter::make('with_employees')
                ->label('Con Empleados')
                ->query(fn ($query) => $query->whereHas('users')),

            Tables\Filters\Filter::make('without_employees')
                ->label('Sin Empleados')
                ->query(fn ($query) => $query->whereDoesntHave('users')),

            Tables\Filters\Filter::make('with_per_diems')
                ->label('Con Viáticos')
                ->query(fn ($query) => $query->whereHas('perDiems')),

            Tables\Filters\Filter::make('without_per_diems')
                ->label('Sin Viáticos')
                ->query(fn ($query) => $query->whereDoesntHave('perDiems')),
        ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('gray'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Posición')
                    ->modalDescription('¿Estás seguro de que deseas eliminar esta posición? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar Posiciones')
                        ->modalDescription('¿Estás seguro de que deseas eliminar las posiciones seleccionadas? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-briefcase')
            ->emptyStateHeading('No hay posiciones')
            ->emptyStateDescription('Comienza creando tu primera posición laboral.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva Posición'),
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
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
        ];
    }
}
