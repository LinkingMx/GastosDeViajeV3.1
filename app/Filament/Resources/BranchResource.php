<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Sucursales';

    protected static ?string $modelLabel = 'Sucursal';

    protected static ?string $pluralModelLabel = 'Sucursales';

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Sucursal')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('ceco')
                                    ->label('Centro de Costo')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Código único del centro de costo')
                                    ->rule('min:3')
                                    ->rule('regex:/^[^\s]+$/')
                                    ->validationMessages([
                                        'min' => 'El código de centro de costo debe tener al menos 3 caracteres.',
                                        'regex' => 'El código de centro de costo no puede contener espacios.',
                                    ]),
                            ]),

                        Forms\Components\TextInput::make('tax_id')
                            ->label('RFC')
                            ->maxLength(13)
                            ->minLength(12)
                            ->helperText('RFC de 12 ó 13 caracteres (opcional)')
                            ->rule('regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/')
                            ->validationMessages([
                                'regex' => 'El formato del RFC no es correcto.',
                                'min' => 'El RFC debe tener al menos 12 caracteres.',
                                'max' => 'El RFC no puede tener más de 13 caracteres.',
                            ])
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    if (empty($value)) {
                                        return; // RFC es opcional
                                    }

                                    // Limpiar RFC
                                    $rfc = preg_replace('/[\s\-]/', '', strtoupper($value));

                                    // Validar longitud
                                    if (strlen($rfc) !== 12 && strlen($rfc) !== 13) {
                                        $fail('El RFC debe tener 12 caracteres (persona moral) o 13 caracteres (persona física).');
                                        return;
                                    }

                                    // Validar formato
                                    if (!preg_match('/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc)) {
                                        $fail('El formato del RFC no es correcto.');
                                        return;
                                    }
                                };
                            })
                            ->formatStateUsing(fn ($state) => $state ? strtoupper(str_replace([' ', '-'], '', $state)) : $state)
                            ->dehydrateStateUsing(fn ($state) => $state ? strtoupper(str_replace([' ', '-'], '', $state)) : $state),
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

                Tables\Columns\TextColumn::make('ceco')
                    ->label('Centro de Costo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tax_id')
                    ->label('RFC')
                    ->searchable()
                    ->placeholder('Sin RFC'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('tax_id')
                    ->label('RFC')
                    ->nullable()
                    ->trueLabel('Con RFC')
                    ->falseLabel('Sin RFC')
                    ->placeholder('Todos'),
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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
