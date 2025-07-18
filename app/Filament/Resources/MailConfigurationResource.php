<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailConfigurationResource\Pages;
use App\Models\MailConfiguration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MailConfigurationResource extends Resource
{
    protected static ?string $model = MailConfiguration::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Configuraciones avanzadas';

    protected static ?string $navigationLabel = 'Configuración de Correo';

    protected static ?string $modelLabel = 'Configuración de Correo';

    protected static ?string $pluralModelLabel = 'Configuraciones de Correo';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración del Servidor')
                    ->description('Configure los parámetros del servidor de correo')
                    ->schema([
                        Forms\Components\Select::make('mailer')
                            ->label('Tipo de Servidor')
                            ->options([
                                'smtp' => 'SMTP',
                                'sendmail' => 'Sendmail',
                                'mailgun' => 'Mailgun',
                                'ses' => 'Amazon SES',
                                'postmark' => 'Postmark',
                            ])
                            ->default('smtp')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('port', null)),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('host')
                                    ->label('Servidor SMTP')
                                    ->placeholder('smtp.gmail.com')
                                    ->required(fn (callable $get) => $get('mailer') === 'smtp')
                                    ->visible(fn (callable $get) => $get('mailer') === 'smtp'),

                                Forms\Components\TextInput::make('port')
                                    ->label('Puerto')
                                    ->numeric()
                                    ->placeholder('587')
                                    ->required(fn (callable $get) => $get('mailer') === 'smtp')
                                    ->visible(fn (callable $get) => $get('mailer') === 'smtp')
                                    ->default(fn (callable $get) => match($get('encryption')) {
                                        'tls' => 587,
                                        'ssl' => 465,
                                        default => 25,
                                    }),
                            ]),

                        Forms\Components\Select::make('encryption')
                            ->label('Encriptación')
                            ->options([
                                null => 'Ninguna',
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                            ])
                            ->visible(fn (callable $get) => $get('mailer') === 'smtp')
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('port', match($state) {
                                    'tls' => 587,
                                    'ssl' => 465,
                                    default => 25,
                                });
                            }),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('username')
                                    ->label('Usuario')
                                    ->autocomplete('off')
                                    ->required(fn (callable $get) => $get('mailer') === 'smtp'),

                                Forms\Components\TextInput::make('password')
                                    ->label('Contraseña')
                                    ->password()
                                    ->autocomplete('new-password')
                                    ->required(fn (callable $get) => $get('mailer') === 'smtp')
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                                    ->dehydrated(fn ($state) => filled($state)),
                            ]),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Configuración del Remitente')
                    ->description('Información del remitente por defecto')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('from_address')
                                    ->label('Correo del Remitente')
                                    ->email()
                                    ->required()
                                    ->placeholder('noreply@miempresa.com'),

                                Forms\Components\TextInput::make('from_name')
                                    ->label('Nombre del Remitente')
                                    ->required()
                                    ->placeholder('Sistema de Gastos de Viaje'),
                            ]),
                    ]),

                Forms\Components\Section::make('Información Adicional')
                    ->description('Configure el estado de la configuración y parámetros adicionales')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Configuración Activa')
                            ->helperText('Solo puede haber una configuración activa a la vez')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $record) {
                                if ($state && $record) {
                                    // Si se activa, desactivar las demás
                                    MailConfiguration::where('id', '!=', $record->id)
                                        ->where('is_active', true)
                                        ->update(['is_active' => false]);
                                }
                            }),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->placeholder('Ej: Configuración para correos de producción, credenciales renovadas el 15/01/2025...')
                            ->helperText('Documenta información importante sobre esta configuración de correo'),

                        Forms\Components\KeyValue::make('additional_settings')
                            ->label('Configuraciones Adicionales')
                            ->helperText('Parámetros específicos del proveedor de correo. Ejemplos: domain→mg.miempresa.com (Mailgun), region→us-east-1 (SES), timeout→30 (SMTP)')
                            ->addActionLabel('Agregar configuración')
                            ->keyLabel('Clave')
                            ->valueLabel('Valor')
                            ->deletable()
                            ->reorderable(),
                    ]),

                Forms\Components\Section::make('Estado de Prueba')
                    ->schema([
                        Forms\Components\Placeholder::make('test_status')
                            ->label('Última Prueba')
                            ->content(function ($record) {
                                if (!$record || !$record->last_tested_at) {
                                    return 'No se ha probado esta configuración';
                                }

                                $status = $record->test_successful 
                                    ? '<span class="text-success-600 dark:text-success-400">✓ Exitosa</span>'
                                    : '<span class="text-danger-600 dark:text-danger-400">✗ Fallida</span>';

                                return new \Illuminate\Support\HtmlString(
                                    $status . ' - ' . $record->last_tested_at->format('d/m/Y H:i')
                                );
                            }),

                        Forms\Components\Textarea::make('test_message')
                            ->label('Mensaje de la Prueba')
                            ->disabled()
                            ->visible(fn ($record) => $record && $record->last_tested_at)
                            ->rows(2),
                    ])
                    ->visible(fn ($record) => $record && $record->exists),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mailer')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'smtp' => 'primary',
                        'sendmail' => 'warning',
                        'mailgun' => 'success',
                        'ses' => 'info',
                        'postmark' => 'purple',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('host')
                    ->label('Servidor')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('from_address')
                    ->label('Correo Remitente')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('test_status')
                    ->label('Estado de Prueba')
                    ->getStateUsing(function ($record) {
                        if (!$record->last_tested_at) {
                            return 'Sin probar';
                        }
                        return $record->test_successful ? 'Exitosa' : 'Fallida';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (!$record->last_tested_at) {
                            return 'gray';
                        }
                        return $record->test_successful ? 'success' : 'danger';
                    })
                    ->icon(function ($record) {
                        if (!$record->last_tested_at) {
                            return 'heroicon-o-question-mark-circle';
                        }
                        return $record->test_successful 
                            ? 'heroicon-o-check-circle' 
                            : 'heroicon-o-x-circle';
                    }),

                Tables\Columns\TextColumn::make('last_tested_at')
                    ->label('Última Prueba')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Nunca')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updater.name')
                    ->label('Modificado por')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Estado')
                    ->options([
                        true => 'Activa',
                        false => 'Inactiva',
                    ]),
                
                Tables\Filters\SelectFilter::make('test_successful')
                    ->label('Prueba')
                    ->options([
                        true => 'Exitosa',
                        false => 'Fallida',
                    ])
                    ->placeholder('Todas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('test')
                    ->label('Probar')
                    ->icon('heroicon-o-beaker')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->label('Correo de Prueba')
                            ->email()
                            ->default(fn () => auth()->user()->email)
                            ->required()
                            ->helperText('Se enviará un correo de prueba a esta dirección'),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            $record->testConfiguration($data['email']);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Prueba exitosa')
                                ->body('El correo de prueba fue enviado correctamente a ' . $data['email'])
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Prueba fallida')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('activate')
                    ->label('Activar')
                    ->icon('heroicon-o-bolt')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading('Activar configuración')
                    ->modalDescription('¿Estás seguro de que deseas activar esta configuración? Se desactivará cualquier otra configuración activa.')
                    ->action(function ($record) {
                        $record->markAsActive();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Configuración activada')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => !$record->is_active),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            // No permitir eliminar configuraciones activas
                            $activeCount = $records->where('is_active', true)->count();
                            if ($activeCount > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title('No se puede eliminar')
                                    ->body('No se pueden eliminar configuraciones activas.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            $records->each->delete();
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Configuraciones eliminadas')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('Sin configuraciones de correo')
            ->emptyStateDescription('Crea una nueva configuración de correo para comenzar.')
            ->emptyStateIcon('heroicon-o-envelope');
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
            'index' => Pages\ListMailConfigurations::route('/'),
            'create' => Pages\CreateMailConfiguration::route('/create'),
            'edit' => Pages\EditMailConfiguration::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'admin']);
    }
}