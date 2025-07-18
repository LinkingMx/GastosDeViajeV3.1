<div class="space-y-1">
    <div class="flex items-center gap-1 text-sm">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span class="text-gray-900 dark:text-gray-100">
            Salida: <span class="font-medium">{{ $getRecord()->departure_date->format('d/m/Y') }}</span>
        </span>
    </div>
    <div class="flex items-center gap-1 text-sm">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span class="text-gray-900 dark:text-gray-100">
            Regreso: <span class="font-medium">{{ $getRecord()->return_date->format('d/m/Y') }}</span>
        </span>
    </div>
</div>