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

                Forms\Components\Section::make('Resumen Monetario')
                    ->schema([
                        Forms\Components\View::make('filament.components.expense-verification-summary')
                            ->viewData(function (?ExpenseVerification $record) {
                                if (! $record || ! $record->travelRequest) {
                                    return [
                                        'request' => null,
                                        'message' => 'Selecciona una solicitud de viaje para ver el resumen monetario.',
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
