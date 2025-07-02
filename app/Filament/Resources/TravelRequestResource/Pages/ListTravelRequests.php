<?php

namespace App\Filament\Resources\TravelRequestResource\Pages;

use App\Filament\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTravelRequests extends ListRecords
{
    protected static string $resource = TravelRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $user = auth()->user();

        return [
            'all' => Tab::make('Todas las Visibles')
                ->icon('heroicon-o-eye')
                ->badge($this->getTabBadgeCount('all')),

            'my_requests' => Tab::make('Mis Solicitudes')
                ->icon('heroicon-o-document-text')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', $user->id))
                ->badge($this->getTabBadgeCount('my_requests')),

            'my_authorizations' => Tab::make('Mis Autorizaciones')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(function (Builder $query) use ($user) {
                    return $query->where('status', 'pending')
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
                })
                ->badge($this->getTabBadgeCount('my_authorizations')),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }

    protected function getTabBadgeCount(string $tab): int
    {
        $user = auth()->user();

        $query = TravelRequest::query();

        // Aplicar el filtro base del recurso (solo visibles para el usuario)
        $query->where(function (Builder $query) use ($user) {
            $query->where('user_id', $user->id) // Mis solicitudes
                ->orWhere(function (Builder $query) use ($user) {
                    // Solicitudes pendientes que puedo autorizar
                    $query->where('status', 'pending')
                        ->where(function (Builder $query) use ($user) {
                            $query->whereHas('user', function (Builder $query) use ($user) {
                                $query->where('override_authorizer_id', $user->id);
                            })
                                ->orWhereHas('user.department', function (Builder $query) use ($user) {
                                    $query->where('authorizer_id', $user->id);
                                });
                        });
                });
        });

        return match ($tab) {
            'all' => $query->count(),
            'my_requests' => TravelRequest::where('user_id', $user->id)->count(),
            'my_authorizations' => TravelRequest::where('status', 'pending')
                ->where(function (Builder $query) use ($user) {
                    $query->whereHas('user', function (Builder $query) use ($user) {
                        $query->where('override_authorizer_id', $user->id);
                    })
                        ->orWhereHas('user.department', function (Builder $query) use ($user) {
                            $query->where('authorizer_id', $user->id);
                        });
                })
                ->count(),
            default => 0,
        };
    }
}
