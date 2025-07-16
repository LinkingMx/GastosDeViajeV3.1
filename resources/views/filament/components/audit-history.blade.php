<div class="space-y-4">
    @if(empty($auditHistory))
        <div class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-2">No hay historial de auditoría disponible</p>
        </div>
    @else
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                @foreach($auditHistory as $index => $entry)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-900 font-medium">{{ $entry['action'] }}</p>
                                        @if(isset($entry['data']['reason']) && !empty($entry['data']['reason']))
                                            <p class="mt-1 text-sm text-gray-600">
                                                <strong>Motivo:</strong> {{ $entry['data']['reason'] }}
                                            </p>
                                        @endif
                                        @if(isset($entry['data']['note']) && !empty($entry['data']['note']))
                                            <p class="mt-1 text-sm text-gray-600">
                                                <strong>Nota:</strong> {{ $entry['data']['note'] }}
                                            </p>
                                        @endif
                                        @if(isset($entry['data']['previous_status']))
                                            <p class="mt-1 text-sm text-gray-500">
                                                Estado anterior: {{ $entry['data']['previous_status'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                        <p>{{ $entry['user'] }}</p>
                                        <time>{{ $entry['timestamp'] }}</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>