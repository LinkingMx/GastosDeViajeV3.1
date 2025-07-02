<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo Usuario'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'usuarios' => Tab::make('Usuarios')
                ->modifyQueryUsing(fn (Builder $query) => $query->regularUsers())
                ->badge(User::regularUsers()->count()),

            'tesoreria' => Tab::make('Tesorería')
                ->modifyQueryUsing(fn (Builder $query) => $query->treasuryTeam())
                ->badge(User::treasuryTeam()->count()),

            'viajes' => Tab::make('Viajes')
                ->modifyQueryUsing(fn (Builder $query) => $query->travelTeam())
                ->badge(User::travelTeam()->count()),
        ];
    }
}
