@if (empty($cfdis))
    <div class="text-center py-8 text-gray-500">
        <div class="flex flex-col items-center space-y-2">
            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            <p class="text-lg font-medium">{{ $message ?? 'No hay CFDIs cargados' }}</p>
            @if (str_contains($message ?? '', 'válidos'))
                <p class="text-sm text-gray-400 max-w-md text-center">
                    Los CFDIs deben tener UUID, proveedor, conceptos y montos válidos para poder mostrarse.
                </p>
            @endif
        </div>
    </div>
@else
    <div class="space-y-6">
        @foreach ($cfdis as $uuid => $cfdi)
            <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                {{-- Cabecera del CFDI (NO colapsable) --}}
                <div class="bg-gray-50 p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            📄 CFDI: {{ $cfdi['supplier_name'] }}
                        </h3>
                        <span class="text-sm text-gray-500">
                            {{ $cfdi['concepts_count'] }} concepto(s)
                        </span>
                    </div>

                    {{-- Información de la cabecera en grid compacto --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Proveedor:</span>
                                <span class="text-sm text-gray-900 font-medium">{{ $cfdi['supplier_name'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">UUID:</span>
                                <span class="text-xs text-gray-600 font-mono break-all">{{ $cfdi['uuid'] }}</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Fecha:</span>
                                <span class="text-sm text-gray-900">{{ $cfdi['receipt_date'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Total CFDI:</span>
                                <span
                                    class="text-sm text-gray-900 font-bold text-green-600">${{ number_format($cfdi['total_amount'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Conceptos del CFDI (colapsables) --}}
                <div class="p-4">
                    <div class="space-y-4">
                        @foreach ($cfdi['receipts'] as $receipt)
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                {{-- Cabecera del concepto (colapsable) --}}
                                <div class="bg-gray-50 p-3 cursor-pointer"
                                    onclick="toggleConcept('concept-{{ $receipt->id }}')">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ Str::limit($receipt->concept ?? 'Sin descripción', 60) }}
                                            </div>
                                            <div class="text-sm text-gray-500 mt-1">
                                                Importe: ${{ number_format($receipt->total_amount, 2) }}
                                                @if ($receipt->applied_amount)
                                                    | Aplicado: ${{ number_format($receipt->applied_amount, 2) }}
                                                @endif
                                                @if ($receipt->expenseDetail)
                                                    | Categoría: {{ $receipt->expenseDetail->name }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ml-4 flex items-center space-x-2">
                                            {{-- Botón de eliminar --}}
                                            <button type="button"
                                                onclick="event.stopPropagation(); showDeleteModal({{ $receipt->id }}, '{{ addslashes($receipt->concept ?? 'Sin descripción') }}')"
                                                class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition-colors"
                                                title="Eliminar concepto">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                            {{-- Flecha de colapso --}}
                                            <svg class="w-5 h-5 text-gray-400 transform transition-transform concept-arrow"
                                                id="arrow-{{ $receipt->id }}" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                {{-- Contenido del concepto (inicialmente oculto) --}}
                                <div id="concept-{{ $receipt->id }}"
                                    class="concept-content hidden p-4 border-t border-gray-200 bg-white">
                                    <div class="space-y-4">
                                        {{-- Descripción del concepto --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Descripción del Concepto
                                            </label>
                                            <div
                                                class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-900">
                                                {{ $receipt->concept ?? 'Sin descripción' }}
                                            </div>
                                        </div>

                                        {{-- Campos editables --}}
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    Importe del Concepto
                                                </label>
                                                <div
                                                    class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-900">
                                                    ${{ number_format($receipt->total_amount, 2) }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    Monto Aplicado
                                                </label>
                                                <input type="number" step="0.01" min="0"
                                                    value="{{ $receipt->applied_amount ?? '' }}" placeholder="0.00"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateReceiptField({{ $receipt->id }}, 'applied_amount', this.value)">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    Concepto de Gasto
                                                </label>
                                                <select
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateReceiptField({{ $receipt->id }}, 'expense_detail_id', this.value)">
                                                    <option value="">Seleccionar concepto de gasto</option>
                                                    @foreach (\App\Models\ExpenseDetail::with('concept')->get() as $detail)
                                                        <option value="{{ $detail->id }}"
                                                            {{ $receipt->expense_detail_id == $detail->id ? 'selected' : '' }}>
                                                            {{ $detail->name }} ({{ $detail->concept->name }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Notas --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Notas
                                            </label>
                                            <textarea rows="2" placeholder="Notas adicionales sobre este concepto..."
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateReceiptField({{ $receipt->id }}, 'notes', this.value)">{{ $receipt->notes ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal de confirmación de eliminación --}}
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden"
        style="z-index: 99999;">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4" onclick="event.stopPropagation();">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                </div>
                <div class="mt-4 text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">⚠️ Eliminar Concepto</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        ¿Estás seguro de que deseas eliminar este concepto?
                    </p>
                    <div class="mt-3 p-3 bg-gray-50 rounded-md border">
                        <p id="conceptDescription" class="text-sm font-medium text-gray-700 break-words"></p>
                    </div>
                    <p class="mt-2 text-xs text-red-600 font-semibold">
                        ⚠️ Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="mt-6 flex justify-center space-x-4">
                    <button id="cancelBtn" type="button"
                        class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                        ❌ Cancelar
                    </button>
                    <button id="confirmBtn" type="button"
                        class="px-6 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                        🗑️ Confirmar Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript --}}
    <script>
        // Asegurar que el token CSRF esté disponible
        document.addEventListener('DOMContentLoaded', function() {
            if (!document.querySelector('meta[name="csrf-token"]')) {
                const meta = document.createElement('meta');
                meta.name = 'csrf-token';
                meta.content = '{{ csrf_token() }}';
                document.head.appendChild(meta);
                console.log('Token CSRF agregado al DOM');
            }
        });

        let currentReceiptId = null;

        function toggleConcept(conceptId) {
            const content = document.getElementById(conceptId);
            const arrow = document.getElementById('arrow-' + conceptId.replace('concept-', ''));

            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }

        function updateReceiptField(receiptId, fieldName, value) {
            console.log(`Actualizando campo ${fieldName} del receipt ${receiptId} con valor:`, value);

            // Actualizar en la base de datos directamente via AJAX
            const updateUrl = `/admin/expense-verifications/update-receipt-field/${receiptId}`;

            fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        field: fieldName,
                        value: value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('✅ Campo actualizado correctamente en la base de datos');

                        // También actualizar el repeater oculto si existe
                        const hiddenRepeater = document.querySelector('[data-field-wrapper="fiscal_receipts"]');
                        if (hiddenRepeater) {
                            const receiptItem = hiddenRepeater.querySelector(`[data-repeater-item-id="${receiptId}"]`);
                            if (receiptItem) {
                                const field = receiptItem.querySelector(`[name*="${fieldName}"]`);
                                if (field) {
                                    field.value = value;
                                    field.dispatchEvent(new Event('change', {
                                        bubbles: true
                                    }));
                                }
                            }
                        }

                        // Actualizar la interfaz visual si es categoría
                        if (fieldName === 'expense_detail_id' && value) {
                            const categoryInfo = document.querySelector(
                                `#concept-${receiptId} select option[value="${value}"]`);
                            if (categoryInfo) {
                                const categoryName = categoryInfo.textContent;
                                // Actualizar la info en la cabecera del concepto
                                const headerInfo = document.querySelector(
                                    `[onclick="toggleConcept('concept-${receiptId}')"] .text-gray-500`);
                                if (headerInfo && !headerInfo.textContent.includes('Categoría:')) {
                                    headerInfo.textContent += ` | Categoría: ${categoryName.split('(')[0].trim()}`;
                                }
                            }
                        }
                    } else {
                        console.error('❌ Error al actualizar campo:', data.message);
                        alert('Error al actualizar: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('❌ Error de red al actualizar campo:', error);
                    alert('Error de conexión: ' + error.message);
                });
        }

        function updateFilamentFormState(receiptId) {
            console.log('Actualizando estado del formulario para receipt:', receiptId);

            // Buscar y remover el item del repeater oculto de Filament
            const hiddenRepeater = document.querySelector('[data-field-wrapper="fiscal_receipts"]');
            if (hiddenRepeater) {
                const receiptItem = hiddenRepeater.querySelector(`[data-repeater-item-id="${receiptId}"]`);
                if (receiptItem) {
                    console.log('Removiendo item del repeater de Filament');
                    receiptItem.remove();

                    // Disparar evento de cambio en el repeater
                    const repeaterContainer = hiddenRepeater.querySelector('[data-field-wrapper]');
                    if (repeaterContainer) {
                        repeaterContainer.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                }
            }

            // También buscar por otros selectores posibles
            const allRepeaterItems = document.querySelectorAll(`[data-repeater-item-id="${receiptId}"]`);
            allRepeaterItems.forEach(item => {
                console.log('Removiendo item adicional del repeater');
                item.remove();
            });
        }

        function triggerFormRecalculation() {
            console.log('Disparando recalculación del formulario');

            // Disparar eventos en campos de resumen para forzar recalculación
            const summaryFields = document.querySelectorAll('[name*="total"], [name*="summary"], [name*="amount"]');
            summaryFields.forEach(field => {
                field.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
                field.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            });

            // Disparar evento global de cambio en el formulario
            const form = document.querySelector('form');
            if (form) {
                form.dispatchEvent(new Event('formUpdated', {
                    bubbles: true
                }));
            }

            // Disparar evento específico de Livewire si está presente
            if (window.Livewire) {
                try {
                    window.Livewire.emit('refreshComponent');
                } catch (e) {
                    console.log('No se pudo disparar evento Livewire:', e);
                }
            }
        }

        function updateSummaryDisplay(summaryData) {
            console.log('Actualizando resumen con datos:', summaryData);

            // Buscar el contenedor del resumen específico
            const summaryContainer = document.querySelector('.expense-verification-status') ||
                document.querySelector('[data-field-wrapper*="summary"]') ||
                document.querySelector('.travel-request-summary');

            if (summaryContainer) {
                console.log('Encontrado contenedor de resumen, actualizando directamente...');

                // Actualizar los valores en la tabla de resumen
                Object.keys(summaryData).forEach(category => {
                    const categoryData = summaryData[category];

                    // Buscar la fila de la categoría en la tabla
                    const rows = summaryContainer.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        const categoryCell = row.querySelector('td:first-child');
                        if (categoryCell && categoryCell.textContent.includes(categoryData.name)) {
                            // Actualizar los valores en las celdas
                            const cells = row.querySelectorAll('td');
                            if (cells.length >= 4) {
                                // Celda de "Comprobado" (índice 2)
                                const provenCell = cells[2];
                                if (provenCell) {
                                    provenCell.innerHTML =
                                        `<span class="text-green-600 dark:text-green-400 font-medium">$${new Intl.NumberFormat('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(categoryData.proven)}</span>`;
                                }

                                // Celda de "Pendiente" (índice 3)
                                const remainingCell = cells[3];
                                if (remainingCell) {
                                    const isComplete = categoryData.remaining <= 0;
                                    const isExcess = categoryData.remaining < 0;
                                    const colorClass = isComplete ? 'text-green-600 dark:text-green-400' :
                                        'text-yellow-600 dark:text-yellow-400';
                                    const excessText = isExcess ?
                                        '<span class="text-xs text-red-500">(Exceso)</span>' : '';
                                    remainingCell.innerHTML =
                                        `<span class="${colorClass} font-medium">$${new Intl.NumberFormat('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(Math.abs(categoryData.remaining))} ${excessText}</span>`;
                                }

                                // Celda de "Estado" (índice 4)
                                const statusCell = cells[4];
                                if (statusCell) {
                                    const isComplete = categoryData.remaining <= 0;
                                    const isExcess = categoryData.remaining < 0;
                                    const percentage = categoryData.pending > 0 ? Math.min(100, (
                                        categoryData.proven / categoryData.pending) * 100) : 0;

                                    let statusHTML = '';
                                    if (isComplete) {
                                        if (isExcess) {
                                            statusHTML =
                                                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300">⚠️ Exceso</span>';
                                        } else {
                                            statusHTML =
                                                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300">✅ Completo</span>';
                                        }
                                    } else {
                                        statusHTML =
                                            `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300">⏳ ${percentage.toFixed(1)}%</span>`;
                                    }
                                    statusCell.innerHTML = statusHTML;
                                }
                            }
                        }
                    });
                });

                // Actualizar totales del footer
                const footer = summaryContainer.querySelector('tfoot tr');
                if (footer) {
                    const totalProven = Object.values(summaryData).reduce((sum, item) => sum + item.proven, 0);
                    const totalRemaining = Object.values(summaryData).reduce((sum, item) => sum + Math.abs(item.remaining),
                        0);

                    const cells = footer.querySelectorAll('td');
                    if (cells.length >= 4) {
                        // Total comprobado
                        if (cells[2]) {
                            cells[2].innerHTML =
                                `$${new Intl.NumberFormat('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(totalProven)}`;
                            cells[2].className =
                                'px-3 py-2 text-right text-sm font-bold text-green-600 dark:text-green-400';
                        }
                        // Total pendiente
                        if (cells[3]) {
                            cells[3].innerHTML =
                                `$${new Intl.NumberFormat('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(totalRemaining)}`;
                            cells[3].className =
                                'px-3 py-2 text-right text-sm font-bold text-yellow-600 dark:text-yellow-400';
                        }
                    }
                }

                console.log('Resumen actualizado exitosamente');
            } else {
                console.log('No se encontró contenedor de resumen, se actualizará con el reload');
            }
        }

        function showDeleteModal(receiptId, conceptDescription) {
            console.log('Mostrando modal para receipt:', receiptId);
            currentReceiptId = receiptId;

            const modal = document.getElementById('deleteModal');
            const descriptionElement = document.getElementById('conceptDescription');

            if (modal && descriptionElement) {
                descriptionElement.textContent = conceptDescription.substring(0, 80) + (conceptDescription.length > 80 ?
                    '...' : '');
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                console.log('Modal mostrado');
            } else {
                console.error('No se pudo encontrar el modal o el elemento de descripción');
            }
        }

        function hideDeleteModal() {
            console.log('Ocultando modal');
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
                currentReceiptId = null;

                // Restaurar botón de confirmación
                const confirmBtn = document.getElementById('confirmBtn');
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = '🗑️ Confirmar Eliminar';
                }
            }
        }

        function confirmDelete() {
            console.log('=== DIAGNÓSTICO DE ELIMINACIÓN ===');
            console.log('Receipt ID:', currentReceiptId);

            if (!currentReceiptId) {
                alert('Error: No se encontró el ID del concepto a eliminar');
                return;
            }

            // Primero probar con una petición GET simple para verificar conectividad
            const testUrl = `/admin/expense-verifications/delete-receipt/${currentReceiptId}`;
            console.log('URL a probar:', testUrl);

            // Test 1: Verificar si la URL es válida con GET
            console.log('=== TEST 1: Verificando URL con GET ===');
            fetch(testUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Test GET - Status:', response.status);
                    console.log('Test GET - URL alcanzable:', response.status !== 404);

                    // Test 2: Ahora intentar con DELETE
                    console.log('=== TEST 2: Intentando DELETE ===');
                    return testDeleteRequest();
                })
                .catch(error => {
                    console.error('Test GET falló:', error);
                    alert('❌ Error de conectividad: ' + error.message);
                    hideDeleteModal();
                });
        }

        function testDeleteRequest() {
            const url = `/admin/expense-verifications/delete-receipt/${currentReceiptId}`;

            // Obtener token CSRF
            let csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                // Crear token si no existe
                csrfToken = document.createElement('meta');
                csrfToken.name = 'csrf-token';
                csrfToken.content = '{{ csrf_token() }}';
                document.head.appendChild(csrfToken);
                console.log('Token CSRF creado');
            }

            const tokenValue = csrfToken.getAttribute('content');
            console.log('Token CSRF:', tokenValue ? tokenValue.substring(0, 10) + '...' : 'VACÍO');

            console.log('Enviando DELETE request...');

            return fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': tokenValue,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('=== RESPUESTA DELETE ===');
                    console.log('Status:', response.status);
                    console.log('Headers:', [...response.headers.entries()]);

                    return response.text().then(text => {
                        console.log('Response text:', text);

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        }

                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            throw new Error(`Invalid JSON: ${text}`);
                        }
                    });
                })
                .then(data => {
                    console.log('=== DATOS RECIBIDOS ===');
                    console.log(data);

                    if (data.success) {
                        alert('✅ Eliminación exitosa en el servidor');
                        // Remover del DOM
                        const conceptElement = document.querySelector(`#concept-${currentReceiptId}`);
                        if (conceptElement) {
                            const borderElement = conceptElement.closest('.border');
                            if (borderElement) {
                                borderElement.remove();
                                console.log('Elemento removido del DOM');
                            }
                        }

                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        alert('❌ Error del servidor: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.log('=== ERROR EN DELETE ===');
                    console.error('Error completo:', error);
                    alert('❌ Error: ' + error.message);
                })
                .finally(() => {
                    hideDeleteModal();
                });
        }

        // Inicializar event listeners cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM listo, configurando event listeners');

            // Event listeners para los botones
            const cancelBtn = document.getElementById('cancelBtn');
            const confirmBtn = document.getElementById('confirmBtn');
            const modal = document.getElementById('deleteModal');

            if (cancelBtn) {
                cancelBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Botón cancelar clickeado');
                    hideDeleteModal();
                });
            } else {
                console.error('No se encontró el botón cancelar');
            }

            if (confirmBtn) {
                confirmBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Botón confirmar clickeado');
                    confirmDelete();
                });
            } else {
                console.error('No se encontró el botón confirmar');
            }

            // Cerrar modal al hacer clic fuera
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        console.log('Click fuera del modal');
                        hideDeleteModal();
                    }
                });
            }

            // Cerrar modal con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                    console.log('Tecla ESC presionada');
                    hideDeleteModal();
                }
            });
        });
    </script>
@endif
