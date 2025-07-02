<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Personal')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre Completo')
                            ->placeholder('Ej. Juan Pérez')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->placeholder('usuario@empresa.com')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),

                        Forms\Components\Select::make('roles')
                            ->label('Rol')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->required()
                            ->helperText('Selecciona uno o más roles para el usuario.')
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Contraseña')
                                    ->password()
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->minLength(8)
                                    ->helperText(fn (string $context): string => $context === 'create'
                                            ? 'Mínimo 8 caracteres'
                                            : 'Dejar en blanco para mantener la contraseña actual'
                                    )
                                    ->live(debounce: 500),

                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Confirmar Contraseña')
                                    ->password()
                                    ->required(fn (Forms\Get $get): bool => filled($get('password')))
                                    ->dehydrated(false)
                                    ->same('password')
                                    ->helperText('Debe coincidir con la contraseña')
                                    ->live(debounce: 500)
                                    ->visible(fn (Forms\Get $get): bool => filled($get('password')))
                                    ->rules([
                                        fn (Forms\Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                            if ($get('password') && $value && $get('password') !== $value) {
                                                $fail('Las contraseñas no coinciden.');
                                            }
                                        },
                                    ]),
                            ]),
                    ]),

                Forms\Components\Section::make('Información Organizacional')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('department_id')
                                    ->label('Departamento')
                                    ->relationship('department', 'name')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('position_id')
                                    ->label('Posición')
                                    ->relationship('position', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Forms\Components\Toggle::make('travel_team')
                            ->label('Miembro del Equipo de Viajes')
                            ->helperText('Este usuario tendrá acceso a funcionalidades especiales del sistema de viajes')
                            ->default(false),

                        Forms\Components\Toggle::make('treasury_team')
                            ->label('Miembro del Equipo de Tesorería')
                            ->helperText('Este usuario tendrá acceso a funcionalidades especiales del sistema de tesorería')
                            ->default(false),

                        Forms\Components\Toggle::make('override_authorization')
                            ->label('Autorización Especial')
                            ->helperText('Permite aprobar gastos sin seguir la jerarquía departamental')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if (! $state) {
                                    $set('override_authorizer_id', null);
                                }
                            }),

                        Forms\Components\Select::make('override_authorizer_id')
                            ->label('Autorizador Personalizado')
                            ->relationship('overrideAuthorizer', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get): bool => $get('override_authorization'))
                            ->required(fn (Forms\Get $get): bool => $get('override_authorization'))
                            ->helperText('Selecciona el usuario que podrá autorizar los gastos de este empleado'),
                    ]),

                Forms\Components\Section::make('Información Bancaria')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('bank_id')
                                    ->label('Banco')
                                    ->relationship('bank', 'name')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\TextInput::make('account_number')
                                    ->label('Número de Cuenta')
                                    ->maxLength(20),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('clabe')
                                    ->label('CLABE Interbancaria')
                                    ->maxLength(23)
                                    ->helperText('18 dígitos (se pueden incluir guiones para facilitar la lectura)')
                                    ->placeholder('000-000-00000000000-0')
                                    ->dehydrateStateUsing(fn ($state) => $state ? preg_replace('/[^\d]/', '', $state) : null)
                                    ->formatStateUsing(function ($state, $record) {
                                        if ($record && $state && strlen($state) === 18) {
                                            return substr($state, 0, 3).'-'.substr($state, 3, 3).'-'.substr($state, 6, 11).'-'.substr($state, 17, 1);
                                        }

                                        return $state;
                                    }),

                                Forms\Components\TextInput::make('rfc')
                                    ->label('RFC')
                                    ->maxLength(13)
                                    ->minLength(13),
                            ]),
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

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position.name')
                    ->label('Posición')
                    ->searchable()
                    ->sortable(),

                // Badge compacto que muestra ambos equipos
                Tables\Columns\TextColumn::make('travel_team')
                    ->label('Equipos')
                    ->getStateUsing(function ($record) {
                        $badges = [];
                        if ($record->travel_team) {
                            $badges[] = 'Viajes';
                        }
                        if ($record->treasury_team) {
                            $badges[] = 'Tesorería';
                        }

                        return empty($badges) ? 'Usuario Regular' : implode(' • ', $badges);
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->travel_team && $record->treasury_team) {
                            return 'info'; // Azul para ambos equipos
                        } elseif ($record->travel_team) {
                            return 'success'; // Verde para viajes
                        } elseif ($record->treasury_team) {
                            return 'warning'; // Amarillo para tesorería
                        }

                        return 'gray'; // Gris para usuarios regulares
                    }),

                Tables\Columns\TextColumn::make('bank.name')
                    ->label('Banco')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('override_authorization')
                    ->label('Autorización Especial')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Departamento')
                    ->relationship('department', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('position_id')
                    ->label('Posición')
                    ->relationship('position', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('override_authorization')
                    ->label('Autorización Especial')
                    ->placeholder('Todos')
                    ->trueLabel('Con autorización especial')
                    ->falseLabel('Autorización normal'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
