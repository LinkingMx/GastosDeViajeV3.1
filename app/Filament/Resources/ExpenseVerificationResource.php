<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseVerificationResource\Pages;
use App\Models\ExpenseVerification;
use App\Models\TravelRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                                    ->where('advance_deposit_made', true)
                                    ->whereIn('status', ['travel_approved']) // Solo solicitudes aprobadas finalmente
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

                Forms\Components\Section::make('Resumen de Comprobación')
                    ->schema([
                        Forms\Components\View::make('filament.components.expense-verification-status')
                            ->viewData(function (?ExpenseVerification $record) {
                                if (! $record) {
                                    return [
                                        'summary' => [],
                                        'message' => 'Agrega comprobantes para ver el resumen de comprobación.',
                                    ];
                                }

                                return [
                                    'summary' => $record->getExpenseVerificationSummary(),
                                    'message' => null,
                                ];
                            })
                            ->columnSpanFull(),
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
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('travelRequest.folio')
                    ->label('Solicitud de Viaje')
                    ->searchable()
                    ->sortable()
                    ->url(fn (ExpenseVerification $record) => route('filament.admin.resources.travel-requests.view', $record->travel_request_id)
                    )
                    ->color('info'),

                Tables\Columns\TextColumn::make('travelRequest.user.name')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('travelRequest.destinationCountry.name')
                    ->label('Destino')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains(strtolower($state), 'méxico') => 'success',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creado por')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('receipts_count')
                    ->label('Comprobantes')
                    ->counts('receipts')
                    ->badge()
                    ->color('success')
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
                    ->color('warning'),

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
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListExpenseVerifications::route('/'),
            'create' => Pages\CreateExpenseVerification::route('/create'),
            'edit' => Pages\EditExpenseVerification::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
