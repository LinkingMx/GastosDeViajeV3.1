@if ($isEmpty)
    <x-filament::section class="text-center py-12">
        <div class="flex flex-col items-center">
            <div class="rounded-full bg-gray-100 dark:bg-gray-800 p-4 mb-4">
                <x-heroicon-o-document-text class="h-8 w-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No hay CFDIs cargados</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md">
                Los comprobantes fiscales digitales aparecerán aquí una vez que cargues archivos XML válidos.
            </p>
        </div>
    </x-filament::section>
@else
    <div class="space-y-6">
        @foreach ($cfdis as $uuid => $conceptos)
            @php
                $firstConcept = $conceptos->first();
                $total = $conceptos->sum('total_amount');
                $applied = $conceptos->sum('applied_amount');
                $fecha = $firstConcept->receipt_date ? $firstConcept->receipt_date->format('d/m/Y') : 'Sin fecha';
                $percentage = $total > 0 ? round(($applied / $total) * 100, 1) : 0;
                $isComplete = $percentage >= 100;
                $statusText = $isComplete
                    ? 'Completamente aplicado'
                    : ($percentage > 0
                        ? 'Parcialmente aplicado'
                        : 'Sin aplicar');
                $statusColor = $isComplete
                    ? 'text-green-600 dark:text-green-400'
                    : ($percentage > 0
                        ? 'text-yellow-600 dark:text-yellow-400'
                        : 'text-red-600 dark:text-red-400');
            @endphp

            <!-- CFDI Principal usando Section de Filament -->
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-document-text class="h-5 w-5 text-primary-600" />
                        <span class="font-semibold">{{ $firstConcept->supplier_name ?? 'Sin proveedor' }}</span>
                    </div>
                </x-slot>
                
                <x-slot name="description">
                    CFDI - {{ $fecha }}
                </x-slot>
                
                <x-slot name="headerEnd">
                    <div class="flex flex-col items-end gap-3">
                        <div class="flex flex-wrap gap-2 justify-end">
                            <x-filament::badge 
                                :color="$isComplete ? 'success' : ($percentage > 0 ? 'warning' : 'danger')"
                                size="sm"
                            >
                                {{ $statusText }} ({{ $percentage }}%)
                            </x-filament::badge>
                            <x-filament::badge color="gray" size="sm">
                                {{ count($conceptos) }} conceptos
                            </x-filament::badge>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format($total, 2) }}
                        </div>
                    </div>
                </x-slot>

                <!-- Información del CFDI en grid compacto -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="space-y-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Fecha</span>
                        <div>
                            <x-filament::badge color="info" size="sm">
                                {{ $fecha }}
                            </x-filament::badge>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">RFC</span>
                        <div>
                            <x-filament::badge color="gray" size="sm">
                                {{ $firstConcept->supplier_rfc ?? 'No especificado' }}
                            </x-filament::badge>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">UUID</span>
                        <div>
                            <x-filament::badge color="gray" size="sm" class="font-mono text-xs">
                                {{ Str::limit($uuid, 8) }}...
                            </x-filament::badge>
                        </div>
                    </div>
                </div>

                <!-- Conceptos del CFDI -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-3">
                        <x-heroicon-o-list-bullet class="h-5 w-5 text-primary-600" />
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Conceptos
                        </h4>
                    </div>

                    <div class="space-y-4">
                        @foreach ($conceptos as $concepto)
                            @php
                                $appliedAmount = $concepto->applied_amount ?? 0;
                                $totalAmount = $concepto->total_amount ?? 0;
                                $conceptPercentage = $totalAmount > 0 ? ($appliedAmount / $totalAmount) * 100 : 0;
                                $badgeColor = $conceptPercentage >= 100 ? 'success' : ($conceptPercentage > 0 ? 'warning' : 'danger');
                            @endphp

                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <!-- Header del concepto compacto -->
                                <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <h5 class="font-medium text-gray-900 dark:text-white truncate">
                                                {{ Str::limit($concepto->concept ?? 'Sin descripción', 50) }}
                                            </h5>
                                        </div>
                                        <div class="flex items-center gap-3 ml-4">
                                            <x-filament::badge color="gray" size="sm">
                                                ${{ number_format($totalAmount, 2) }}
                                            </x-filament::badge>
                                            <x-filament::badge :color="$badgeColor" size="sm">
                                                ${{ number_format($appliedAmount, 2) }}
                                            </x-filament::badge>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contenido del concepto -->
                                <div class="p-4 space-y-4">
                                    <!-- Descripción completa si es diferente del título -->
                                    @if(strlen($concepto->concept ?? '') > 50)
                                        <div class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg">
                                            {{ $concepto->concept }}
                                        </div>
                                    @endif

                                    <!-- Grid de inputs -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        <!-- Categorización -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Categoría de Gasto
                                            </label>
                                            <select
                                                class="w-full text-sm border-gray-300 dark:border-gray-700 rounded-lg focus:ring-primary-600 focus:border-primary-600 dark:bg-gray-800"
                                                onchange="updateReceiptField({{ $concepto->id }}, 'expense_detail_id', this.value)"
                                            >
                                                <option value="">Seleccionar categoría...</option>
                                                @foreach (\App\Models\ExpenseDetail::with('concept')->get() as $detail)
                                                    <option value="{{ $detail->id }}" {{ $concepto->expense_detail_id == $detail->id ? 'selected' : '' }}>
                                                        {{ $detail->name }} ({{ $detail->concept->name }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if ($concepto->expenseDetail)
                                                <div class="flex items-center gap-2 mt-2">
                                                    <x-filament::badge color="primary" size="sm">
                                                        {{ $concepto->expenseDetail->name }}
                                                    </x-filament::badge>
                                                    @if ($concepto->expenseDetail->concept)
                                                        <x-filament::badge color="info" size="sm">
                                                            {{ $concepto->expenseDetail->concept->name }}
                                                        </x-filament::badge>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Monto Aplicado -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Monto Aplicado
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">$</span>
                                                </div>
                                                <input 
                                                    type="number" 
                                                    step="0.01" 
                                                    min="0" 
                                                    max="{{ $totalAmount }}"
                                                    value="{{ $appliedAmount }}" 
                                                    placeholder="0.00"
                                                    class="w-full pl-7 text-sm border-gray-300 dark:border-gray-700 rounded-lg focus:ring-primary-600 focus:border-primary-600 dark:bg-gray-800"
                                                    onchange="updateReceiptField({{ $concepto->id }}, 'applied_amount', this.value)"
                                                >
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Máximo: ${{ number_format($totalAmount, 2) }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notas -->
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Notas adicionales
                                        </label>
                                        <textarea 
                                            rows="2" 
                                            placeholder="Agregar notas sobre este concepto..."
                                            class="w-full text-sm border-gray-300 dark:border-gray-700 rounded-lg focus:ring-primary-600 focus:border-primary-600 dark:bg-gray-800 resize-none"
                                            onchange="updateReceiptField({{ $concepto->id }}, 'notes', this.value)"
                                        >{{ $concepto->notes ?? '' }}</textarea>
                                    </div>

                                    @if ($concepto->notes)
                                        <div class="flex items-start gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                            <x-heroicon-o-chat-bubble-left-ellipsis class="h-4 w-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                                            <span class="text-sm text-blue-700 dark:text-blue-300">{{ $concepto->notes }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Resumen del CFDI -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total CFDI</dt>
                            <dd class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                                ${{ number_format($total, 2) }}
                            </dd>
                        </div>
                        <div class="text-center">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Aplicado</dt>
                            <dd class="mt-2 text-2xl font-bold {{ $statusColor }}">
                                ${{ number_format($applied, 2) }}
                            </dd>
                        </div>
                        <div class="text-center">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Progreso</dt>
                            <dd class="mt-2 space-y-2">
                                <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $isComplete ? 'bg-success-500' : ($percentage > 0 ? 'bg-warning-500' : 'bg-danger-500') }}"
                                        style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                <div class="text-sm font-semibold {{ $statusColor }}">{{ $percentage }}%</div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </x-filament::section>
        @endforeach
    </div>
@endif
