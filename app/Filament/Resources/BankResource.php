<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankResource\Pages;
use App\Models\Bank;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankResource extends Resource
{
    protected static ?string $model = Bank::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Catálogos';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Bancos';

    protected static ?string $modelLabel = 'Banco';

    protected static ?string $pluralModelLabel = 'Bancos';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Banco')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('code')
                                    ->label('Código')
                                    ->required()
                                    ->requiredWithoutAll(['name'])
                                    ->maxLength(10)
                                    ->minLength(2)
                                    ->unique(ignoreRecord: true)
                                    ->alpha()
                                    ->helperText('Código único del banco')
                                    ->validationMessages([
                                        'required' => 'El código es obligatorio.',
                                        'max' => 'El código no puede tener más de :max caracteres.',
                                        'min' => 'El código debe tener al menos :min caracteres.',
                                        'unique' => 'Este código ya está registrado.',
                                        'alpha' => 'El código solo puede contener letras.',
                                    ]),

                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->minLength(3)
                                    ->unique(ignoreRecord: true)
                                    ->regex('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/')
                                    ->validationMessages([
                                        'required' => 'El nombre es obligatorio.',
                                        'max' => 'El nombre no puede tener más de :max caracteres.',
                                        'min' => 'El nombre debe tener al menos :min caracteres.',
                                        'unique' => 'Este nombre de banco ya está registrado.',
                                        'regex' => 'El nombre solo puede contener letras y espacios.',
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBanks::route('/'),
            'create' => Pages\CreateBank::route('/create'),
            'edit' => Pages\EditBank::route('/{record}/edit'),
        ];
    }
}
