<?php

namespace App\Filament\Resources\TravelRequestResource\Pages;

use App\Filament\Resources\TravelRequestResource;
use App\Models\Country;
use App\Models\ExpenseConcept;
use App\Models\PerDiem;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class EditTravelRequest extends EditRecord
{
    use EditRecord\Concerns\HasWizard;

    protected static string $resource = TravelRequestResource::class;

    public function form(Form $form): Form
    {
        // Forzar el uso del wizard en la página de edición con ancho completo
        return $form
            ->columns(1)
            ->schema([
                Wizard::make($this->getSteps())
                    ->submitAction(new \Illuminate\Support\HtmlString(''))
                    ->columnSpanFull()
                    ->extraAttributes([
                        'style' => 'width: 100%; max-width: none;',
                        'class' => 'w-full',
                    ])
                    ->live() // Hace que el wizard sea reactivo
                    ->afterStateUpdated(function () {
                        // Auto-guardar cambios cuando el usuario navega o modifica campos
                        $this->autoSaveDraft();
                    }),
            ]);
    }

    protected function autoSaveDraft(): void
    {
        try {
            if ($this->getRecord()->status === 'draft') {
                $data = $this->form->getRawState();

                // Filtrar campos vacíos para evitar sobrescribir con nulls
                $filteredData = array_filter($data, function ($value) {
                    return ! is_null($value) && $value !== '';
                });

                // Mantener campos que pueden ser null
                $allowedNullFields = ['notes', 'additional_services', 'per_diem_data', 'custom_expenses_data'];
                foreach ($allowedNullFields as $field) {
                    if (array_key_exists($field, $data)) {
                        $filteredData[$field] = $data[$field];
                    }
                }

                $this->getRecord()->update($filteredData);
            }
        } catch (\Exception $e) {
            // Log pero no mostrar error al usuario para no interrumpir la experiencia
            \Log::warning('Auto-save failed: '.$e->getMessage(), [
                'record_id' => $this->getRecord()->id,
                'user_id' => auth()->id(),
            ]);
        }
    }

    protected function getSteps(): array
    {
        $isDisabled = $this->getRecord()->status !== 'draft';

        return [
            Step::make('Información General')
                ->description('Datos principales del viaje')
                ->schema($this->getStepOneSchema($isDisabled)),
            Step::make('Servicios y Viáticos')
                ->description('Solicitudes de transporte, hospedaje y viáticos')
                ->schema($this->getStepTwoSchema($isDisabled)),
            Step::make('Resumen y Envío')
                ->description('Revisión final y total estimado')
                ->schema($this->getStepThreeSchema($isDisabled)),
        ];
    }

    // Reusing the same schema methods from CreateTravelRequest
    protected function getStepOneSchema($isDisabled = false): array
    {
        $schema = [];

        // Mensaje informativo si no es editable
        if ($isDisabled) {
            $schema[] = Placeholder::make('readonly_notice')
                ->content(new HtmlString('<div class="bg-blue-50 p-4 rounded-lg border border-blue-200"><div class="flex"><div class="flex-shrink-0"><svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg></div><div class="ml-3"><h3 class="text-sm font-medium text-blue-800">Solo Lectura</h3><p class="mt-1 text-sm text-blue-700">Esta solicitud ya no puede editarse porque su estado es: <strong>'.ucfirst($this->getRecord()->status).'</strong></p></div></div></div>'))
                ->columnSpanFull();
        }

        $schema = array_merge($schema, [
            Select::make('branch_id')
                ->relationship('branch', 'name')
                ->label('Centro de costos principal')
                ->required()
                ->disabled($isDisabled)
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
                    ->disabled($isDisabled)
                    ->required(),
                TextInput::make('origin_city')
                    ->label('Ciudad Origen')
                    ->disabled($isDisabled)
                    ->required(),
            ]),
            Grid::make(2)->schema([
                Select::make('destination_country_id')
                    ->relationship('destinationCountry', 'name')
                    ->label('País Destino')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->disabled($isDisabled)
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        $country = Country::find($state);
                        $set('request_type', $country?->is_foreign ? 'foreign' : 'domestic');
                    })
                    ->required(),
                TextInput::make('destination_city')
                    ->label('Ciudad Destino')
                    ->disabled($isDisabled)
                    ->required(),
            ]),
            Grid::make(2)->schema([
                DatePicker::make('departure_date')
                    ->label('Fecha Salida')
                    ->disabled($isDisabled)
                    ->required(),
                DatePicker::make('return_date')
                    ->label('Fecha Regreso')
                    ->disabled($isDisabled)
                    ->required(),
            ]),
            Textarea::make('notes')
                ->label('Notas / Justificación del Viaje')
                ->disabled($isDisabled)
                ->columnSpanFull(),
        ]);

        return $schema;
    }

    protected function getStepTwoSchema($isDisabled = false): array
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
                ->schema(function () use ($isDisabled) {
                    $managedServices = ExpenseConcept::where('is_unmanaged', false)->get();
                    $fields = [];
                    foreach ($managedServices as $service) {
                        $label = $service->description ? ($service->description.' — agrega tu preferencia') : 'Notas';
                        $fields[] = Grid::make(2)->schema([
                            Toggle::make("additional_services.{$service->id}.enabled")
                                ->label($service->name)
                                ->disabled($isDisabled)
                                ->live(),
                            Textarea::make("additional_services.{$service->id}.notes")
                                ->label($label)
                                ->disabled($isDisabled)
                                ->visible(fn (Get $get) => $get("additional_services.{$service->id}.enabled")),
                        ]);
                    }

                    return $fields;
                }),            Section::make('Viáticos Estándar')
                ->description('Viáticos aplicables según tu puesto y destino')
                ->schema(function (Get $get) use ($isDisabled) {
                    $user = auth()->user();
                    $requestType = $get('request_type');

                    // Si no hay request_type en el formulario, calcularlo desde el país de destino
                    if (! $requestType) {
                        $destinationCountryId = $get('destination_country_id');
                        if ($destinationCountryId) {
                            $country = Country::find($destinationCountryId);
                            $requestType = $country?->is_foreign ? 'foreign' : 'domestic';
                        }
                    }

                    // Si aún no hay request_type, intentar obtenerlo del registro existente
                    if (! $requestType && $this->getRecord()) {
                        $record = $this->getRecord();
                        if ($record->destinationCountry) {
                            $requestType = $record->destinationCountry->is_foreign ? 'foreign' : 'domestic';
                        }
                    }

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
                                ->disabled($isDisabled)
                                ->live(),
                            Placeholder::make("per_diem_data.{$perDiem->id}.total")
                                ->label('Total Estimado')
                                ->content(fn () => '$'.number_format($days * $perDiem->amount, 2)." ({$days} días)")
                                ->visible(fn (Get $get) => $get("per_diem_data.{$perDiem->id}.enabled")),
                            Textarea::make("per_diem_data.{$perDiem->id}.notes")
                                ->label('Notas')
                                ->disabled($isDisabled)
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
                        ->disabled($isDisabled)
                        ->schema([
                            TextInput::make('concept')->label('Concepto')->required(),
                            TextInput::make('amount')->label('Monto')->numeric()->prefix('$')->required(),
                            Textarea::make('justification')->label('Justificación')->columnSpanFull()->required(),
                        ])
                        ->addActionLabel('Agregar Gasto'),
                ]),
        ];
    }

    protected function getStepThreeSchema($isDisabled = false): array
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

                            // Obtener el folio real del registro actual
                            $currentRecord = $this->getRecord();
                            $folio = $currentRecord ? $currentRecord->folio : 'NUEVO-'.strtoupper(substr(uniqid(), -8));

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
                                                <div class="text-2xl font-bold text-blue-800 dark:text-blue-200 font-mono tracking-wider">'.$folio.'</div>
                                                <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">Solicitud existente</div>
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

    protected function getFormActions(): array
    {
        $record = $this->getRecord();

        // If the record is not available yet, return no actions.
        if (! $record) {
            return [];
        }

        // Solo mostrar acciones si el usuario puede editar la solicitud
        if (! $record->canBeEdited() || auth()->id() !== $record->user_id) {
            return [];
        }

        $actions = [];

        // Botón para enviar a autorización
        if ($record->canBeSubmitted() && $record->actual_authorizer) {
            $actions[] = Action::make('submitForAuthorization')
                ->label('Enviar a Autorización')
                ->color('primary')
                ->icon('heroicon-o-paper-airplane')
                ->action(function () {
                    try {
                        // Validar campos requeridos antes de enviar
                        $data = $this->form->getState();

                        $requiredFields = [
                            'branch_id', 'origin_country_id', 'origin_city',
                            'destination_country_id', 'destination_city',
                            'departure_date', 'return_date',
                        ];

                        foreach ($requiredFields as $field) {
                            if (empty($data[$field])) {
                                Notification::make()
                                    ->title('Campos Incompletos')
                                    ->body('Por favor completa todos los campos obligatorios antes de enviar.')
                                    ->warning()
                                    ->send();

                                return;
                            }
                        }

                        // Guardar cambios primero
                        $this->getRecord()->update($data);

                        // Enviar a autorización usando el método del modelo
                        $this->getRecord()->submitForAuthorization();

                        Notification::make()
                            ->title('Solicitud Enviada')
                            ->body('Tu solicitud ha sido enviada para autorización a '.$this->getRecord()->actual_authorizer->name)
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->getRecord()]));

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al Enviar')
                            ->body('Ocurrió un error al enviar la solicitud: '.$e->getMessage())
                            ->danger()
                            ->send();

                        \Log::error('Error submitting travel request: '.$e->getMessage(), [
                            'record_id' => $this->getRecord()->id,
                            'user_id' => auth()->id(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                });
        }

        return $actions;
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $actions = [];

        // Solo mostrar acciones de header si el usuario es el propietario
        if (auth()->id() === $record->user_id) {

            // Acción para eliminar (solo en draft y revision)
            if ($record->canBeEdited()) {
                $actions[] = Actions\DeleteAction::make()
                    ->label('Eliminar Solicitud');
            }

            // Acción para poner en revisión (solo si está rechazada)
            if ($record->canBeRevisedBy(auth()->user())) {
                $actions[] = Action::make('putInRevision')
                    ->label('Poner en Revisión')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square')
                    ->action(function () use ($record) {
                        $record->putInRevision();

                        Notification::make()
                            ->title('Solicitud en Revisión')
                            ->body('La solicitud ha sido puesta en revisión y puede ser editada.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $record]));
                    });
            }
        }

        return $actions;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
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
}
