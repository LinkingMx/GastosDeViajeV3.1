<div class="space-y-1">
    <div class="flex items-center gap-1 text-sm">
        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
        </svg>
        <span class="text-gray-900 dark:text-gray-100">
            <span class="font-medium">{{ $getRecord()->origin_city ?? 'Sin definir' }}</span>
        </span>
    </div>
    <div class="flex items-center gap-1 text-sm">
        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <span class="text-gray-900 dark:text-gray-100">
            <span class="font-medium">{{ $getRecord()->destination_city }}</span>
        </span>
    </div>
</div>