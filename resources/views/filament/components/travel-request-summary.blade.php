@if ($request)
    <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white">{{ $request->folio }}</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Solicitante: {{ $request->user->name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Estado: {{ $request->status_display }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Destino: {{ $request->destination_city }}, {{ $request->destinationCountry?->name }}
                </p>
                @if ($request->departure_date && $request->return_date)
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Fechas: {{ $request->departure_date->format('d/m/Y') }} -
                        {{ $request->return_date->format('d/m/Y') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@else
    <p class="text-gray-500 dark:text-gray-400">
        {{ $message ?? 'No hay información de la solicitud disponible.' }}
    </p>
@endif
