<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseVerificationResource\Pages;
use App\Filament\Resources\ExpenseVerificationResource\Pages\ListActiveExpenseVerifications;
use App\Filament\Resources\ExpenseVerificationResource\Pages\ListHistoricalExpenseVerifications;
use App\Models\ExpenseVerification;
use App\Models\TravelRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ExpenseVerificationResource extends Resource
{
    protected static ?string $model = ExpenseVerification::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Comprobaciones de Gastos';

    protected static ?string $modelLabel = 'Comprobación de Gastos';

    protected static ?string $pluralModelLabel = 'Comprobaciones de Gastos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Comprobación')
                    ->schema([
                        Forms\Components\Select::make('travel_request_id')
                            ->label('Solicitud de Viaje')
                            ->options(function () {
                                return TravelRequest::with(['user', 'destinationCountry'])
                                    ->where('user_id', auth()->id()) // Solo mis propias solicitudes
                                    ->where('advance_deposit_made', true)
                                    ->whereIn('status', ['travel_approved', 'pending_verification']) // Solo solicitudes aprobadas finalmente o por comprobar
                                    ->get()
                                    ->mapWithKeys(function ($request) {
                                        $departureDate = $request->departure_date ? $request->departure_date->format('d/m/Y') : 'Sin fecha';
                                        $destination = $request->destination_city
                                            ? $request->destination_city.', '.($request->destinationCountry?->name ?? 'Sin país')
                                            : ($request->destinationCountry?->name ?? 'Sin destino');

                                        $label = $request->folio.' - '.$departureDate.' - '.$destination;

                                        return [$request->id => $label];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Solo se muestran solicitudes aprobadas finalmente con depósito de anticipo confirmado'),

                        Forms\Components\TextInput::make('uuid')
                            ->label('Folio UUID')
                            ->disabled()
                            ->helperText('Se generará automáticamente al crear la comprobación')
                            ->hiddenOn('create'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Información de la Solicitud')
                    ->schema([
                        Forms\Components\View::make('filament.components.travel-request-summary')
                            ->viewData(function (?ExpenseVerification $record) {
                                if (! $record || ! $record->travelRequest) {
                                    return [
                                        'request' => null,
                                        'message' => 'Selecciona una solicitud de viaje para ver sus detalles.',
                                    ];
                                }

                                return [
                                    'request' => $record->travelRequest,
                                    'message' => null,
                                ];
                            })
                            ->columnSpanFull(),
                    ])
                    ->hiddenOn('create')
                    ->collapsible(),

                Forms\Components\Section::make('Comprobantes No Deducibles')
                    ->schema([
                        Forms\Components\Repeater::make('receipts')
                            ->relationship('nonDeductibleReceipts')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('supplier_name')
                                            ->label('Proveedor')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Nombre del proveedor o comercio'),

                                        Forms\Components\DatePicker::make('receipt_date')
                                            ->label('Fecha del Comprobante')
                                            ->required()
                                            ->default(now())
                                            ->maxDate(now()),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('total_amount')
                                            ->label('Importe Total')
                                            ->numeric()
                                            ->required()
                                            ->prefix('$')
                                            ->step(0.01)
                                            ->minValue(0.01)
                                            ->placeholder('0.00'),

                                        Forms\Components\Select::make('expense_detail_id')
                                            ->label('Concepto de Gasto a Comprobar')
                                            ->relationship('expenseDetail', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Seleccionar concepto de gasto')
                                            ->helperText('Selecciona el detalle de gasto específico que este comprobante está cubriendo')
                                            ->getOptionLabelFromRecordUsing(function ($record) {
                                                return $record->name . ' (' . $record->concept->name . ')';
                                            }),

                                        Forms\Components\Hidden::make('receipt_type')
                                            ->default('non_deductible'),
                                    ])
                                    ->columns(3),

                                Forms\Components\FileUpload::make('photo_file_path')
                                    ->label('Foto del Comprobante')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('expense-receipts/photos')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('applied_amount')
                                    ->label('Monto Aplicado al Gasto')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->minValue(0.01)
                                    ->placeholder('0.00')
                                    ->helperText('Monto específico que se aplica al concepto seleccionado (puede ser menor al total)')
                                    ->visible(fn ($get) => !empty($get('expense_detail_id')))
                                    ->live(),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Notas (Opcional)')
                                    ->rows(2)
                                    ->placeholder('Detalles adicionales del gasto...')
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->cloneable()
                            ->addActionLabel('Agregar Comprobante No Deducible')
                            ->itemLabel(function (array $state): ?string {
                                $label = ($state['supplier_name'] ?? 'Nuevo comprobante') . 
                                        ' - $' . number_format($state['total_amount'] ?? 0, 2);
                                
                                if (!empty($state['expense_detail_id'])) {
                                    $expenseDetail = \App\Models\ExpenseDetail::with('concept')->find($state['expense_detail_id']);
                                    if ($expenseDetail) {
                                        $label .= ' → ' . $expenseDetail->name . ' (' . $expenseDetail->concept->name . ')';
                                    }
                                }
                                
                                return $label;
                            })
                            ->columns(2)
                            ->defaultItems(0)
                            ->reorderableWithButtons()
                            ->deleteAction(
                                fn ($action) => $action
                                    ->requiresConfirmation()
                                    ->modalHeading('Eliminar comprobante')
                                    ->modalDescription('¿Estás seguro de que deseas eliminar este comprobante? Esta acción no se puede deshacer.')
                            ),
                    ])
                    ->hiddenOn('create')
                    ->collapsible(),

                Forms\Components\Section::make('Comprobantes Fiscales CFDI')
                    ->icon('heroicon-o-document-arrow-up')
                    ->description('Carga archivos XML de CFDI para extraer automáticamente los datos fiscales y crear comprobantes. Cada concepto se puede categorizar por detalle de gasto.')
                    ->schema([
                        // Carga del archivo XML
                        Forms\Components\FileUpload::make('xml_upload')
                            ->label('Cargar archivo XML de CFDI')
                            ->acceptedFileTypes(['application/xml', 'text/xml', '.xml'])
                            ->directory('temp-xml')
                            ->helperText('Selecciona el archivo XML del comprobante fiscal CFDI para procesar automáticamente')
                            ->live()
                            ->afterStateUpdated(function ($state, $livewire) {
                                if ($state) {
                                    // Obtener el record de ExpenseVerification
                                    $record = $livewire->getRecord();
                                    if (!$record) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Error')
                                            ->body('Debes guardar la comprobación primero antes de cargar XMLs.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    try {
                                        $xmlContent = null;
                                        
                                        // Si es un TemporaryUploadedFile de Livewire
                                        if (is_object($state) && method_exists($state, 'get')) {
                                            \Log::info('Processing TemporaryUploadedFile object');
                                            $xmlContent = $state->get();
                                        }
                                        // Si es una cadena de texto (ruta del archivo)
                                        elseif (is_string($state)) {
                                            \Log::info('Processing file path string: ' . $state);
                                            if (file_exists($state)) {
                                                $xmlContent = file_get_contents($state);
                                            }
                                        }
                                        // Si es un array de archivos
                                        elseif (is_array($state) && !empty($state)) {
                                            \Log::info('Processing array of files');
                                            $firstFile = $state[0];
                                            if (is_object($firstFile) && method_exists($firstFile, 'get')) {
                                                $xmlContent = $firstFile->get();
                                            } elseif (is_string($firstFile) && file_exists($firstFile)) {
                                                $xmlContent = file_get_contents($firstFile);
                                            }
                                        }
                                        
                                        if ($xmlContent) {
                                            \Log::info('XML content loaded successfully, length: ' . strlen($xmlContent));
                                            static::processXmlAndCreateReceipts($xmlContent, $record, $livewire);
                                            
                                            // Limpiar el campo de upload después del procesamiento
                                            $livewire->data['xml_upload'] = null;
                                        } else {
                                            \Log::error('Failed to load XML content', [
                                                'state_type' => gettype($state),
                                                'state_class' => is_object($state) ? get_class($state) : null,
                                                'state_methods' => is_object($state) ? get_class_methods($state) : null
                                            ]);
                                            
                                            \Filament\Notifications\Notification::make()
                                                ->title('Error al cargar archivo')
                                                ->body('No se pudo leer el contenido del archivo XML. Verifica que sea un archivo válido.')
                                                ->warning()
                                                ->send();
                                        }
                                        
                                    } catch (\Exception $e) {
                                        \Log::error('Exception processing XML upload', [
                                            'error' => $e->getMessage(),
                                            'trace' => $e->getTraceAsString()
                                        ]);
                                        
                                        \Filament\Notifications\Notification::make()
                                            ->title('Error al procesar XML')
                                            ->body('Error: ' . $e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        // Secciones dinámicas agrupadas por XML/UUID
                        static::generateCfdiSectionsByUuid()
                    ])
                    ->hiddenOn('create')
                    ->collapsible(),

                Forms\Components\Section::make('Resumen de Comprobación')
                    ->icon('heroicon-o-chart-bar')
                    ->description('Seguimiento del estado de comprobación por categoría de gasto')
                    ->schema([
                        static::generateExpenseVerificationTable()
                    ])
                    ->hiddenOn('create')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('folio')
                    ->label('Folio')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('travelRequest.folio')
                    ->label('Solicitud de Viaje')
                    ->searchable()
                    ->sortable()
                    ->url(fn (ExpenseVerification $record) => route('filament.admin.resources.travel-requests.view', $record->travel_request_id)
                    ),

                Tables\Columns\TextColumn::make('travelRequest.user.name')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('travelRequest.destinationCountry.name')
                    ->label('Destino')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creado por')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('receipts_count')
                    ->label('Comprobantes')
                    ->counts('receipts')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('receipts_total')
                    ->label('Total Comprobado')
                    ->getStateUsing(function (ExpenseVerification $record) {
                        $total = $record->receipts->sum(function ($receipt) {
                            return $receipt->applied_amount ?? $receipt->total_amount;
                        });
                        return $total ? '$' . number_format($total, 2) : '$0.00';
                    })
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status_display')
                    ->label('Estado Autorización')
                    ->badge()
                    ->color(fn ($record) => match ($record ? $record->status : null) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'revision' => 'info',
                        'closed' => 'success',
                        default => 'gray',
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('status', $direction);
                    }),

                Tables\Columns\TextColumn::make('combined_status_display')
                    ->label('Estado General')
                    ->badge()
                    ->color(fn ($record) => match ($record ? $record->reimbursement_status ?? $record->status : null) {
                        'pending_reimbursement' => 'warning',
                        'reimbursed' => 'success',
                        'closed' => 'success',
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        'draft' => 'gray',
                        'revision' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('reimbursement_info')
                    ->label('Info Reembolso')
                    ->getStateUsing(function (ExpenseVerification $record) {
                        if ($record->needsReimbursement()) {
                            $amount = $record->getReimbursementAmountNeeded();
                            return '$' . number_format($amount, 2);
                        }
                        return 'No necesario';
                    })
                    ->badge()
                    ->color(fn ($record) => $record->needsReimbursement() ? 'warning' : 'gray')
                    ->tooltip(function (ExpenseVerification $record) {
                        if ($record->needsReimbursement()) {
                            $verified = $record->getTotalVerifiedAmount();
                            $advance = $record->getAdvanceDepositAmount();
                            return "Comprobado: $" . number_format($verified, 2) . " | Anticipo: $" . number_format($advance, 2);
                        }
                        return 'El monto comprobado no excede el anticipo';
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado Autorización')
                    ->options([
                        'draft' => 'Borrador',
                        'pending' => 'Pendiente de Autorización',
                        'approved' => 'Autorizada',
                        'rejected' => 'Rechazada',
                        'revision' => 'En Revisión',
                        'closed' => 'Cerrada',
                    ])
                    ->default(null),

                Tables\Filters\SelectFilter::make('reimbursement_status')
                    ->label('Estado Reembolso')
                    ->options([
                        'pending_reimbursement' => 'En Reembolso',
                        'reimbursed' => 'Reembolsada',
                    ])
                    ->default(null),

                Tables\Filters\SelectFilter::make('created_by')
                    ->label('Creado por')
                    ->options(User::all()->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([])
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
            'index' => Pages\ListExpenseVerifications::route('/'),
            'create' => Pages\CreateExpenseVerification::route('/create'),
            'edit' => Pages\EditExpenseVerification::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = \Illuminate\Support\Facades\Auth::user();

        // Los super_admin pueden ver todo
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Los miembros del equipo de viajes pueden ver comprobaciones pendientes de autorización y todas las históricas
        if ($user->isTravelTeamMember()) {
            return $query->where(function ($query) use ($user) {
                // Sus propias comprobaciones
                $query->whereHas('travelRequest', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                // O comprobaciones pendientes de autorización de cualquier usuario
                ->orWhere('status', 'pending')
                // O comprobaciones procesadas/históricas de cualquier usuario (para gestión)
                ->orWhereIn('status', ['approved', 'rejected', 'closed'])
                // O comprobaciones con estado de reembolso (históricas)
                ->orWhereNotNull('reimbursement_status');
            });
        }

        // Los miembros del equipo de tesorería pueden ver comprobaciones que necesitan reembolso y todas las históricas
        if ($user->isTreasuryTeamMember()) {
            return $query->where(function ($query) use ($user) {
                // Sus propias comprobaciones
                $query->whereHas('travelRequest', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                // O comprobaciones en proceso de reembolso
                ->orWhere('reimbursement_status', 'pending_reimbursement')
                // O comprobaciones reembolsadas para seguimiento
                ->orWhere('reimbursement_status', 'reimbursed')
                // O comprobaciones procesadas/históricas de cualquier usuario (para gestión)
                ->orWhereIn('status', ['approved', 'rejected', 'closed']);
            });
        }

        // Solo mostrar comprobaciones de gastos de mis propias solicitudes de viaje
        return $query->whereHas('travelRequest', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });
    }

    protected static function processXmlAndCreateReceipts($xmlContent, ExpenseVerification $record, $livewire)
    {
        try {
            if (!$xmlContent || trim($xmlContent) === '') {
                \Filament\Notifications\Notification::make()
                    ->title('Error al procesar XML')
                    ->body('El contenido del archivo XML está vacío')
                    ->danger()
                    ->send();
                return;
            }

            // Limpiar BOM y caracteres no válidos
            $xmlContent = preg_replace('/^[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $xmlContent);
            $xmlContent = str_replace(["\xEF\xBB\xBF", "\xFE\xFF", "\xFF\xFE"], '', $xmlContent);
            
            // Verificar que comience con una declaración XML válida
            if (!preg_match('/^\s*<\?xml/', $xmlContent)) {
                \Filament\Notifications\Notification::make()
                    ->title('Error al procesar XML')
                    ->body('El archivo no parece ser un XML válido (no comienza con <?xml)')
                    ->danger()
                    ->send();
                return;
            }
            
            // Deshabilitar errores de libxml temporalmente
            $previous_setting = libxml_use_internal_errors(true);
            libxml_clear_errors();
            
            $xml = simplexml_load_string($xmlContent);
            
            if ($xml === false) {
                $errors = libxml_get_errors();
                $errorMessage = 'El archivo XML no es válido';
                if (!empty($errors)) {
                    $errorDetails = [];
                    foreach ($errors as $error) {
                        $errorDetails[] = "Línea {$error->line}: {$error->message}";
                    }
                    $errorMessage .= ":\n" . implode("\n", $errorDetails);
                }
                
                \Filament\Notifications\Notification::make()
                    ->title('Error al procesar XML')
                    ->body($errorMessage)
                    ->danger()
                    ->send();
                
                libxml_use_internal_errors($previous_setting);
                return;
            }

            libxml_use_internal_errors($previous_setting);

            // Verificar que sea un CFDI válido
            $rootName = $xml->getName();
            if (!in_array($rootName, ['Comprobante', 'cfdi:Comprobante'])) {
                \Filament\Notifications\Notification::make()
                    ->title('Error al procesar XML')
                    ->body("El archivo XML no es un CFDI válido. Elemento raíz encontrado: {$rootName}")
                    ->danger()
                    ->send();
                return;
            }

            // Registrar namespaces comunes del CFDI
            $xml->registerXPathNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
            $xml->registerXPathNamespace('cfdi3', 'http://www.sat.gob.mx/cfd/3');
            $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');

            // Extraer datos con diferentes versiones de CFDI
            $comprobante = null;
            $emisor = null;
            $conceptos = [];
            $timbreFiscal = null;

            // CFDI 4.0
            if (!$comprobante) {
                $comprobante = $xml->xpath('//cfdi:Comprobante')[0] ?? null;
                $emisor = $xml->xpath('//cfdi:Emisor')[0] ?? null;
                $conceptos = $xml->xpath('//cfdi:Concepto') ?? [];
                $timbreFiscal = $xml->xpath('//tfd:TimbreFiscalDigital')[0] ?? null;
            }

            // CFDI 3.3 o anteriores
            if (!$comprobante) {
                $comprobante = $xml->xpath('//cfdi3:Comprobante')[0] ?? null;
                $emisor = $xml->xpath('//cfdi3:Emisor')[0] ?? null;
                $conceptos = $xml->xpath('//cfdi3:Concepto') ?? [];
                $timbreFiscal = $xml->xpath('//tfd:TimbreFiscalDigital')[0] ?? null;
            }

            // Si no encuentra con namespaces, intentar sin ellos
            if (!$comprobante) {
                $comprobante = $xml;
                $emisor = $xml->Emisor ?? null;
                $conceptos = $xml->xpath('//Concepto') ?? [];
                $timbreFiscal = $xml->xpath('//TimbreFiscalDigital')[0] ?? null;
            }

            // Verificar que se encontraron datos básicos
            if (!$comprobante) {
                \Filament\Notifications\Notification::make()
                    ->title('Error al procesar XML')
                    ->body('No se pudo encontrar el elemento Comprobante en el XML')
                    ->danger()
                    ->send();
                return;
            }

            // Extraer datos de la cabecera del CFDI
            $nombreEmisor = '';
            $rfcEmisor = '';
            
            if ($emisor) {
                $nombreEmisor = (string)($emisor['Nombre'] ?? $emisor['nombre'] ?? $emisor->Nombre ?? '');
                $rfcEmisor = (string)($emisor['Rfc'] ?? $emisor['rfc'] ?? $emisor->Rfc ?? '');
            }
            
            // Si no se encontró con los métodos directos, usar XPath
            if (!$nombreEmisor || !$rfcEmisor) {
                $nombreXPath = $xml->xpath('//cfdi:Emisor/@Nombre')[0] ?? $xml->xpath('//cfdi3:Emisor/@Nombre')[0] ?? null;
                $rfcXPath = $xml->xpath('//cfdi:Emisor/@Rfc')[0] ?? $xml->xpath('//cfdi3:Emisor/@Rfc')[0] ?? null;
                
                if ($nombreXPath) $nombreEmisor = (string)$nombreXPath;
                if ($rfcXPath) $rfcEmisor = (string)$rfcXPath;
            }

            // Fecha del comprobante
            $fecha = '';
            if ($comprobante) {
                $fecha = (string)($comprobante['Fecha'] ?? $comprobante['fecha'] ?? $comprobante->Fecha ?? '');
            }

            // UUID del timbre fiscal
            $uuid = '';
            if ($timbreFiscal) {
                $uuid = (string)($timbreFiscal['UUID'] ?? $timbreFiscal['uuid'] ?? $timbreFiscal->UUID ?? '');
            }

            // Crear ExpenseReceipts para cada concepto
            $conceptosCreados = 0;
            $montoTotal = 0;
            
            foreach ($conceptos as $concepto) {
                $descripcion = (string)($concepto['Descripcion'] ?? $concepto['descripcion'] ?? $concepto->Descripcion ?? '');
                $importe = (string)($concepto['Importe'] ?? $concepto['importe'] ?? $concepto->Importe ?? '0');
                
                if ($descripcion && $importe > 0) {
                    $receiptData = [
                        'receipt_type' => 'fiscal',
                        'supplier_name' => $nombreEmisor,
                        'supplier_rfc' => $rfcEmisor,
                        'receipt_date' => $fecha ? date('Y-m-d', strtotime($fecha)) : now()->format('Y-m-d'),
                        'total_amount' => floatval($importe),
                        'applied_amount' => floatval($importe), // Por defecto aplicar el importe completo
                        'uuid' => $uuid,
                        'concept' => $descripcion,
                        'expense_detail_id' => null, // Usuario debe categorizar
                        'notes' => null,
                    ];

                    $receipt = $record->fiscalReceipts()->create($receiptData);
                    $conceptosCreados++;
                    $montoTotal += floatval($importe);
                }
            }

            // Guardar también el archivo XML original
            if ($conceptosCreados > 0) {
                $fileName = 'CFDI_' . ($uuid ?: uniqid()) . '.xml';
                $permanentPath = 'expense-receipts/xml/' . $fileName;
                $permanentFullPath = storage_path('app/public/' . $permanentPath);
                
                // Crear directorio si no existe
                if (!file_exists(dirname($permanentFullPath))) {
                    mkdir(dirname($permanentFullPath), 0755, true);
                }
                
                file_put_contents($permanentFullPath, $xmlContent);
                
                // Actualizar el primer comprobante con la ruta del XML
                $firstReceipt = $record->fiscalReceipts()->where('receipt_type', 'fiscal')->where('uuid', $uuid)->first();
                if ($firstReceipt) {
                    $firstReceipt->update(['xml_file_path' => $permanentPath]);
                }
            }

            \Filament\Notifications\Notification::make()
                ->title('CFDI cargado exitosamente')
                ->body("Se crearon {$conceptosCreados} comprobante(s) fiscal(es) del CFDI de {$nombreEmisor} por un total de $" . number_format($montoTotal, 2) . ". Ahora puedes categorizar cada concepto.")
                ->success()
                ->duration(8000)
                ->send();

            // Forzar actualización del formulario y recargar datos
            $record->refresh();
            $record->load('fiscalReceipts');
            
            // Programar refresh de la página después de mostrar la notificación
            $livewire->js('setTimeout(() => { window.location.reload(); }, 2000);');

        } catch (\Exception $e) {
            \Log::error('Error processing XML: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            \Filament\Notifications\Notification::make()
                ->title('Error al procesar XML')
                ->body('Error técnico: ' . $e->getMessage())
                ->danger()
                ->duration(10000)
                ->send();
        }
    }

    protected static function generateCfdiSectionsByUuid()
    {
        return Forms\Components\Group::make()
            ->schema(function ($livewire) {
                $record = $livewire->getRecord();
                if (!$record) {
                    return [
                        Forms\Components\Placeholder::make('no_cfdis')
                            ->label('')
                            ->content(new HtmlString('<p class="text-gray-500 text-center py-4">No hay CFDIs cargados. Carga un archivo XML para ver los comprobantes.</p>'))
                    ];
                }

                // Agrupar comprobantes fiscales por UUID (cada XML)
                $cfdiGroups = $record->fiscalReceipts()
                    ->where('receipt_type', 'fiscal')
                    ->get()
                    ->groupBy('uuid');

                if ($cfdiGroups->isEmpty()) {
                    return [
                        Forms\Components\Placeholder::make('no_cfdis')
                            ->label('')
                            ->content(new HtmlString('<p class="text-gray-500 text-center py-4">No hay CFDIs cargados. Carga un archivo XML para ver los comprobantes.</p>'))
                    ];
                }

                $sections = [];
                
                foreach ($cfdiGroups as $uuid => $concepts) {
                    $firstConcept = $concepts->first();
                    $totalCfdi = $concepts->sum('total_amount');
                    $conceptCount = $concepts->count();
                    
                    // Crear título descriptivo para la sección
                    $supplierName = $firstConcept->supplier_name ?: 'Proveedor sin nombre';
                    $receiptDate = $firstConcept->receipt_date ? $firstConcept->receipt_date->format('d/m/Y') : 'Sin fecha';
                    $shortUuid = $uuid ? substr($uuid, 0, 8) . '...' : 'Sin UUID';
                    
                    $sectionTitle = "{$supplierName} - {$receiptDate} - {$shortUuid}";
                    $sectionDescription = "{$conceptCount} concepto(s) - Total: $" . number_format($totalCfdi, 2);

                    // Crear Repeater para los conceptos de este UUID específico
                    $sections[] = Forms\Components\Section::make($sectionTitle)
                        ->description($sectionDescription)
                        ->schema([
                            Forms\Components\Repeater::make("cfdi_concepts_{$uuid}")
                                ->relationship('fiscalReceipts', function ($query) use ($uuid) {
                                    return $query->where('uuid', $uuid)->where('receipt_type', 'fiscal');
                                })
                                ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                    $data['receipt_type'] = 'fiscal';
                                    return $data;
                                })
                                ->schema([
                                    // Información del concepto
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Textarea::make('concept')
                                                ->label('Concepto')
                                                ->disabled()
                                                ->rows(2)
                                                ->dehydrated()
                                                ->columnSpan(2),
                                        ]),
                                    
                                    // Campos editables  
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('total_amount')
                                                ->label('Importe CFDI')
                                                ->numeric()
                                                ->disabled()
                                                ->prefix('$')
                                                ->dehydrated(),
                                                
                                            Forms\Components\Select::make('expense_detail_id')
                                                ->label('Categoría de Gasto')
                                                ->relationship('expenseDetail', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Seleccionar concepto de gasto')
                                                ->helperText('Selecciona el detalle de gasto específico')
                                                ->getOptionLabelFromRecordUsing(function ($record) {
                                                    return $record->name . ' (' . $record->concept->name . ')';
                                                })
                                                ->live()
                                                ->afterStateUpdated(function ($state, $set, $get) {
                                                    if ($state && $get('total_amount')) {
                                                        $set('applied_amount', $get('total_amount'));
                                                    }
                                                }),
                                        ]),
                                    
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('applied_amount')
                                                ->label('Monto Aplicado')
                                                ->numeric()
                                                ->prefix('$')
                                                ->step(0.01)
                                                ->minValue(0.01)
                                                ->placeholder('0.00')
                                                ->helperText('Monto específico que se aplica al concepto')
                                                ->visible(fn ($get) => !empty($get('expense_detail_id')))
                                                ->live(),
                                                
                                            Forms\Components\TextInput::make('uuid')
                                                ->label('UUID CFDI')
                                                ->disabled()
                                                ->dehydrated()
                                                ->helperText('Identificador único del CFDI'),
                                        ]),
                                    
                                    Forms\Components\Textarea::make('notes')
                                        ->label('Notas (Opcional)')
                                        ->rows(2)
                                        ->placeholder('Comentarios adicionales...')
                                        ->columnSpanFull(),
                                        
                                    // Hidden fields
                                    Forms\Components\Hidden::make('receipt_type')
                                        ->default('fiscal'),
                                        
                                    Forms\Components\Hidden::make('supplier_name')
                                        ->dehydrated(),
                                        
                                    Forms\Components\Hidden::make('supplier_rfc')
                                        ->dehydrated(),
                                        
                                    Forms\Components\Hidden::make('receipt_date')
                                        ->dehydrated(),
                                ])
                                ->collapsible()
                                ->collapsed(true)
                                ->cloneable(false)
                                ->addable(false)
                                ->reorderableWithButtons()
                                ->itemLabel(function (array $state): ?string {
                                    $concept = $state['concept'] ?? 'Concepto';
                                    $amount = (float)($state['total_amount'] ?? 0);
                                    $shortConcept = strlen($concept) > 50 ? substr($concept, 0, 50) . '...' : $concept;
                                    
                                    $status = '';
                                    if (!empty($state['expense_detail_id'])) {
                                        $detail = \App\Models\ExpenseDetail::find($state['expense_detail_id']);
                                        $iconCheck = '<svg class="w-3 h-3 inline text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                        $status = $detail ? ' ' . $iconCheck . ' ' . $detail->name : ' ' . $iconCheck . ' Categorizado';
                                    } else {
                                        $iconClock = '<svg class="w-3 h-3 inline text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                                        $status = ' ' . $iconClock . ' Pendiente';
                                    }
                                    
                                    return $shortConcept . ' - $' . number_format($amount, 2) . $status;
                                })
                                ->deleteAction(
                                    fn ($action) => $action
                                        ->requiresConfirmation()
                                        ->modalHeading('Eliminar concepto CFDI')
                                        ->modalDescription('¿Estás seguro de que deseas eliminar este concepto? Esta acción no se puede deshacer.')
                                        ->modalSubmitActionLabel('Sí, eliminar')
                                        ->modalCancelActionLabel('Cancelar')
                                )
                                ->columns(2)
                                ->defaultItems(0)
                        ])
                        ->collapsible()
                        ->collapsed(true)
                        ->icon('heroicon-o-document-text');
                }

                return $sections;
            });
    }

    protected static function generateExpenseVerificationTable()
    {
        return Forms\Components\Placeholder::make('expense_verification_summary')
            ->label('')
            ->content(function ($livewire) {
                $record = $livewire->getRecord();
                if (!$record) {
                    return new HtmlString(
                        '<div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center">
                            <p class="text-gray-500 dark:text-gray-400">
                                Agrega comprobantes para ver el resumen de comprobación.
                            </p>
                        </div>'
                    );
                }

                $summary = $record->getExpenseVerificationSummary();
                
                if (empty($summary)) {
                    return new HtmlString(
                        '<div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center">
                            <p class="text-gray-500 dark:text-gray-400">
                                No hay categorías de gasto configuradas para esta solicitud.
                            </p>
                        </div>'
                    );
                }

                // Preparar datos para la tabla
                $tableData = [];
                $totalPending = 0;
                $totalProven = 0;
                $totalRemaining = 0;

                foreach ($summary as $category) {
                    $totalPending += $category['pending'];
                    $totalProven += $category['proven'];
                    $totalRemaining += $category['remaining'];
                    
                    // Calcular porcentaje
                    $percentage = $category['pending'] > 0 ? ($category['proven'] / $category['pending']) * 100 : 0;
                    
                    // Determinar estado
                    $status = 'pending';
                    $statusText = 'Pendiente';
                    $statusColor = 'warning';
                    $statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    
                    if ($category['remaining'] < 0) {
                        $status = 'excess';
                        $statusText = 'Exceso';
                        $statusColor = 'danger';
                        $statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>';
                    } elseif ($category['remaining'] == 0) {
                        $status = 'complete';
                        $statusText = 'Completo';
                        $statusColor = 'success';
                        $statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    } elseif ($percentage >= 50) {
                        $statusText = number_format($percentage, 0) . '%';
                        $statusColor = 'info';
                        $statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>';
                    }

                    $tableData[] = [
                        'category' => $category['name'],
                        'pending' => $category['pending'],
                        'proven' => $category['proven'],
                        'remaining' => $category['remaining'],
                        'percentage' => $percentage,
                        'status' => $status,
                        'status_text' => $statusText,
                        'status_color' => $statusColor,
                        'status_icon' => $statusIcon,
                    ];
                }

                // Calcular porcentaje total
                $totalPercentage = $totalPending > 0 ? ($totalProven / $totalPending) * 100 : 0;

                // Generar HTML de la tabla usando estilos de Filament
                $html = '
                <div class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
                    <div class="fi-ta-header-ctn divide-y divide-gray-200 dark:divide-white/10">
                        <div class="fi-ta-header-toolbar flex flex-col gap-3 p-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <h3 class="fi-ta-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                    Estado de Comprobación por Categoría
                                </h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
                        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                            <thead class="divide-y divide-gray-200 dark:divide-white/5">
                                <tr class="bg-gray-50 dark:bg-white/5">
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Categoría de Gasto
                                            </span>
                                        </span>
                                    </th>
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Por Comprobar
                                            </span>
                                        </span>
                                    </th>
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Comprobado
                                            </span>
                                        </span>
                                    </th>
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Pendiente
                                            </span>
                                        </span>
                                    </th>
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Estado
                                            </span>
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">';

                // Filas de datos
                foreach ($tableData as $row) {
                    $html .= '
                                <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="flex w-max">
                                                <div class="fi-ta-text grid w-full gap-y-1">
                                                    <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white font-medium">
                                                        ' . e($row['category']) . '
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="flex w-max">
                                                <div class="fi-ta-text grid w-full gap-y-1">
                                                    <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white font-mono">
                                                        $' . number_format($row['pending'], 2) . '
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="flex w-max">
                                                <div class="fi-ta-text grid w-full gap-y-1">
                                                    <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white font-mono">
                                                        $' . number_format($row['proven'], 2) . '
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="flex w-max">
                                                <div class="fi-ta-text grid w-full gap-y-1">
                                                    <span class="fi-ta-text-item-label text-sm leading-6 font-mono ' . 
                                                        ($row['remaining'] < 0 ? 'text-red-600 dark:text-red-400' : 
                                                         ($row['remaining'] == 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-950 dark:text-white')) . '">
                                                        $' . number_format($row['remaining'], 2) . '
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="flex w-max">
                                                <div class="fi-ta-text grid w-full gap-y-1">
                                                    <span class="fi-badge inline-flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-h-6 fi-color-' . $row['status_color'] . ' fi-badge-color-' . $row['status_color'] . '">
                                                        <span class="fi-badge-text">' . $row['status_icon'] . ' ' . e($row['status_text']) . '</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>';
                }

                // Fila de totales
                $html .= '
                                <tr class="fi-ta-row bg-gray-50 dark:bg-white/5 font-semibold">
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="flex w-max">
                                                <div class="fi-ta-text grid w-full gap-y-1">
                                                    <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white font-bold">
                                                        TOTALES
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="flex w-max">
                                                <div class="fi-ta-text grid w-full gap-y-1">
                                                    <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white font-mono font-bold">
                                                        $' . number_format($totalPending, 2) . '
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="flex w-max">
                                                <div class="fi-ta-text grid w-full gap-y-1">
                                                    <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white font-mono font-bold">
                                                        $' . number_format($totalProven, 2) . '
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="flex w-max">
                                                <div class="fi-ta-text grid w-full gap-y-1">
                                                    <span class="fi-ta-text-item-label text-sm leading-6 font-mono font-bold ' . 
                                                        ($totalRemaining < 0 ? 'text-red-600 dark:text-red-400' : 
                                                         ($totalRemaining == 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-950 dark:text-white')) . '">
                                                        $' . number_format($totalRemaining, 2) . '
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="flex w-max">
                                                <div class="fi-ta-text grid w-full gap-y-1">
                                                    <span class="fi-badge inline-flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-h-6 fi-color-info fi-badge-color-info">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                                        <span class="fi-badge-text">' . number_format($totalPercentage, 0) . '%</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Footer con notas informativas -->
                    <div class="fi-ta-footer-ctn border-t border-gray-200 px-3 py-3 dark:border-white/10 sm:px-6">
                        <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <strong>Cómo interpretar esta tabla:</strong>
                            </div>
                            <p>• <strong>Por Comprobar:</strong> Monto asignado originalmente (gastos personalizados + viáticos)</p>
                            <p>• <strong>Comprobado:</strong> Suma de montos aplicados de comprobantes categorizados</p>
                            <p>• <strong>Pendiente:</strong> Diferencia entre lo asignado y lo comprobado</p>
                            <div class="flex items-center gap-2 mt-2">
                                <strong>Estados:</strong>
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Completo
                                </span>
                                |
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                                    Exceso
                                </span>
                                |
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                    Porcentaje
                                </span>
                                |
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Pendiente
                                </span>
                            </div>
                        </div>
                    </div>
                </div>';

                return new HtmlString($html);
            })
            ->columnSpanFull();
    }
}
