<?php

namespace App\Filament\Resources\TravelRequestResource\Pages;

use App\Filament\Resources\TravelRequestResource;
use App\Models\Country;
use App\Models\ExpenseConcept;
use App\Models\PerDiem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class CreateTravelRequest extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = TravelRequestResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make('Información General')
                ->description('Datos principales del viaje')
                ->schema($this->getStepOneSchema()),
            Step::make('Servicios y Viáticos')
                ->description('Solicitudes de transporte, hospedaje y viáticos')
                ->schema($this->getStepTwoSchema()),
            Step::make('Resumen y Envío')
                ->description('Revisión final y total estimado')
                ->schema($this->getStepThreeSchema()),
        ];
    }

    protected function getStepOneSchema(): array
    {
        return [
            Select::make('branch_id')
                ->relationship('branch', 'name')
                ->label('Centro de costos principal')
                ->required()
                ->columnSpanFull(),

            // Campo oculto para mantener la funcionalidad del tipo de viaje
            TextInput::make('request_type')
                ->hiddenLabel()
                ->hidden()
                ->dehydrated(),

            Grid::make(2)->schema([
                Select::make('origin_country_id')
                    ->relationship('originCountry', 'name')
                    ->label('País Origen')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('origin_city')
                    ->label('Ciudad Origen')
                    ->required(),
            ]),
            Grid::make(2)->schema([
                Select::make('destination_country_id')
                    ->relationship('destinationCountry', 'name')
                    ->label('País Destino')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        $country = Country::find($state);
                        $set('request_type', $country?->is_foreign ? 'foreign' : 'domestic');
                    })
                    ->required(),
                TextInput::make('destination_city')
                    ->label('Ciudad Destino')
                    ->required(),
            ]),
            Grid::make(2)->schema([
                DatePicker::make('departure_date')
                    ->label('Fecha Salida')
                    ->required(),
                DatePicker::make('return_date')
                    ->label('Fecha Regreso')
                    ->required(),
            ]),
            Textarea::make('notes')
                ->label('Notas / Justificación del Viaje')
                ->columnSpanFull(),

            // Restaurar el return del schema del primer paso sin el bloque de Actions personalizado
            // Eliminar el botón personalizado "Siguiente" para dejar el comportamiento por defecto del wizard
        ];
    }

    protected function getStepTwoSchema(): array
    {
        return [
            Section::make('Resumen del Viaje')
                ->schema([
                    Placeholder::make('travel_summary')
                        ->label('')
                        ->content(function (Get $get) {
                            $originCountryId = $get('origin_country_id');
                            $destinationCountryId = $get('destination_country_id');
                            $originCity = $get('origin_city');
                            $destinationCity = $get('destination_city');
                            $departureDate = $get('departure_date');
                            $returnDate = $get('return_date');

                            $originCountry = $originCountryId ? Country::find($originCountryId)?->name : '';
                            $destCountry = $destinationCountryId ? Country::find($destinationCountryId)?->name : '';

                            // Formatear fechas en español
                            $departureDateFormatted = $departureDate ? Carbon::parse($departureDate)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') : '';
                            $returnDateFormatted = $returnDate ? Carbon::parse($returnDate)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') : '';

                            // Calcular días de viaje
                            $days = '';
                            if ($departureDate && $returnDate) {
                                $departure = Carbon::parse($departureDate)->startOfDay();
                                $return = Carbon::parse($returnDate)->startOfDay();

                                // Calcular días incluyendo ambos días (salida y regreso)
                                $totalDays = $departure->diffInDays($return) + 1;

                                // Asegurar que sea al menos 1 día
                                $totalDays = max(1, $totalDays);

                                $days = "({$totalDays} ".($totalDays == 1 ? 'día' : 'días').')';
                            }

                            return new HtmlString('
                                <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Origen -->
                                        <div class="space-y-2">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">Origen</span>
                                            </div>
                                            <div class="ml-7">
                                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">
                                                    '.$originCity.', '.$originCountry.'
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Destino -->
                                        <div class="space-y-2">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-rose-600 dark:text-rose-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">Destino</span>
                                            </div>
                                            <div class="ml-7">
                                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-rose-100 dark:bg-rose-900 text-rose-800 dark:text-rose-200">
                                                    '.$destinationCity.', '.$destCountry.'
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fecha de Salida -->
                                        <div class="space-y-2">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">Fecha de Salida</span>
                                            </div>
                                            <div class="ml-7">
                                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                                    '.$departureDateFormatted.'
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fecha de Regreso -->
                                        <div class="space-y-2">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">Fecha de Regreso</span>
                                            </div>
                                            <div class="ml-7">
                                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">
                                                    '.$returnDateFormatted.'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Duración del viaje -->
                                    '.($days ? '
                                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center justify-center">
                                            <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Duración total: '.$days.'
                                            </div>
                                        </div>
                                    </div>
                                    ' : '').'
                                </div>
                            ');
                        })
                        ->columnSpanFull(),
                ]),

            Section::make('Servicios Administrados')
                ->description('Solicita servicios como vuelos, hoteles, etc.')
                ->schema(function () {
                    $managedServices = ExpenseConcept::where('is_unmanaged', false)->get();
                    $fields = [];
                    foreach ($managedServices as $service) {
                        $label = $service->description ? ($service->description.' — agrega tu preferencia') : 'Notas';
                        $fields[] = Grid::make(2)->schema([
                            Toggle::make("additional_services.{$service->id}.enabled")
                                ->label($service->name)
                                ->live(),
                            Textarea::make("additional_services.{$service->id}.notes")
                                ->label($label)
                                ->visible(fn (Get $get) => $get("additional_services.{$service->id}.enabled")),
                        ]);
                    }
                    if (empty($fields)) {
                        $fields[] = Placeholder::make('no_managed_services')->content('No hay servicios administrados configurados.');
                    }

                    return $fields;
                }),

            Section::make('Viáticos Estándar')
                ->description('Viáticos aplicables según tu puesto y destino')
                ->schema(function (Get $get) {
                    $user = auth()->user();
                    $requestType = $get('request_type');

                    if (! $requestType) {
                        return [Placeholder::make('select_destination')->content('Selecciona un país de destino para ver los viáticos disponibles.')];
                    }

                    $perDiems = PerDiem::with(['detail.concept'])
                        ->where('position_id', $user->position_id)
                        ->where('scope', $requestType)
                        ->get()
                        ->sortByDesc(function ($perDiem) {
                            return $perDiem->detail->priority ?? 0;
                        });

                    if ($perDiems->isEmpty()) {
                        return [Placeholder::make('no_per_diems')->content('No hay viáticos estándar configurados para tu puesto y el tipo de viaje seleccionado.')];
                    }

                    $departureDate = $get('departure_date');
                    $returnDate = $get('return_date');

                    if (! $departureDate || ! $returnDate) {
                        return [Placeholder::make('select_dates')->content('Selecciona las fechas de viaje para calcular los totales.')];
                    }

                    $departure = Carbon::parse($departureDate)->startOfDay();
                    $return = Carbon::parse($returnDate)->startOfDay();
                    $days = $departure->diffInDays($return) + 1;
                    $days = max(1, $days);

                    $fields = [];
                    foreach ($perDiems as $perDiem) {
                        $detailName = $perDiem->detail?->name ?? null;
                        $conceptName = $perDiem->detail?->concept?->name ?? null;
                        $label = $detailName;
                        if ($conceptName) {
                            $label .= " ({$conceptName})";
                        }
                        $fields[] = Grid::make(3)->schema([
                            Toggle::make("per_diem_data.{$perDiem->id}.enabled")
                                ->label($label)
                                ->live(),
                            Placeholder::make("per_diem_data.{$perDiem->id}.total")
                                ->label('Total Estimado')
                                ->content(fn () => '$'.number_format($days * $perDiem->amount, 2)." ({$days} días)")
                                ->visible(fn (Get $get) => $get("per_diem_data.{$perDiem->id}.enabled")),
                            Textarea::make("per_diem_data.{$perDiem->id}.notes")
                                ->label('Notas')
                                ->visible(fn (Get $get) => $get("per_diem_data.{$perDiem->id}.enabled")),
                        ]);
                    }

                    return $fields;
                })
                ->live(),

            Section::make('Gastos Personalizados')
                ->description('Agrega otros gastos no cubiertos por los viáticos estándar')
                ->schema([
                    Repeater::make('custom_expenses_data')
                        ->label('Gastos')
                        ->columns(3)
                        ->schema([
                            TextInput::make('concept')->label('Concepto')->required(),
                            TextInput::make('amount')->label('Monto')->numeric()->prefix('$')->required(),
                            Textarea::make('justification')->label('Justificación')->columnSpanFull()->required(),
                        ])
                        ->addActionLabel('Agregar Gasto'),
                ]),
        ];
    }

    protected function getStepThreeSchema(): array
    {
        return [
            Section::make('Resumen Final')
                ->schema([
                    Placeholder::make('final_summary')
                        ->label('')
                        ->content(function (Get $get) {
                            $user = auth()->user();
                            $originCountryId = $get('origin_country_id');
                            $destinationCountryId = $get('destination_country_id');
                            $originCity = $get('origin_city');
                            $destinationCity = $get('destination_city');
                            $departureDate = $get('departure_date');
                            $returnDate = $get('return_date');
                            $requestType = $get('request_type');
                            $notes = $get('notes');
                            $branchId = $get('branch_id');

                            // Si request_type está vacío, calcularlo basado en el país de destino
                            if (! $requestType && $destinationCountryId) {
                                $destCountryObj = Country::find($destinationCountryId);
                                $requestType = $destCountryObj && $destCountryObj->is_foreign ? 'foreign' : 'domestic';
                            }

                            $originCountry = $originCountryId ? Country::find($originCountryId)?->name : '';
                            $destCountry = $destinationCountryId ? Country::find($destinationCountryId)?->name : '';
                            $branch = $branchId ? \App\Models\Branch::find($branchId)?->name : '';

                            // Formatear fechas en español
                            $departureDateFormatted = $departureDate ? Carbon::parse($departureDate)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') : '';
                            $returnDateFormatted = $returnDate ? Carbon::parse($returnDate)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') : '';

                            // Calcular días de viaje
                            $totalDays = 0;
                            $daysText = '';
                            if ($departureDate && $returnDate) {
                                $departure = Carbon::parse($departureDate)->startOfDay();
                                $return = Carbon::parse($returnDate)->startOfDay();
                                $totalDays = max(1, $departure->diffInDays($return) + 1);
                                $daysText = $totalDays.($totalDays == 1 ? ' día' : ' días');
                            }

                            // Calcular total de viáticos
                            $perDiemTotal = 0;
                            $perDiemDetails = [];
                            if ($requestType && $totalDays > 0) {
                                $perDiems = PerDiem::with(['detail.concept'])
                                    ->where('position_id', $user->position_id)
                                    ->where('scope', $requestType)
                                    ->get();

                                foreach ($perDiems as $perDiem) {
                                    $enabled = $get("per_diem_data.{$perDiem->id}.enabled");
                                    if ($enabled) {
                                        $amount = $totalDays * $perDiem->amount;
                                        $perDiemTotal += $amount;
                                        $perDiemDetails[] = [
                                            'name' => $perDiem->detail?->name ?? 'Viático',
                                            'concept' => $perDiem->detail?->concept?->name ?? '',
                                            'daily' => $perDiem->amount,
                                            'days' => $totalDays,
                                            'total' => $amount,
                                            'notes' => $get("per_diem_data.{$perDiem->id}.notes") ?: 'No hay notas definidas por el usuario, se procesará de manera estandard.',
                                        ];
                                    }
                                }
                            }

                            // Calcular total de gastos personalizados
                            $customExpensesTotal = 0;
                            $customExpensesData = $get('custom_expenses_data') ?: [];
                            foreach ($customExpensesData as $expense) {
                                if (! empty($expense['amount'])) {
                                    $customExpensesTotal += floatval($expense['amount']);
                                }
                            }

                            // Total general
                            $grandTotal = $perDiemTotal + $customExpensesTotal;

                            // Generar un folio temporal para el preview (se generará uno real al guardar)
                            $tempFolio = 'PREV-'.strtoupper(substr(uniqid(), -8));

                            // Servicios administrados solicitados
                            $requestedServices = [];
                            $managedServices = ExpenseConcept::where('is_unmanaged', false)->get();
                            foreach ($managedServices as $service) {
                                $serviceEnabled = $get("additional_services.{$service->id}.enabled");
                                if ($serviceEnabled) {
                                    $requestedServices[] = [
                                        'name' => $service->name,
                                        'notes' => $get("additional_services.{$service->id}.notes") ?: 'No hay notas definidas por el usuario, se procesará de manera estandard.',
                                    ];
                                }
                            }

                            return new HtmlString('
                                <div class="w-full">
                                    <!-- Folio de la Solicitud -->
                                    <div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                        <div class="flex items-center justify-center">
                                            <div class="text-center bg-amber-100">
                                                <div class="text-sm font-medium mb-1">Folio de Solicitud</div>
                                                <div class="text-2xl font-bold text-blue-800 dark:text-blue-200 font-mono tracking-wider">'.$tempFolio.'</div>
                                                <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">(Se generará automáticamente al guardar)</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Header del Reporte -->
                                    <div class="border-l-4 p-6 rounded-t-lg shadow-lg bg-gray-100 dark:bg-gray-800">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h2 class="text-2xl font-bold dark:text-white">Solicitud de Viaje</h2>
                                                <p class="text-blue-100 dark:text-gray-300 mt-1">Resumen completo de la solicitud</p>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold dark:text-white">$'.number_format($grandTotal, 2).'</div>
                                                <div class="text-blue-100 dark:text-gray-300 text-sm">Total Estimado</div>
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
                                        <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                </svg>
                                                 Detalles del Viaje
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                                <div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                    <div class="text-sm text-emerald-700 dark:text-emerald-300">Origen</div>
                                                    <div class="font-semibold text-emerald-900 dark:text-emerald-100">'.$originCity.'</div>
                                                    <div class="text-xs text-emerald-600 dark:text-emerald-400">'.$originCountry.'</div>
                                                </div>
                                                <div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                    <div class="text-sm text-rose-700 dark:text-rose-300">Destino</div>
                                                    <div class="font-semibold text-rose-900 dark:text-rose-100">'.$destinationCity.'</div>
                                                    <div class="text-xs text-rose-600 dark:text-rose-400">'.$destCountry.'</div>
                                                </div>
                                                <div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                    <div class="text-sm text-blue-700 dark:text-blue-300">Fecha de Salida</div>
                                                    <div class="font-semibold text-blue-900 dark:text-blue-100 text-sm">'.$departureDateFormatted.'</div>
                                                </div>
                                                <div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                    <div class="text-sm text-purple-700 dark:text-purple-300">Fecha de Regreso</div>
                                                    <div class="font-semibold text-purple-900 dark:text-purple-100 text-sm">'.$returnDateFormatted.'</div>
                                                </div>
                                            </div>
                                            <div class="mt-4 text-center">
                                                <div class="inline-flex items-center px-4 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                    <span class="font-semibold mt-2">Duración total: '.$daysText.'</span>
                                                </div>
                                            </div>
                                        </div>

                                        '.($notes ? '
                                        <!-- Notas y Justificación -->
                                        <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                                </svg>
                                                Notas y Justificación del Viaje
                                            </h3>
                                            <div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                <p class="">'.$notes.'</p>
                                            </div>
                                        </div>
                                        ' : '').'

                                        '.(count($requestedServices) > 0 ? '
                                        <!-- Servicios Administrados -->
                                        <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                                <svg class="w-5 h-5 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                 Servicios Administrados Solicitados
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                '.implode('', array_map(function ($service) {
                                return '<div class="border-2 bg-white dark:bg-gray-700 rounded-lg mb-4 border-b border-gray-200 dark:border-gray-700 p-6">
                                                        <div class="font-semibold text-orange-900 dark:text-orange-100">'.$service['name'].'</div>
                                                        <div class="text-sm text-orange-700 dark:text-orange-300 mt-1">'.$service['notes'].'</div>
                                                    </div>';
                            }, $requestedServices)).'
                                            </div>
                                        </div>
                                        ' : '').'

                                        '.(count($perDiemDetails) > 0 ? '
                                        <!-- Viáticos Estándar -->
                                        <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
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
                                return '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
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
                                        ' : '').'

                                        '.(count($customExpensesData) > 0 ? '
                                        <!-- Gastos Personalizados -->
                                        <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
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
                                                                <td class="px-4 py-4 text-sm font-semibold text-red-600 dark:text-red-400">$'.number_format(floatval($expense['amount'] ?? 0), 2).'</td>
                                                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">'.($expense['justification'] ?? 'Sin justificación').'</td>
                                                            </tr>';
                            }, $customExpensesData)).'
                                                    </tbody>
                                                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                                                        <tr>
                                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white text-right">Subtotal Gastos Personalizados:</td>
                                                            <td class="px-4 py-3 text-sm font-bold text-red-600 dark:text-red-400">$'.number_format($customExpensesTotal, 2).'</td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        ' : '').'

                                        <!-- Resumen Final -->
                                        <div class="p-6">
                                            <div class="bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20 border border-green-200 dark:border-green-800 p-6 rounded-lg">
                                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 text-center">Solicitud de Anticipo de gasto</h3>
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                                    <div class="text-center">
                                                        <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">$'.number_format($perDiemTotal, 2).'</div>
                                                        <div class="text-sm text-gray-600 dark:text-gray-400">Viáticos Estándar</div>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">$'.number_format($customExpensesTotal, 2).'</div>
                                                        <div class="text-sm text-gray-600 dark:text-gray-400">Gastos Personalizados</div>
                                                    </div>
                                                    <div class="text-center border-l border-gray-300 dark:border-gray-600">
                                                        <div class="text-3xl font-bold text-green-600 dark:text-green-400">$'.number_format($grandTotal, 2).'</div>
                                                        <div class="text-sm text-gray-600 dark:text-gray-400 font-semibold">Total General</div>
                                                    </div>
                                                </div>
                                                <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                                                    * Los servicios administrados no tienen costo asociado en este resumen ya que son gestionados directamente por la empresa.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ');
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar el user_id del usuario autenticado
        $data['user_id'] = auth()->id();

        // Calcular y asignar request_type basado en el país de destino si no está presente
        if (empty($data['request_type']) && ! empty($data['destination_country_id'])) {
            $destCountry = Country::find($data['destination_country_id']);
            $data['request_type'] = $destCountry && $destCountry->is_foreign ? 'foreign' : 'domestic';
        }

        // Asegurarse de que los campos de fecha sean instancias de Carbon
        if (isset($data['departure_date']) && ! is_a($data['departure_date'], Carbon::class)) {
            $data['departure_date'] = Carbon::parse($data['departure_date']);
        }
        if (isset($data['return_date']) && ! is_a($data['return_date'], Carbon::class)) {
            $data['return_date'] = Carbon::parse($data['return_date']);
        }

        return $data;
    }

    public bool $isSubmittingAndContinuing = false;

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        // Determinar el autorizador basado en la configuración del usuario
        $authorizer = $record->actual_authorizer;

        if ($authorizer) {
            $record->update(['authorizer_id' => $authorizer->id]);
        }

        if ($this->isSubmittingAndContinuing) {
            $this->redirect(self::$resource::getUrl('edit', ['record' => $record]));
        } else {
            $this->redirect(self::$resource::getUrl('index'));
        }
    }

    // Desactivar la notificación estándar de Filament
    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->title('Solicitud Creada')
            ->body('La solicitud de viaje ha sido creada exitosamente como borrador.')
            ->success();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCreateAndSubmitFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Crear Solicitud');
    }

    protected function getCreateAndSubmitFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('createAndSubmit')
            ->label('Crear y Continuar')
            ->action(function () {
                $this->isSubmittingAndContinuing = true;
                $this->create();

                // Devolver una respuesta de redirección en lugar de llamar a $this->redirect()
                return redirect(self::$resource::getUrl('edit', ['record' => $this->getRecord()]));
            })
            ->color('primary')
            ->icon('heroicon-o-arrow-right-circle');
    }
}
