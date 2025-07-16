<?php

namespace App\Filament\Resources\ExpenseVerificationResource\Concerns;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

trait HasTabs
{
    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Activas')
                ->icon('heroicon-m-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->active()->notArchived())
                ->badge(fn () => $this->getModel()::active()->notArchived()->count()),
            
            'historical' => Tab::make('Históricas')
                ->icon('heroicon-m-archive-box')
                ->modifyQueryUsing(fn (Builder $query) => $query->processed()->notArchived())
                ->badge(fn () => $this->getModel()::processed()->notArchived()->count()),
            
            'archived' => Tab::make('Archivadas')
                ->icon('heroicon-m-archive-box-x-mark')
                ->modifyQueryUsing(fn (Builder $query) => $query->archived())
                ->badge(fn () => $this->getModel()::archived()->count()),
        ];
    }
}