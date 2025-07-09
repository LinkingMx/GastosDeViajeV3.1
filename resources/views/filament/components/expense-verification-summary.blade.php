@if ($request)
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white"> Resumen Monetario - {{ $request->folio }}
            </h3>
        </div>
        <div class="p-4 space-y-6"> <!-- Anticipo de Tesorería -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-3"> 💰 Anticipo de Tesorería </h4>
                @if ($request->advance_deposit_made)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">Monto del Anticipo</p>
                            <p class="text-lg font-bold text-blue-900 dark:text-blue-100">
                                ${{ number_format($request->advance_deposit_amount ?? 0, 2) }} </p>
                        </div>
                        <div>
                            <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">Fecha de Depósito</p>
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                {{ $request->advance_deposit_made_at ? $request->advance_deposit_made_at->format('d/m/Y H:i') : 'No especificada' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">Procesado por</p>
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                {{ $request->advanceDepositMadeByUser?->name ?? 'Sistema' }} </p>
                        </div>
                    </div>
                    @if ($request->advance_deposit_notes)
                        <div class="mt-3 pt-3 border-t border-blue-200 dark:border-blue-800">
                            <p class="text-xs text-blue-700 dark:text-blue-300 font-medium mb-1">Notas</p>
                            <p class="text-sm text-blue-800 dark:text-blue-200"> {{ $request->advance_deposit_notes }}
                            </p>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <p class="text-sm text-blue-700 dark:text-blue-300"> ❌ No se ha registrado anticipo de tesorería
                        </p>
                    </div>
                @endif
            </div>
            @php
                $user = $request->user;
                $requestType = $request->request_type;

                // Calcular días de viaje
                $totalDays = 0;
                if ($request->departure_date && $request->return_date) {
                    $departure = $request->departure_date->startOfDay();
                    $return = $request->return_date->startOfDay();
                    $totalDays = max(1, $departure->diffInDays($return) + 1);
                }

                // Calcular viáticos estándar usando la misma lógica que ViewTravelRequest
                $perDiemTotal = 0;
                $perDiemDetails = [];
                $perDiemData = $request->per_diem_data ?? [];
                $hasPerDiemConfig = false;

                if ($requestType && $totalDays > 0 && $user->position_id) {
                    $perDiems = \App\Models\PerDiem::with(['detail.concept'])
                        ->where('position_id', $user->position_id)
                        ->where('scope', $requestType)
                        ->get();
                    $hasPerDiemConfig = $perDiems->count() > 0;

                    foreach ($perDiems as $perDiem) {
                        $isEnabled = !empty($perDiemData)
                            ? isset($perDiemData[$perDiem->id]) && ($perDiemData[$perDiem->id]['enabled'] ?? false)
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
                                'notes' =>
                                    !empty($perDiemData) && isset($perDiemData[$perDiem->id]['notes'])
                                        ? $perDiemData[$perDiem->id]['notes']
                                        : 'Procesado según políticas estándar.',
                            ];
                        }
                    }
                }

                // Calcular gastos personalizados
                $customExpensesTotal = 0;
                $customExpensesData = $request->custom_expenses_data ?? [];

                if (is_array($customExpensesData)) {
                    foreach ($customExpensesData as $expense) {
                        if (!empty($expense['amount'])) {
                            $customExpensesTotal += floatval($expense['amount']);
                        }
                    }
                }

                $totalRequested = $perDiemTotal + $customExpensesTotal;
            @endphp

            @if (count($perDiemDetails) > 0)
                <!-- Viáticos Estándar Aprobados -->
                <div
                    class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-green-900 dark:text-green-100 mb-3 flex items-center"> <svg
                            class="w-4 h-4 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z">
                            </path>
                        </svg> Viáticos Estándar Aprobados </h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-green-100 dark:bg-green-900/40">
                                <tr>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-medium text-green-800 dark:text-green-200 uppercase">
                                        Concepto </th>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-medium text-green-800 dark:text-green-200 uppercase">
                                        Monto Diario </th>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-medium text-green-800 dark:text-green-200 uppercase">
                                        Días </th>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-medium text-green-800 dark:text-green-200 uppercase">
                                        Total </th>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-medium text-green-800 dark:text-green-200 uppercase">
                                        Notas </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-green-200 dark:divide-green-800">
                                @foreach ($perDiemDetails as $detail)
                                    <tr class="hover:bg-green-50 dark:hover:bg-green-900/10">
                                        <td class="px-3 py-2">
                                            <div class="font-medium text-green-900 dark:text-green-100">
                                                {{ $detail['name'] }}</div>
                                            @if ($detail['concept'])
                                                <div class="text-xs text-green-600 dark:text-green-400">
                                                    ({{ $detail['concept'] }})
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-green-900 dark:text-green-100">
                                            ${{ number_format($detail['daily'], 2) }}</td>
                                        <td class="px-3 py-2 text-green-900 dark:text-green-100">{{ $detail['days'] }}
                                        </td>
                                        <td class="px-3 py-2 text-green-900 dark:text-green-100 font-semibold">
                                            ${{ number_format($detail['total'], 2) }}</td>
                                        <td class="px-3 py-2 text-green-700 dark:text-green-300 text-xs">
                                            {{ $detail['notes'] ?: 'Sin notas' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-green-100 dark:bg-green-900/40">
                                <tr>
                                    <td colspan="3"
                                        class="px-3 py-2 text-sm font-semibold text-green-900 dark:text-green-100 text-right">
                                        Subtotal Viáticos: </td>
                                    <td class="px-3 py-2 text-sm font-bold text-green-900 dark:text-green-100">
                                        ${{ number_format($perDiemTotal, 2) }} </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @elseif (!$hasPerDiemConfig)
                <!-- Mensaje cuando no hay viáticos configurados -->
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-yellow-900 dark:text-yellow-100 mb-3 flex items-center"> <svg
                            class="w-4 h-4 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z">
                            </path>
                        </svg> Viáticos Estándar </h4>
                    <div class="flex items-start"> <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-yellow-800 dark:text-yellow-200 font-medium">No hay viáticos configurados</p>
                            <p class="text-yellow-700 dark:text-yellow-300 text-sm mt-1"> No se encontraron viáticos
                                configurados para la posición "{{ $user->position?->name ?? 'Sin posición' }}" y tipo
                                de viaje "{{ $requestType ?? 'No definido' }}". </p>
                        </div>
                    </div>
                </div>
            @endif

            @if (is_array($customExpensesData) && count($customExpensesData) > 0)
                <!-- Gastos Personalizados -->
                <div
                    class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-orange-900 dark:text-orange-100 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z">
                            </path>
                        </svg> Gastos Personalizados
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-orange-100 dark:bg-orange-900/40">
                                <tr>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-medium text-orange-800 dark:text-orange-200 uppercase">
                                        Tipo </th>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-medium text-orange-800 dark:text-orange-200 uppercase">
                                        Descripción </th>
                                    <th
                                        class="px-3 py-2 text-right text-xs font-medium text-orange-800 dark:text-orange-200 uppercase">
                                        Monto </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-orange-200 dark:divide-orange-800">
                                @foreach ($customExpensesData as $expense)
                                    <tr class="hover:bg-orange-50 dark:hover:bg-orange-900/10">
                                        <td class="px-3 py-2 text-orange-900 dark:text-orange-100 font-medium">
                                            {{ $expense['type'] ?? 'Gasto Especial' }}
                                        </td>
                                        <td class="px-3 py-2 text-orange-700 dark:text-orange-300">
                                            {{ $expense['description'] ?? 'Sin descripción' }}
                                        </td>
                                        <td
                                            class="px-3 py-2 text-right text-orange-900 dark:text-orange-100 font-semibold">
                                            ${{ number_format($expense['amount'] ?? 0, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-orange-100 dark:bg-orange-900/40">
                                <tr>
                                    <td colspan="2"
                                        class="px-3 py-2 text-sm font-semibold text-orange-900 dark:text-orange-100 text-right">
                                        Subtotal Gastos Personalizados:
                                    </td>
                                    <td
                                        class="px-3 py-2 text-sm font-bold text-orange-900 dark:text-orange-100 text-right">
                                        ${{ number_format($customExpensesTotal, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Totales Generales -->
            <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                    📊 Resumen de Totales
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Total Viáticos Estándar</p>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">
                            ${{ number_format($perDiemTotal, 2) }}
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Total Gastos Personalizados
                        </p>
                        <p class="text-lg font-bold text-orange-600 dark:text-orange-400">
                            ${{ number_format($customExpensesTotal, 2) }}
                        </p>
                    </div>
                    <div class="text-center bg-gray-100 dark:bg-gray-800 rounded-lg p-3">
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Total General Solicitado</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">
                            ${{ number_format($totalRequested, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Comparación Anticipo vs Solicitado -->
            @if ($request->advance_deposit_made && $request->advance_deposit_amount)
                @php
                    $advance = (float) $request->advance_deposit_amount;
                    $difference = $advance - $totalRequested;
                    $diffType = $difference >= 0 ? 'surplus' : 'deficit';
                @endphp

                <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        ⚖️ Comparación Anticipo vs Solicitado
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Anticipo Recibido</p>
                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                ${{ number_format($advance, 2) }}
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Total Solicitado</p>
                            <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                ${{ number_format($totalRequested, 2) }}
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                                {{ $diffType === 'surplus' ? 'Excedente' : 'Faltante' }}
                            </p>
                            <p
                                class="text-lg font-bold {{ $diffType === 'surplus' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                ${{ number_format(abs($difference), 2) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        @if ($diffType === 'surplus')
                            <p class="text-sm text-green-600 dark:text-green-400">
                                ✅ El anticipo cubre todos los gastos solicitados
                            </p>
                        @else
                            <p class="text-sm text-red-600 dark:text-red-400">
                                ⚠️ El anticipo no cubre todos los gastos solicitados
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center">
        <p class="text-gray-500 dark:text-gray-400">
            {{ $message ?? 'No hay información monetaria disponible.' }}
        </p>
    </div>
@endif
