@if (count($summary) > 0)
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">📊 Resumen de Comprobación de Gastos</h3>
        </div>

        <div class="p-4">
            <!-- Tabla de resumen -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-900/40">
                        <tr>
                            <th
                                class="px-3 py-2 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase">
                                Categoría de Gasto
                            </th>
                            <th
                                class="px-3 py-2 text-right text-xs font-medium text-gray-800 dark:text-gray-200 uppercase">
                                Por Comprobar
                            </th>
                            <th
                                class="px-3 py-2 text-right text-xs font-medium text-gray-800 dark:text-gray-200 uppercase">
                                Comprobado
                            </th>
                            <th
                                class="px-3 py-2 text-right text-xs font-medium text-gray-800 dark:text-gray-200 uppercase">
                                Pendiente
                            </th>
                            <th
                                class="px-3 py-2 text-center text-xs font-medium text-gray-800 dark:text-gray-200 uppercase">
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($summary as $category => $data)
                            @php
                                $isComplete = $data['remaining'] <= 0;
                                $isExcess = $data['remaining'] < 0;
                                $percentage =
                                    $data['pending'] > 0 ? min(100, ($data['proven'] / $data['pending']) * 100) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/10">
                                <td class="px-3 py-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $data['name'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ ucfirst(str_replace('_', ' ', $category)) }}
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">
                                        ${{ number_format($data['pending'], 2) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <span class="text-green-600 dark:text-green-400 font-medium">
                                        ${{ number_format($data['proven'], 2) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <span
                                        class="{{ $isComplete ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }} font-medium">
                                        ${{ number_format(abs($data['remaining']), 2) }}
                                        @if ($isExcess)
                                            <span class="text-xs text-red-500">(Exceso)</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if ($isComplete)
                                        @if ($isExcess)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300">
                                                ⚠️ Exceso
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300">
                                                ✅ Completo
                                            </span>
                                        @endif
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300">
                                            ⏳ {{ number_format($percentage, 1) }}%
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 dark:bg-gray-900/40">
                        <tr>
                            <td class="px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                TOTALES
                            </td>
                            <td class="px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-gray-100">
                                ${{ number_format(array_sum(array_column($summary, 'pending')), 2) }}
                            </td>
                            <td class="px-3 py-2 text-right text-sm font-bold text-green-600 dark:text-green-400">
                                ${{ number_format(array_sum(array_column($summary, 'proven')), 2) }}
                            </td>
                            <td class="px-3 py-2 text-right text-sm font-bold text-yellow-600 dark:text-yellow-400">
                                ${{ number_format(abs(array_sum(array_column($summary, 'remaining'))), 2) }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                @php
                                    $totalPending = array_sum(array_column($summary, 'pending'));
                                    $totalProven = array_sum(array_column($summary, 'proven'));
                                    $overallPercentage =
                                        $totalPending > 0 ? min(100, ($totalProven / $totalPending) * 100) : 0;
                                @endphp
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ number_format($overallPercentage, 1) }}%
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Notas importantes -->
            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">ℹ️ Notas Importantes:</h4>
                <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                    <li>• Los gastos se descuentan automáticamente según la categoría seleccionada</li>
                    <li>• El "Monto Aplicado" puede ser menor al total del comprobante</li>
                    <li>• Los excesos se marcan para revisión adicional</li>
                    <li>• Los comprobantes sin categoría no afectan los totales pendientes</li>
                </ul>
            </div>
        </div>
    </div>
@else
    <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center">
        <p class="text-gray-500 dark:text-gray-400">
            {{ $message ?? 'No hay información de comprobación disponible.' }}
        </p>
    </div>
@endif
