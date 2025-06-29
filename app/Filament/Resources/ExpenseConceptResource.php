<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseConceptResource\Pages;
use App\Models\ExpenseConcept;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseConceptResource extends Resource
{
    protected static ?string $model = ExpenseConcept::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Conceptos de Gasto';

    protected static ?string $modelLabel = 'Concepto de Gasto';

    protected static ?string $pluralModelLabel = 'Conceptos de Gasto';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Concepto')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_unmanaged')
                            ->label('No Gestionado')
                            ->helperText('Permite que los usuarios agreguen detalles personalizados'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->placeholder('Sin descripción'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Gestionado' => 'success',
                        'No Gestionado' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('details_count')
                    ->label('Detalles')
                    ->counts('details')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_unmanaged')
                    ->label('Tipo de Concepto')
                    ->placeholder('Todos')
                    ->trueLabel('No Gestionados')
                    ->falseLabel('Gestionados'),

                Tables\Filters\Filter::make('has_details')
                    ->label('Con Detalles')
                    ->query(fn ($query) => $query->whereHas('details')),

                Tables\Filters\Filter::make('without_details')
                    ->label('Sin Detalles')
                    ->query(fn ($query) => $query->whereDoesntHave('details')),
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
            'index' => Pages\ListExpenseConcepts::route('/'),
            'create' => Pages\CreateExpenseConcept::route('/create'),
            'edit' => Pages\EditExpenseConcept::route('/{record}/edit'),
        ];
    }
}
