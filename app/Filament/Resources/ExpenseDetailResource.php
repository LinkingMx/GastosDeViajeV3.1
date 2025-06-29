<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseDetailResource\Pages;
use App\Models\ExpenseDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseDetailResource extends Resource
{
    protected static ?string $model = ExpenseDetail::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Detalles de Gasto';

    protected static ?string $modelLabel = 'Detalle de Gasto';

    protected static ?string $pluralModelLabel = 'Detalles de Gasto';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Detalle')
                    ->schema([
                        Forms\Components\Select::make('concept_id')
                            ->label('Concepto de Gasto')
                            ->relationship('concept', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Solo se pueden agregar detalles a conceptos no gestionados'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Los detalles inactivos no aparecerán en los formularios de gastos'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('concept.name')
                    ->label('Concepto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->placeholder('Sin descripción'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('concept_id')
                    ->label('Concepto')
                    ->relationship('concept', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),

                Tables\Filters\Filter::make('unmanaged_only')
                    ->label('Solo No Gestionados')
                    ->query(fn ($query) => $query->whereHas('concept', fn ($q) => $q->where('is_unmanaged', true))),
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
            ->defaultSort('concept.name', 'asc');
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
            'index' => Pages\ListExpenseDetails::route('/'),
            'create' => Pages\CreateExpenseDetail::route('/create'),
            'edit' => Pages\EditExpenseDetail::route('/{record}/edit'),
        ];
    }
}
