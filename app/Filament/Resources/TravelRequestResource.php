<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TravelRequestResource\Pages;
use App\Models\TravelRequest;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TravelRequestResource extends Resource
{
    protected static ?string $model = TravelRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Gestión de Viajes';

    protected static ?string $modelLabel = 'Solicitud de Viaje';

    protected static ?string $pluralModelLabel = 'Solicitudes de Viaje';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Schema will be defined in Create/Edit pages
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('folio')
                    ->label('Folio')
                    ->searchable(['uuid'])
                    ->copyable()
                    ->tooltip(fn ($record): string => $record->uuid ?? 'UUID no disponible'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('destination_city')
                    ->label('Destino')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Fecha Salida')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_date')
                    ->label('Fecha Regreso')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('actualAuthorizer.name')
                    ->label('Autorizador')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin autorizador'),
                Tables\Columns\TextColumn::make('status_display')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'revision' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Enviada')
                    ->date('d/m/Y H:i')
                    ->placeholder('No enviada')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'pending' => 'Pendiente',
                        'approved' => 'Autorizada',
                        'rejected' => 'Rechazada',
                        'revision' => 'En Revisión',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->canBeEdited() && auth()->id() === $record->user_id),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->canBeAuthorizedBy(auth()->user()))
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comment')
                            ->label('Comentarios (opcional)')
                            ->placeholder('Agregar comentarios sobre la aprobación...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->approve($data['comment'] ?? null);
                        \Filament\Notifications\Notification::make()
                            ->title('Solicitud Aprobada')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->canBeAuthorizedBy(auth()->user()))
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comment')
                            ->label('Motivo del rechazo')
                            ->placeholder('Explica el motivo del rechazo...')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject($data['comment']);
                        \Filament\Notifications\Notification::make()
                            ->title('Solicitud Rechazada')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('put_in_revision')
                    ->label('Revisar')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(fn ($record) => $record->canBeRevisedBy(auth()->user()))
                    ->action(function ($record) {
                        $record->putInRevision();
                        \Filament\Notifications\Notification::make()
                            ->title('Solicitud puesta en revisión')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->can('delete_travel_requests')),
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
            'index' => Pages\ListTravelRequests::route('/'),
            'create' => Pages\CreateTravelRequest::route('/create'),
            'view' => Pages\ViewTravelRequest::route('/{record}'),
            'edit' => Pages\EditTravelRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Los super_admin pueden ver todo
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Para otros usuarios, solo mostrar solicitudes visibles:
        // 1. Sus propias solicitudes
        // 2. Solicitudes pendientes que pueden autorizar
        return $query->where(function (Builder $query) use ($user) {
            $query->where('user_id', $user->id) // Mis solicitudes
                ->orWhere(function (Builder $query) use ($user) {
                    // Solicitudes pendientes que puedo autorizar
                    $query->where('status', 'pending')
                        ->where(function (Builder $query) use ($user) {
                            $query->whereHas('user', function (Builder $query) use ($user) {
                                // Si el usuario tiene override_authorizer_id, esas solicitudes van a él
                                $query->where('override_authorizer_id', $user->id);
                            })
                                ->orWhereHas('user.department', function (Builder $query) use ($user) {
                                    // Si no tiene override, verificar si el usuario es autorizador del departamento
                                    $query->where('authorizer_id', $user->id);
                                });
                        });
                });
        });
    }
}
