<?php

namespace App\Filament\Resources\TravelRequestResource\Pages;

use App\Filament\Resources\TravelRequestResource;
use Filament\Actions;
use Filament\Actions\Action;
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

                Section::make('Resumen Completo de la Solicitud')
                    ->schema([
                        TextEntry::make('folio')
                            ->label('Vista Completa del Viaje')
                            ->formatStateUsing(function ($state, $record) {
                                $user = $record->user;
                                $originCountry = $record->originCountry?->name ?? 'No especificado';
                                $destCountry = $record->destinationCountry?->name ?? 'No especificado';
                                $branch = $record->branch?->name ?? 'No especificado';
                                $requestType = $record->request_type;

                                // Si request_type está vacío, calcularlo basado en el país de destino
                                if (! $requestType && $record->destinationCountry) {
                                    $requestType = $record->destinationCountry->is_foreign ? 'foreign' : 'domestic';
                                }

                                // Formatear fechas en español
                                $departureDateFormatted = $record->departure_date ?
                                    $record->departure_date->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') :
                                    'No especificada';
                                $returnDateFormatted = $record->return_date ?
                                    $record->return_date->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') :
                                    'No especificada';

                                // Calcular días de viaje
                                $totalDays = 0;
                                $daysText = 'No calculado';
                                if ($record->departure_date && $record->return_date) {
                                    $departure = $record->departure_date->startOfDay();
                                    $return = $record->return_date->startOfDay();
                                    $totalDays = max(1, $departure->diffInDays($return) + 1);
                                    $daysText = $totalDays.($totalDays == 1 ? ' día' : ' días');
                                }

                                // Calcular total de viáticos usando los datos guardados
                                $perDiemTotal = 0;
                                $perDiemDetails = [];
                                $perDiemData = $record->per_diem_data ?? [];
                                $hasPerDiemConfig = false;

                                if ($requestType && $totalDays > 0 && $user->position_id) {
                                    $perDiems = \App\Models\PerDiem::with(['detail.concept'])
                                        ->where('position_id', $user->position_id)
                                        ->where('scope', $requestType)
                                        ->get();

                                    $hasPerDiemConfig = $perDiems->count() > 0;

                                    foreach ($perDiems as $perDiem) {
                                        // Si hay datos guardados, usar esos; si no, mostrar todos los disponibles
                                        $isEnabled = ! empty($perDiemData)
                                            ? (isset($perDiemData[$perDiem->id]) && ($perDiemData[$perDiem->id]['enabled'] ?? false))
                                            : true; // Para solicitudes sin datos específicos, mostrar todos

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

                                // Calcular total de gastos personalizados
                                $customExpensesTotal = 0;
                                $customExpensesData = $record->custom_expenses_data ?? [];
                                foreach ($customExpensesData as $expense) {
                                    if (! empty($expense['amount'])) {
                                        $customExpensesTotal += floatval($expense['amount']);
                                    }
                                }

                                // Total general
                                $grandTotal = $perDiemTotal + $customExpensesTotal;

                                // Obtener el folio
                                $folio = $record->folio ?? 'Sin folio';

                                // Servicios administrados solicitados
                                $requestedServices = [];
                                $additionalServices = $record->additional_services ?? [];
                                $managedServices = \App\Models\ExpenseConcept::where('is_unmanaged', false)->get();

                                foreach ($managedServices as $service) {
                                    if (isset($additionalServices[$service->id]) &&
                                        ($additionalServices[$service->id]['enabled'] ?? false)) {
                                        $requestedServices[] = [
                                            'name' => $service->name,
                                            'notes' => $additionalServices[$service->id]['notes'] ??
                                                'Se procesará de manera estándar.',
                                        ];
                                    }
                                }

                                return new HtmlString('
                                    <div class="w-full">
                                        <!-- Header del Reporte -->
                                        <div class="border-l-4 p-6 rounded-t-lg shadow-lg bg-gray-100 dark:bg-gray-800">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                                                <div>
                                                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Solicitud de Viaje</h2>
                                                    <p class="text-gray-600 dark:text-gray-300 mt-1">Resumen completo de la solicitud</p>
                                                </div>
                                                <div class="text-center">
                                                    <div class="bg-amber-100 dark:bg-amber-900/30 p-4 rounded-lg">
                                                        <div class="text-sm font-medium text-amber-800 dark:text-amber-200 mb-1">Folio</div>
                                                        <div class="text-xl font-bold text-gray-900 dark:text-white font-mono tracking-wider">'.$folio.'</div>
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Estado: '.$record->status_display.'</div>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-3xl font-bold text-gray-900 dark:text-white">$'.number_format($grandTotal, 2).'</div>
                                                    <div class="text-gray-600 dark:text-gray-300 text-sm">Total Estimado</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-white dark:bg-gray-800 rounded-b-lg shadow-lg">
                                            <!-- Información del Solicitante -->
                                            <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Información del Solicitante
                                                </h3>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full" >
                                                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg min-w-full">
                                                        <div class="text-sm text-gray-600 dark:text-gray-400">Solicitante</div>
                                                        <div class="font-medium text-gray-900 dark:text-white">'.$user->name.'</div>
                                                    </div>
                                                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg min-w-full">
                                                        <div class="text-sm text-gray-600 dark:text-gray-400">Centro de costo</div>
                                                        <div class="font-medium text-gray-900 dark:text-white">'.$branch.'</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Detalles del Viaje -->
                                            <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                     Detalles del Viaje
                                                </h3>
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                                    <div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                        <div class="text-sm text-gray-600 dark:text-gray-400">Origen</div>
                                                        <div class="font-semibold text-gray-900 dark:text-white">'.($record->origin_city ?? 'No especificado').'</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">'.$originCountry.'</div>
                                                    </div>
                                                    <div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                        <div class="text-sm text-gray-600 dark:text-gray-400">Destino</div>
                                                        <div class="font-semibold text-gray-900 dark:text-white">'.($record->destination_city ?? 'No especificado').'</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">'.$destCountry.'</div>
                                                    </div>
                                                    <div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                        <div class="text-sm text-gray-600 dark:text-gray-400">Fecha de Salida</div>
                                                        <div class="font-semibold text-gray-900 dark:text-white text-sm">'.$departureDateFormatted.'</div>
                                                    </div>
                                                    <div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                        <div class="text-sm text-gray-600 dark:text-gray-400">Fecha de Regreso</div>
                                                        <div class="font-semibold text-gray-900 dark:text-white text-sm">'.$returnDateFormatted.'</div>
                                                    </div>
                                                </div>
                                                <div class="mt-4 text-center">
                                                    <div class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-full">
                                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <span class="font-semibold mt-2">Duración total: '.$daysText.'</span>
                                                    </div>
                                                </div>
                                            </div>

                                            '.($record->notes ? '
                                            <!-- Notas y Justificación -->
                                            <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Notas y Justificación del Viaje
                                                </h3>
                                                <div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                    <p class="text-gray-700 dark:text-gray-300">'.htmlspecialchars($record->notes, ENT_QUOTES, 'UTF-8').'</p>
                                                </div>
                                            </div>
                                            ' : '').'

                                            '.(count($requestedServices) > 0 ? '
                                            <!-- Servicios Administrados -->
                                            <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                     Servicios Administrados Solicitados
                                                </h3>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    '.implode('', array_map(function ($service) {
                                    return '<div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                        <div class="font-semibold text-gray-900 dark:text-white">'.$service['name'].'</div>
                                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">'.$service['notes'].'</div>
                                                    </div>';
                                }, $requestedServices)).'
                                                </div>
                                            </div>
                                            ' : '').'

                                            '.(count($perDiemDetails) > 0 ? '
                                            <!-- Viáticos Estándar -->
                                            <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                                                    </svg>
                                                    Viáticos Estándar Aprobados
                                                </h3>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full">
                                                        <thead class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                            <tr>
                                                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Concepto</th>
                                                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Monto Diario</th>
                                                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Días</th>
                                                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Total</th>
                                                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Notas</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="">
                                                            '.implode('', array_map(function ($detail) {
                                    return '<tr class="">
                                                                        <td class="px-4 py-4">
                                                                            <div class="font-medium text-gray-900 dark:text-white">'.$detail['name'].'</div>
                                                                            '.($detail['concept'] ? '<div class="text-sm text-gray-500 dark:text-gray-400">('.$detail['concept'].')</div>' : '').'
                                                                        </td>
                                                                        <td class="px-4 py-4 text-sm text-gray-900 dark:text-white">$'.number_format($detail['daily'], 2).'</td>
                                                                        <td class="px-4 py-4 text-sm text-gray-900 dark:text-white">'.$detail['days'].'</td>
                                                                        <td class="px-4 py-4 text-sm font-semibold text-green-600 dark:text-green-400">$'.number_format($detail['total'], 2).'</td>
                                                                        <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 w-full">'.($detail['notes'] ?: 'Sin notas').'</td>
                                                                    </tr>';
                                }, $perDiemDetails)).'
                                                        </tbody>
                                                        <tfoot class="bg-gray-50 dark:bg-gray-700">
                                                            <tr>
                                                                <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white text-right">Subtotal Viáticos:</td>
                                                                <td class="px-4 py-3 text-sm font-bold text-green-600 dark:text-green-400">$'.number_format($perDiemTotal, 2).'</td>
                                                                <td></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                            ' : (! $hasPerDiemConfig ? '
                                            <!-- Mensaje cuando no hay viáticos configurados -->
                                            <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                                                    </svg>
                                                    Viáticos Estándar
                                                </h3>
                                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-4 rounded-lg">
                                                    <div class="flex items-start">
                                                        <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <div>
                                                            <p class="text-yellow-800 dark:text-yellow-200 font-medium">No hay viáticos configurados</p>
                                                            <p class="text-yellow-700 dark:text-yellow-300 text-sm mt-1">
                                                                No se encontraron viáticos configurados para la posición "'.($user->position?->name ?? 'Sin posición').'" 
                                                                y tipo de viaje "'.($requestType ?? 'No definido').'".
                                                            </p>
                                                            <p class="text-yellow-600 dark:text-yellow-400 text-xs mt-2">
                                                                Contacta al administrador para configurar los viáticos correspondientes.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            ' : '')).'

                                            '.(count($customExpensesData) > 0 ? '
                                            <!-- Gastos Personalizados -->
                                            <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                                    </svg>
                                                    Gastos Personalizados
                                                </h3>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full">
                                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                                            <tr>
                                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Concepto</th>
                                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monto</th>
                                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Justificación</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                            '.implode('', array_map(function ($expense) {
                                    return '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                                        <td class="px-4 py-4 font-medium text-gray-900 dark:text-white">'.($expense['concept'] ?? 'Sin concepto').'</td>
                                                                        <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-white">$'.number_format(floatval($expense['amount'] ?? 0), 2).'</td>
                                                                        <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">'.($expense['justification'] ?? 'Sin justificación').'</td>
                                                                    </tr>';
                                }, $customExpensesData)).'
                                                        </tbody>
                                                        <tfoot class="bg-gray-50 dark:bg-gray-700">
                                                            <tr>
                                                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white text-right">Subtotal Gastos Personalizados:</td>
                                                                <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white">$'.number_format($customExpensesTotal, 2).'</td>
                                                                <td></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                            ' : '').'

                                            <!-- Resumen Final -->
                                            <div class="p-6">
                                                <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 border border-gray-200 dark:border-gray-600 p-6 rounded-lg">
                                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 text-center">Solicitud de Anticipo de gasto</h3>
                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                                        <div class="text-center">
                                                            <div class="text-2xl font-bold text-gray-900 dark:text-white">$'.number_format($perDiemTotal, 2).'</div>
                                                            <div class="text-sm text-gray-600 dark:text-gray-400">Viáticos Estándar</div>
                                                        </div>
                                                        <div class="text-center">
                                                            <div class="text-2xl font-bold text-gray-900 dark:text-white">$'.number_format($customExpensesTotal, 2).'</div>
                                                            <div class="text-sm text-gray-600 dark:text-gray-400">Gastos Personalizados</div>
                                                        </div>
                                                        <div class="text-center border-l border-gray-300 dark:border-gray-600">
                                                            <div class="text-3xl font-bold text-green-600 dark:text-green-400">$'.number_format($grandTotal, 2).'</div>
                                                            <div class="text-sm text-gray-600 dark:text-gray-400 font-semibold">Total General</div>
                                                        </div>
                                                    </div>
                                                    '.($grandTotal == 0 ? '
                                                    <div class="text-center text-sm text-gray-600 dark:text-gray-400 bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg">
                                                        <p><strong>Nota:</strong> Esta solicitud no tiene montos monetarios asociados. Los servicios administrados se procesan directamente por la empresa.</p>
                                                    </div>
                                                    ' : '
                                                    <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                                                        * Los servicios administrados no tienen costo asociado en este resumen ya que son gestionados directamente por la empresa.
                                                    </div>
                                                    ').'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ');
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Archivos Adjuntos')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Lista de Adjuntos')
                            ->formatStateUsing(function ($state, $record) {
                                $html = '<div class="space-y-4">';
                                $html .= '<p><strong>ID de Solicitud:</strong> '.$record->id.'</p>';

                                $attachments = $record->attachments;
                                $html .= '<p><strong>Total de adjuntos:</strong> '.$attachments->count().'</p>';

                                if ($attachments->count() > 0) {
                                    foreach ($attachments as $attachment) {
                                        $typeLabel = $attachment->attachmentType?->name ?? 'Sin tipo';
                                        $uploaderName = $attachment->uploader?->name ?? 'Sin Usuario';
                                        $uploadDate = $attachment->created_at->format('d/m/Y H:i');
                                        $fileSize = $attachment->file_size ? number_format($attachment->file_size / 1024, 1).' KB' : 'Desconocido';

                                        $html .= '
                                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <div class="flex items-center mb-2">
                                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            <h4 class="font-semibold text-gray-900 dark:text-white">'.htmlspecialchars($attachment->file_name).'</h4>
                                                        </div>
                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                            <div>
                                                                <span class="font-medium">Tipo:</span> 
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                                    '.htmlspecialchars($typeLabel).'
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <span class="font-medium">Subido por:</span> '.htmlspecialchars($uploaderName).'
                                                            </div>
                                                            <div>
                                                                <span class="font-medium">Fecha:</span> '.$uploadDate.'
                                                            </div>
                                                        </div>
                                                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                            <span class="font-medium">Tamaño:</span> '.$fileSize.'
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <a href="'.route('attachments.download', $attachment).'"
                                                           class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-900 dark:bg-gray-600 dark:hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                                            </svg>
                                                            Descargar
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        ';
                                    }
                                } else {
                                    $html .= '<p>No hay adjuntos</p>';
                                }

                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Historial de Comentarios')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('')
                            ->formatStateUsing(function ($state, $record) {
                                $comments = $record->comments()->with('user')->orderBy('created_at', 'desc')->get();

                                if ($comments->isEmpty()) {
                                    return new HtmlString('<p class="text-gray-500 text-sm">No hay comentarios registrados.</p>');
                                }

                                $html = '<div class="space-y-4">';

                                foreach ($comments as $comment) {
                                    $badgeClass = $comment->type_badge_class ?? 'bg-gray-100 text-gray-800';
                                    $typeDisplay = $comment->type_display ?? 'Comentario';
                                    $userName = $comment->user->name ?? 'Usuario';
                                    $date = $comment->created_at->format('d/m/Y H:i');
                                    $commentText = nl2br(e($comment->comment));

                                    $html .= "
                                        <div class='border border-gray-200 dark:border-gray-700 rounded-lg p-4'>
                                            <div class='flex items-center justify-between mb-2'>
                                                <div class='flex items-center gap-2'>
                                                    <span class='inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {$badgeClass}'>
                                                        {$typeDisplay}
                                                    </span>
                                                    <span class='text-sm font-medium text-gray-900 dark:text-white'>{$userName}</span>
                                                </div>
                                                <span class='text-xs text-gray-500 dark:text-gray-400'>{$date}</span>
                                            </div>
                                            <div class='text-sm text-gray-700 dark:text-gray-300'>{$commentText}</div>
                                        </div>
                                    ";
                                }

                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->comments()->exists())
                    ->collapsible()
                    ->collapsed()
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->description('Historial completo de comentarios y acciones'),

                // Timeline de fechas del workflow al final
                Section::make('Fechas del Workflow')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('')
                            ->formatStateUsing(function ($state, $record) {
                                $html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">';

                                // Fecha de creación (siempre existe)
                                $html .= '
                                <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 p-4 rounded-lg">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                                        </svg>
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Creada</h4>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">'.$record->created_at->format('d/m/Y').'</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">'.$record->created_at->format('H:i').'</div>
                                </div>';

                                // Fecha de envío (submitted_at)
                                if ($record->submitted_at) {
                                    $html .= '
                                    <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 p-4 rounded-lg">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                                            </svg>
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Enviada</h4>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">'.$record->submitted_at->format('d/m/Y').'</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">'.$record->submitted_at->format('H:i').'</div>
                                    </div>';
                                }

                                // Fecha de autorización (authorized_at)
                                if ($record->authorized_at) {
                                    $html .= '
                                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4 rounded-lg">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            <h4 class="text-sm font-semibold text-green-800 dark:text-green-200">Autorizada</h4>
                                        </div>
                                        <div class="text-sm font-medium text-green-900 dark:text-green-100">'.$record->authorized_at->format('d/m/Y').'</div>
                                        <div class="text-xs text-green-600 dark:text-green-400">'.$record->authorized_at->format('H:i').'</div>
                                    </div>';
                                }

                                // Fecha de rechazo
                                if ($record->rejected_at) {
                                    $html .= '
                                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 rounded-lg">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            <h4 class="text-sm font-semibold text-red-800 dark:text-red-200">Rechazada</h4>
                                        </div>
                                        <div class="text-sm font-medium text-red-900 dark:text-red-100">'.$record->rejected_at->format('d/m/Y').'</div>
                                        <div class="text-xs text-red-600 dark:text-red-400">'.$record->rejected_at->format('H:i').'</div>
                                    </div>';
                                }

                                // Última actualización (siempre existe)
                                $html .= '
                                <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 p-4 rounded-lg">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Actualizada</h4>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">'.$record->updated_at->format('d/m/Y').'</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">'.$record->updated_at->format('H:i').'</div>
                                </div>';

                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->icon('heroicon-o-clock')
                    ->description('Fechas importantes del proceso de la solicitud'),
            ]);
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

            // Authorization button removed - now handled in list view table actions

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
