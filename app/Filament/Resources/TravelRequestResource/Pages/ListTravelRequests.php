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
        $tabs = [
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

        // Agregar tabs específicos para el equipo de viajes
        if ($user->isTravelTeamMember()) {
            $tabs['travel_pending'] = Tab::make('Pendientes de Viajes')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'travel_review'))
                ->badge($this->getTabBadgeCount('travel_pending'));

            $tabs['travel_approved'] = Tab::make('Aprobadas Final')
                ->icon('heroicon-o-shield-check')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'travel_approved'))
                ->badge($this->getTabBadgeCount('travel_approved'));
        }

        return $tabs;
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
                })
                ->orWhere(function (Builder $query) use ($user) {
                    // Para equipo de viajes: solicitudes en revisión y revisadas
                    if ($user->isTravelTeamMember()) {
                        $query->whereIn('status', ['travel_review', 'travel_approved', 'travel_rejected']);
                    }
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
            'travel_pending' => TravelRequest::where('status', 'travel_review')->count(),
            'travel_approved' => TravelRequest::where('status', 'travel_approved')->count(),
            default => 0,
        };
    }
}
