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
                Tables\Columns\TextColumn::make('authorizer.name')
                    ->label('Autorizador')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'primary',
                        'warning' => 'draft',
                        'info' => 'submitted',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => __("status.{$state}")),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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

        if (Auth::user()->hasRole('super_admin')) {
            return $query;
        }

        return $query->where(function (Builder $query) {
            $query->where('user_id', Auth::id())
                ->orWhere('authorizer_id', Auth::id());
        });
    }
}
