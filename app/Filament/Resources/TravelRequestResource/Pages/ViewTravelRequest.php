<?php

namespace App\Filament\Resources\TravelRequestResource\Pages;

use App\Filament\Resources\TravelRequestResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewTravelRequest extends ViewRecord
{
    protected static string $resource = TravelRequestResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Header con diseño especial
                $this->getHeaderSection(),

                // Información del solicitante (componentes nativos)
                $this->getRequesterInfoSection(),

                // Detalles del viaje (componentes nativos)
                $this->getTravelDetailsSection(),

                // Notas (componentes nativos)
                $this->getNotesSection(),

                // Servicios administrados (componentes nativos)
                $this->getServicesSection(),

                // Viáticos (mantener tabla HTML)
                $this->getPerDiemsSection(),

                // Gastos personalizados (mantener tabla HTML)
                $this->getCustomExpensesSection(),

                // Resumen final (mantener diseño especial)
                $this->getSummarySection(),

                // Archivos adjuntos (componentes nativos)
                $this->getAttachmentsSection(),

                // Comentarios (componentes nativos)
                $this->getCommentsSection(),

                // Fechas del workflow (componentes nativos)
                $this->getWorkflowDatesSection(),
            ]);
    }

    protected function getHeaderSection(): Section
    {
        return Section::make()
            ->schema([
                TextEntry::make('folio')
                    ->label('')
                    ->formatStateUsing(function ($state, $record) {
                        $totals = $this->calculateTotals($record);

                        return new HtmlString('
                            <div class="border-l-4 border-primary-600 p-6 rounded-lg shadow-lg bg-gray-100 dark:bg-gray-800">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                                    <div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Solicitud de Viaje</h2>
                                        <p class="text-gray-600 dark:text-gray-300 mt-1">Resumen completo de la solicitud</p>
                                    </div>
                                    <div class="text-center">
                                        <div class="bg-primary-100 dark:bg-primary-900/30 p-4 rounded-lg">
                                            <div class="text-sm font-medium text-primary-800 dark:text-primary-200 mb-1">Folio</div>
                                            <div class="text-xl font-bold text-gray-900 dark:text-white font-mono tracking-wider">'.$record->folio.'</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Estado: '.$record->status_display.'</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-3xl font-bold text-gray-900 dark:text-white">$'.number_format($totals['grand'], 2).'</div>
                                        <div class="text-gray-600 dark:text-gray-300 text-sm">Total Estimado</div>
                                    </div>
                                </div>
                            </div>
                        ');
                    })
                    ->columnSpanFull(),
            ]);
    }

    protected function getRequesterInfoSection(): Section
    {
        return Section::make('Información del Solicitante')
            ->icon('heroicon-o-user')
            ->schema([
                Grid::make(['default' => 1, 'sm' => 2])
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Solicitante')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('branch.name')
                            ->label('Centro de costo')
                            ->badge()
                            ->color('primary'),
                    ]),
            ])
            ->collapsible()
            ->collapsed(false);
    }

    protected function getTravelDetailsSection(): Section
    {
        return Section::make('Detalles del Viaje')
            ->icon('heroicon-o-map-pin')
            ->schema([
                Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])
                    ->schema([
                        TextEntry::make('origin_city')
                            ->label('Origen')
                            ->formatStateUsing(fn ($state, $record) =>
                                $state.', '.($record->originCountry?->name ?? 'No especificado')
                            )
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('destination_city')
                            ->label('Destino')
                            ->formatStateUsing(fn ($state, $record) =>
                                $state.', '.($record->destinationCountry?->name ?? 'No especificado')
                            )
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('departure_date')
                            ->label('Fecha de Salida')
                            ->formatStateUsing(fn ($state) =>
                                $state ? $state->locale('es')->isoFormat('D [de] MMMM [de] YYYY') : 'No especificada'
                            )
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('return_date')
                            ->label('Fecha de Regreso')
                            ->formatStateUsing(fn ($state) =>
                                $state ? $state->locale('es')->isoFormat('D [de] MMMM [de] YYYY') : 'No especificada'
                            )
                            ->badge()
                            ->color('primary'),
                    ]),

                TextEntry::make('id')
                    ->label('Duración del Viaje')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->departure_date && $record->return_date) {
                            $totalDays = max(1, $record->departure_date->diffInDays($record->return_date) + 1);
                            return $totalDays.($totalDays == 1 ? ' día' : ' días');
                        }
                        return 'No calculado';
                    })
                    ->badge()
                    ->color('primary')
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->collapsed(false);
    }

    protected function getNotesSection(): Section
    {
        return Section::make('Notas y Justificación del Viaje')
            ->icon('heroicon-o-document-text')
            ->schema([
                TextEntry::make('notes')
                    ->label('')
                    ->placeholder('Sin notas')
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->visible(fn ($record) => ! empty($record->notes));
    }

    protected function getServicesSection(): Section
    {
        return Section::make('Servicios Administrados Solicitados')
            ->icon('heroicon-o-check-circle')
            ->schema([
                TextEntry::make('id')
                    ->label('')
                    ->formatStateUsing(function ($state, $record) {
                        $managedServices = \App\Models\ExpenseConcept::where('is_unmanaged', false)->get();
                        $requestedServices = [];
                        $additionalServices = $record->additional_services ?? [];

                        foreach ($managedServices as $service) {
                            if (isset($additionalServices[$service->id]) && ($additionalServices[$service->id]['enabled'] ?? false)) {
                                $requestedServices[] = [
                                    'name' => $service->name,
                                    'notes' => $additionalServices[$service->id]['notes'] ?? 'Se procesará de manera estándar.',
                                ];
                            }
                        }

                        if (empty($requestedServices)) {
                            return new HtmlString('<p class="text-gray-500 dark:text-gray-400">No se solicitaron servicios administrados</p>');
                        }

                        $html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
                        foreach ($requestedServices as $service) {
                            $html .= '
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-1">'.$service['name'].'</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">'.$service['notes'].'</p>
                                </div>
                            ';
                        }
                        $html .= '</div>';

                        return new HtmlString($html);
                    })
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->visible(function ($record) {
                $services = $record->additional_services ?? [];
                foreach ($services as $service) {
                    if ($service['enabled'] ?? false) {
                        return true;
                    }
                }
                return false;
            });
    }

    protected function getPerDiemsSection(): Section
    {
        return Section::make('Viáticos Estándar')
            ->icon('heroicon-o-banknotes')
            ->schema([
                TextEntry::make('id')
                    ->label('')
                    ->formatStateUsing(function ($state, $record) {
                        return $this->renderPerDiemsTable($record);
                    })
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->visible(fn ($record) => $this->hasPerDiems($record) || ! $this->hasPerDiemConfig($record));
    }

    protected function getCustomExpensesSection(): Section
    {
        return Section::make('Gastos Personalizados')
            ->icon('heroicon-o-currency-dollar')
            ->schema([
                TextEntry::make('id')
                    ->label('')
                    ->formatStateUsing(function ($state, $record) {
                        return $this->renderCustomExpensesTable($record->custom_expenses_data);
                    })
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->visible(fn ($record) => count($record->custom_expenses_data ?? []) > 0);
    }

    protected function getSummarySection(): Section
    {
        return Section::make('Resumen de Anticipo')
            ->schema([
                TextEntry::make('id')
                    ->label('')
                    ->formatStateUsing(function ($state, $record) {
                        $totals = $this->calculateTotals($record);

                        return new HtmlString('
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 border border-gray-200 dark:border-gray-600 p-6 rounded-lg">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 text-center">Solicitud de Anticipo de Gasto</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="text-center p-4 bg-white/50 dark:bg-gray-900/50 rounded-lg">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white">$'.number_format($totals['perDiem'], 2).'</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Viáticos Estándar</div>
                                    </div>
                                    <div class="text-center p-4 bg-white/50 dark:bg-gray-900/50 rounded-lg">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white">$'.number_format($totals['custom'], 2).'</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Gastos Personalizados</div>
                                    </div>
                                    <div class="text-center p-4 bg-white/50 dark:bg-gray-900/50 rounded-lg border-2 border-primary-300 dark:border-primary-700">
                                        <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">$'.number_format($totals['grand'], 2).'</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-semibold">Total General</div>
                                    </div>
                                </div>
                                '.($totals['grand'] == 0 ? '
                                <div class="text-center text-sm text-gray-600 dark:text-gray-400 bg-warning-50 dark:bg-warning-900/20 p-3 rounded-lg">
                                    <p><strong>Nota:</strong> Esta solicitud no tiene montos monetarios asociados. Los servicios administrados se procesan directamente por la empresa.</p>
                                </div>
                                ' : '
                                <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                                    * Los servicios administrados no tienen costo asociado en este resumen ya que son gestionados directamente por la empresa.
                                </div>
                                ').'
                            </div>
                        ');
                    })
                    ->columnSpanFull(),
            ]);
    }

    protected function getAttachmentsSection(): Section
    {
        return Section::make('Archivos Adjuntos')
            ->icon('heroicon-o-paper-clip')
            ->schema([
                RepeatableEntry::make('attachments')
                    ->label('')
                    ->schema([
                        TextEntry::make('attachmentType.name')
                            ->label('Tipo de Documento')
                            ->badge()
                            ->color('info')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->weight('bold'),

                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])
                            ->schema([
                                TextEntry::make('uploader.name')
                                    ->label('Subido por')
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('created_at')
                                    ->label('Fecha')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('formatted_file_size')
                                    ->label('Tamaño')
                                    ->icon('heroicon-o-scale'),

                                TextEntry::make('id')
                                    ->label('Descarga')
                                    ->formatStateUsing(fn ($state, $record) =>
                                        new HtmlString('
                                            <a href="'.route('attachments.download', $record).'"
                                               class="inline-flex items-center gap-2 text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Descargar
                                            </a>
                                        ')
                                    ),
                            ]),

                        TextEntry::make('description')
                            ->label('Descripción')
                            ->visible(fn ($record) => ! empty($record->description))
                            ->icon('heroicon-o-information-circle')
                            ->color('gray')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->collapsed()
            ->visible(fn ($record) => $record->attachments()->exists());
    }

    protected function getCommentsSection(): Section
    {
        return Section::make('Historial de Comentarios')
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->description('Historial completo de comentarios y acciones')
            ->schema([
                RepeatableEntry::make('comments')
                    ->label('')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                TextEntry::make('type')
                                    ->label('Tipo')
                                    ->formatStateUsing(fn ($state, $record) => $record->type_display)
                                    ->badge()
                                    ->color(fn ($record) => match ($record->type) {
                                        'approval' => 'success',
                                        'rejection' => 'danger',
                                        'revision' => 'warning',
                                        'authorization' => 'info',
                                        default => 'gray',
                                    }),

                                TextEntry::make('user.name')
                                    ->label('Usuario')
                                    ->weight('bold')
                                    ->icon('heroicon-o-user'),
                            ]),

                        TextEntry::make('comment')
                            ->label('Comentario')
                            ->columnSpanFull(),

                        TextEntry::make('created_at')
                            ->label('Fecha')
                            ->dateTime('d/m/Y H:i')
                            ->size(TextEntry\TextEntrySize::Small)
                            ->color('gray')
                            ->icon('heroicon-o-clock'),
                    ])
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->collapsed()
            ->visible(fn ($record) => $record->comments()->exists());
    }

    protected function getWorkflowDatesSection(): Section
    {
        return Section::make('Fechas del Workflow')
            ->icon('heroicon-o-clock')
            ->description('Fechas importantes del proceso de la solicitud')
            ->schema([
                Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creada')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-plus-circle')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('submitted_at')
                            ->label('Enviada')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-paper-airplane')
                            ->badge()
                            ->color('info')
                            ->visible(fn ($record) => $record->submitted_at !== null),

                        TextEntry::make('authorized_at')
                            ->label('Autorizada')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-check-circle')
                            ->badge()
                            ->color('success')
                            ->visible(fn ($record) => $record->authorized_at !== null),

                        TextEntry::make('rejected_at')
                            ->label('Rechazada')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-x-circle')
                            ->badge()
                            ->color('danger')
                            ->visible(fn ($record) => $record->rejected_at !== null),

                        TextEntry::make('updated_at')
                            ->label('Última Actualización')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-arrow-path')
                            ->badge()
                            ->color('gray'),
                    ]),
            ])
            ->collapsible()
            ->collapsed();
    }

    // Helper methods for calculations

    protected function calculateTotals($record): array
    {
        $perDiem = $this->calculatePerDiemTotal($record);
        $custom = $this->calculateCustomExpensesTotal($record);

        return [
            'perDiem' => $perDiem,
            'custom' => $custom,
            'grand' => $perDiem + $custom,
        ];
    }

    protected function calculatePerDiemTotal($record): float
    {
        $total = 0;
        $user = $record->user;
        $requestType = $record->request_type;

        if (! $requestType && $record->destinationCountry) {
            $requestType = $record->destinationCountry->is_foreign ? 'foreign' : 'domestic';
        }

        if (! $requestType || ! $record->departure_date || ! $record->return_date || ! $user->position_id) {
            return 0;
        }

        $totalDays = max(1, $record->departure_date->diffInDays($record->return_date) + 1);
        $perDiemData = $record->per_diem_data ?? [];

        $perDiems = \App\Models\PerDiem::where('position_id', $user->position_id)
            ->where('scope', $requestType)
            ->get();

        foreach ($perDiems as $perDiem) {
            $isEnabled = ! empty($perDiemData)
                ? (isset($perDiemData[$perDiem->id]) && ($perDiemData[$perDiem->id]['enabled'] ?? false))
                : true;

            if ($isEnabled) {
                $total += $totalDays * $perDiem->amount;
            }
        }

        return $total;
    }

    protected function calculateCustomExpensesTotal($record): float
    {
        $total = 0;
        $customExpensesData = $record->custom_expenses_data ?? [];

        foreach ($customExpensesData as $expense) {
            if (! empty($expense['amount'])) {
                $total += floatval($expense['amount']);
            }
        }

        return $total;
    }

    protected function hasPerDiems($record): bool
    {
        $perDiemData = $record->per_diem_data ?? [];
        foreach ($perDiemData as $perDiem) {
            if ($perDiem['enabled'] ?? false) {
                return true;
            }
        }

        return false;
    }

    protected function hasPerDiemConfig($record): bool
    {
        $user = $record->user;
        $requestType = $record->request_type;

        if (! $requestType && $record->destinationCountry) {
            $requestType = $record->destinationCountry->is_foreign ? 'foreign' : 'domestic';
        }

        if (! $requestType || ! $user->position_id) {
            return false;
        }

        return \App\Models\PerDiem::where('position_id', $user->position_id)
            ->where('scope', $requestType)
            ->exists();
    }

    protected function renderPerDiemsTable($record): HtmlString
    {
        $user = $record->user;
        $requestType = $record->request_type;

        if (! $requestType && $record->destinationCountry) {
            $requestType = $record->destinationCountry->is_foreign ? 'foreign' : 'domestic';
        }

        $totalDays = 0;
        if ($record->departure_date && $record->return_date) {
            $totalDays = max(1, $record->departure_date->diffInDays($record->return_date) + 1);
        }

        $perDiemTotal = 0;
        $perDiemDetails = [];
        $perDiemData = $record->per_diem_data ?? [];

        if ($requestType && $totalDays > 0 && $user->position_id) {
            $perDiems = \App\Models\PerDiem::with(['detail.concept'])
                ->where('position_id', $user->position_id)
                ->where('scope', $requestType)
                ->get();

            if ($perDiems->count() == 0) {
                return new HtmlString('
                    <div class="bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 p-4 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-warning-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-warning-800 dark:text-warning-200 font-medium">No hay viáticos configurados</p>
                                <p class="text-warning-700 dark:text-warning-300 text-sm mt-1">
                                    No se encontraron viáticos configurados para la posición "'.($user->position?->name ?? 'Sin posición').'"
                                    y tipo de viaje "'.$requestType.'".
                                </p>
                            </div>
                        </div>
                    </div>
                ');
            }

            foreach ($perDiems as $perDiem) {
                $isEnabled = ! empty($perDiemData)
                    ? (isset($perDiemData[$perDiem->id]) && ($perDiemData[$perDiem->id]['enabled'] ?? false))
                    : true;

                if ($isEnabled) {
                    $amount = $totalDays * $perDiem->amount;
                    $perDiemTotal += $amount;
                    $perDiemDetails[] = [
                        'name' => $perDiem->detail?->name ?? 'Viático',
                        'concept' => $perDiem->detail?->concept?->name ?? '',
                        'daily' => $perDiem->amount,
                        'days' => $totalDays,
                        'total' => $amount,
                        'notes' => ! empty($perDiemData) && isset($perDiemData[$perDiem->id]['notes'])
                            ? $perDiemData[$perDiem->id]['notes']
                            : 'Procesado según políticas estándar.',
                    ];
                }
            }
        }

        if (empty($perDiemDetails)) {
            return new HtmlString('<p class="text-gray-500 dark:text-gray-400">No hay viáticos aplicables</p>');
        }

        $html = '
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 dark:border-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Concepto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monto Diario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Días</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notas</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';

        foreach ($perDiemDetails as $detail) {
            $html .= '
                    <tr>
                        <td class="px-4 py-4">
                            <div class="font-medium text-gray-900 dark:text-white">'.$detail['name'].'</div>
                            '.($detail['concept'] ? '<div class="text-sm text-gray-500 dark:text-gray-400">('.$detail['concept'].')</div>' : '').'
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-900 dark:text-white">$'.number_format($detail['daily'], 2).'</td>
                        <td class="px-4 py-4 text-sm text-gray-900 dark:text-white">'.$detail['days'].'</td>
                        <td class="px-4 py-4 text-sm font-semibold text-success-600 dark:text-success-400">$'.number_format($detail['total'], 2).'</td>
                        <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">'.($detail['notes'] ?: 'Sin notas').'</td>
                    </tr>';
        }

        $html .= '
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white text-right">Subtotal Viáticos:</td>
                        <td class="px-4 py-3 text-sm font-bold text-success-600 dark:text-success-400">$'.number_format($perDiemTotal, 2).'</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>';

        return new HtmlString($html);
    }

    protected function renderCustomExpensesTable($customExpensesData): HtmlString
    {
        if (empty($customExpensesData)) {
            return new HtmlString('<p class="text-gray-500 dark:text-gray-400">No hay gastos personalizados</p>');
        }

        $customExpensesTotal = 0;
        foreach ($customExpensesData as $expense) {
            if (! empty($expense['amount'])) {
                $customExpensesTotal += floatval($expense['amount']);
            }
        }

        $html = '
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 dark:border-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Concepto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Justificación</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';

        foreach ($customExpensesData as $expense) {
            $html .= '
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-4 font-medium text-gray-900 dark:text-white">'.($expense['concept'] ?? 'Sin concepto').'</td>
                        <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-white">$'.number_format(floatval($expense['amount'] ?? 0), 2).'</td>
                        <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">'.($expense['justification'] ?? 'Sin justificación').'</td>
                    </tr>';
        }

        $html .= '
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white text-right">Subtotal Gastos Personalizados:</td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white">$'.number_format($customExpensesTotal, 2).'</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>';

        return new HtmlString($html);
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $user = auth()->user();
        $actions = [];

        // Acciones para el propietario de la solicitud
        if ($user->id === $record->user_id) {
            // Editar (solo en draft/revision)
            if ($record->canBeEdited()) {
                $actions[] = Actions\EditAction::make();
            }

            // Poner en revisión (solo si está rechazada)
            if ($record->canBeRevisedBy($user)) {
                $actions[] = Action::make('putInRevision')
                    ->label('Poner en Revisión')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Poner Solicitud en Revisión')
                    ->modalDescription('Al poner la solicitud en revisión podrás editarla nuevamente para hacer las correcciones necesarias y luego reenviarla para autorización.')
                    ->modalSubmitActionLabel('Poner en Revisión')
                    ->action(function () use ($record) {
                        $record->putInRevision();
                        Notification::make()
                            ->title('Solicitud en Revisión')
                            ->body('La solicitud ha sido puesta en revisión. Ahora puedes editarla para hacer correcciones.')
                            ->success()
                            ->send();
                    });
            }
        }

        // Acciones para el autorizador
        if ($record->canBeAuthorizedBy($user)) {
            $actions[] = Action::make('approve')
                ->label('Aprobar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Textarea::make('comment')
                        ->label('Comentarios (opcional)')
                        ->placeholder('Agregar comentarios sobre la aprobación...'),
                ])
                ->action(function (array $data) use ($record) {
                    $record->approve($data['comment'] ?? null);
                    Notification::make()
                        ->title('Solicitud Aprobada')
                        ->body('La solicitud ha sido aprobada exitosamente.')
                        ->success()
                        ->send();
                });

            $actions[] = Action::make('reject')
                ->label('Rechazar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\Textarea::make('comment')
                        ->label('Motivo del rechazo')
                        ->placeholder('Explica el motivo del rechazo...')
                        ->required(),
                ])
                ->action(function (array $data) use ($record) {
                    $record->reject($data['comment']);
                    Notification::make()
                        ->title('Solicitud Rechazada')
                        ->body('La solicitud ha sido rechazada.')
                        ->success()
                        ->send();
                });
        }

        return $actions;
    }
}
