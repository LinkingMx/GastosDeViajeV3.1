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
                ->schema($this->getStepOneSchema())
                ->completedIcon('heroicon-o-check')
                ->afterValidation(function () {
                    // Trigger wizard state update after validation
                    $this->dispatch('wizard-step-completed', step: 1);
                }),
            Step::make('Servicios y Viáticos')
                ->description('Solicitudes de transporte, hospedaje y viáticos')
                ->schema($this->getStepTwoSchema())
                ->completedIcon('heroicon-o-check')
                ->afterValidation(function () {
                    // Trigger wizard state update after validation
                    $this->dispatch('wizard-step-completed', step: 2);
                }),
            Step::make('Resumen y Envío')
                ->description('Revisión final y total estimado')
                ->schema($this->getStepThreeSchema())
                ->completedIcon('heroicon-o-check'),
        ];
    }

    protected function getStepOneSchema(): array
    {
        return [
            Select::make('branch_id')
                ->relationship('branch', 'name')
                ->label('Centro de costos principal')
                ->required()
                ->default(fn () => \App\Models\Branch::getDefault()?->id)
                ->disabled()
                ->dehydrated()
                ->validationMessages([
                    'required' => 'El centro de costos principal es obligatorio.',
                ])
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
                    ->required()
                    ->validationMessages([
                        'required' => 'El país de origen es obligatorio.',
                    ]),
                TextInput::make('origin_city')
                    ->label('Ciudad Origen')
                    ->required()
                    ->validationMessages([
                        'required' => 'La ciudad de origen es obligatoria.',
                        'min' => 'La ciudad de origen debe tener al menos 2 caracteres.',
                        'max' => 'La ciudad de origen no puede tener más de 100 caracteres.',
                    ])
                    ->minLength(2)
                    ->maxLength(100),
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
                    ->required()
                    ->validationMessages([
                        'required' => 'El país de destino es obligatorio.',
                    ]),
                TextInput::make('destination_city')
                    ->label('Ciudad Destino')
                    ->required()
                    ->validationMessages([
                        'required' => 'La ciudad de destino es obligatoria.',
                        'min' => 'La ciudad de destino debe tener al menos 2 caracteres.',
                        'max' => 'La ciudad de destino no puede tener más de 100 caracteres.',
                    ])
                    ->minLength(2)
                    ->maxLength(100),
            ]),
            Grid::make(2)->schema([
                DatePicker::make('departure_date')
                    ->label('Fecha Salida')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                        $returnDate = $get('return_date');
                        if ($state && $returnDate && $returnDate < $state) {
                            $set('return_date', null);
                        }
                    })
                    ->minDate(function () {
                        $config = \App\Models\GeneralSetting::get();
                        return now()->addDays($config->dias_minimos_anticipacion);
                    })
                    ->validationMessages([
                        'required' => 'La fecha de salida es obligatoria.',
                        'after' => function () {
                            $config = \App\Models\GeneralSetting::get();
                            return 'Las solicitudes deben hacerse con al menos '.$config->dias_minimos_anticipacion.' días de anticipación.';
                        },
                        'before' => 'La fecha de salida debe ser anterior a la fecha de regreso.',
                    ])
                    ->before('return_date'),
                DatePicker::make('return_date')
                    ->label('Fecha Regreso')
                    ->required()
                    ->minDate(fn (Get $get) => $get('departure_date'))
                    ->validationMessages([
                        'required' => 'La fecha de regreso es obligatoria.',
                        'after' => 'La fecha de regreso debe ser posterior a la fecha de salida.',
                    ])
                    ->after('departure_date'),
            ]),
            Textarea::make('notes')
                ->label('Notas / Justificación del Viaje')
                ->columnSpanFull()
                ->rows(3)
                ->maxLength(1000)
                ->validationMessages([
                    'max' => 'Las notas no pueden tener más de 1000 caracteres.',
                ])
                ->placeholder('Describe brevemente el propósito del viaje y cualquier información relevante.'),

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
                                ->visible(fn (Get $get) => $get("additional_services.{$service->id}.enabled"))
                                ->maxLength(300)
                                ->validationMessages([
                                    'max' => 'Las notas no pueden tener más de 300 caracteres.',
                                ])
                                ->rows(3)
                                ->placeholder('Especifica detalles o preferencias para este servicio.'),
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
                                ->visible(fn (Get $get) => $get("per_diem_data.{$perDiem->id}.enabled"))
                                ->maxLength(200)
                                ->validationMessages([
                                    'max' => 'Las notas no pueden tener más de 200 caracteres.',
                                ])
                                ->rows(2)
                                ->placeholder('Notas adicionales para este viático (opcional).'),
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
                        ->defaultItems(0)
                        ->schema([
                            TextInput::make('concept')
                                ->label('Concepto')
                                ->required()
                                ->validationMessages([
                                    'required' => 'El concepto del gasto es obligatorio.',
                                    'min' => 'El concepto debe tener al menos 3 caracteres.',
                                    'max' => 'El concepto no puede tener más de 100 caracteres.',
                                ])
                                ->minLength(3)
                                ->maxLength(100)
                                ->placeholder('Ej: Transporte local, Materiales, etc.'),
                            TextInput::make('amount')
                                ->label('Monto')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->validationMessages([
                                    'required' => 'El monto del gasto es obligatorio.',
                                    'min' => 'El monto debe ser mayor a $0.01.',
                                    'max' => 'El monto no puede ser mayor a $999,999.99.',
                                ])
                                ->minValue(0.01)
                                ->maxValue(999999.99)
                                ->step(0.01)
                                ->placeholder('0.00'),
                            Textarea::make('justification')
                                ->label('Justificación')
                                ->columnSpanFull()
                                ->required()
                                ->validationMessages([
                                    'required' => 'La justificación del gasto es obligatoria.',
                                    'min' => 'La justificación debe tener al menos 10 caracteres.',
                                    'max' => 'La justificación no puede tener más de 500 caracteres.',
                                ])
                                ->minLength(10)
                                ->maxLength(500)
                                ->rows(2)
                                ->placeholder('Explica por qué es necesario este gasto para el viaje.'),
                        ])
                        ->addActionLabel('Agregar Gasto'),
                ]),
        ];
    }

protected function getStepThreeSchema(): array
{
    return [
        // Folio Preview
        Placeholder::make('folio_preview')
            ->label('Folio de Solicitud')
            ->content(function () {
                $tempFolio = 'PREV-'.strtoupper(substr(uniqid(), -8));
                return new HtmlString("
                    <div class='text-center py-4'>
                        <div class='inline-block px-6 py-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg border-2 border-primary-200 dark:border-primary-800'>
                            <div class='text-2xl font-bold text-primary-600 dark:text-primary-400 font-mono tracking-wider'>{$tempFolio}</div>
                            <div class='text-xs text-gray-500 dark:text-gray-400 mt-1'>(Se generará automáticamente al guardar)</div>
                        </div>
                    </div>
                ");
            })
            ->columnSpanFull(),

        // Total General Destacado
        Placeholder::make('grand_total')
            ->label('')
            ->content(function (Get $get) {
                $user = auth()->user();
                $requestType = $get('request_type');
                $departureDate = $get('departure_date');
                $returnDate = $get('return_date');
                $destinationCountryId = $get('destination_country_id');

                if (! $requestType && $destinationCountryId) {
                    $destCountryObj = Country::find($destinationCountryId);
                    $requestType = $destCountryObj && $destCountryObj->is_foreign ? 'foreign' : 'domestic';
                }

                // Calcular días
                $totalDays = 0;
                if ($departureDate && $returnDate) {
                    $departure = Carbon::parse($departureDate)->startOfDay();
                    $return = Carbon::parse($returnDate)->startOfDay();
                    $totalDays = max(1, $departure->diffInDays($return) + 1);
                }

                // Calcular total de viáticos
                $perDiemTotal = 0;
                if ($requestType && $totalDays > 0) {
                    $perDiems = PerDiem::where('position_id', $user->position_id)
                        ->where('scope', $requestType)
                        ->get();

                    foreach ($perDiems as $perDiem) {
                        if ($get("per_diem_data.{$perDiem->id}.enabled")) {
                            $perDiemTotal += $totalDays * $perDiem->amount;
                        }
                    }
                }

                // Calcular gastos personalizados
                $customExpensesTotal = 0;
                $customExpensesData = $get('custom_expenses_data') ?: [];
                foreach ($customExpensesData as $expense) {
                    if (! empty($expense['amount'])) {
                        $customExpensesTotal += floatval($expense['amount']);
                    }
                }

                $grandTotal = $perDiemTotal + $customExpensesTotal;

                return new HtmlString("
                    <div class='bg-gradient-to-r from-success-50 to-primary-50 dark:from-success-900/20 dark:to-primary-900/20 border border-success-200 dark:border-success-800 p-6 rounded-lg'>
                        <h3 class='text-xl font-bold text-gray-900 dark:text-white mb-4 text-center'>Solicitud de Anticipo de Gasto</h3>
                        <div class='grid grid-cols-1 sm:grid-cols-3 gap-4'>
                            <div class='text-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg'>
                                <div class='text-2xl font-bold text-warning-600 dark:text-warning-400'>$".number_format($perDiemTotal, 2)."</div>
                                <div class='text-sm text-gray-600 dark:text-gray-400 mt-1'>Viáticos Estándar</div>
                            </div>
                            <div class='text-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg'>
                                <div class='text-2xl font-bold text-danger-600 dark:text-danger-400'>$".number_format($customExpensesTotal, 2)."</div>
                                <div class='text-sm text-gray-600 dark:text-gray-400 mt-1'>Gastos Personalizados</div>
                            </div>
                            <div class='text-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border-2 border-success-300 dark:border-success-700'>
                                <div class='text-3xl font-bold text-success-600 dark:text-success-400'>$".number_format($grandTotal, 2)."</div>
                                <div class='text-sm text-gray-600 dark:text-gray-400 mt-1 font-semibold'>Total General</div>
                            </div>
                        </div>
                        <p class='text-center text-xs text-gray-500 dark:text-gray-400 mt-4'>
                            * Los servicios administrados no tienen costo asociado en este resumen ya que son gestionados directamente por la empresa.
                        </p>
                    </div>
                ");
            })
            ->columnSpanFull(),

        // Información del Solicitante
        Section::make('Información del Solicitante')
            ->icon('heroicon-o-user')
            ->schema([
                Grid::make(['default' => 1, 'sm' => 2])
                    ->schema([
                        Placeholder::make('requester')
                            ->label('Solicitante')
                            ->content(fn () => auth()->user()->name),
                        Placeholder::make('branch')
                            ->label('Centro de costo')
                            ->content(function (Get $get) {
                                $branchId = $get('branch_id');
                                return $branchId ? \App\Models\Branch::find($branchId)?->name : 'No asignado';
                            }),
                    ]),
            ])
            ->collapsible(),

        // Detalles del Viaje
        Section::make('Detalles del Viaje')
            ->icon('heroicon-o-map-pin')
            ->schema([
                Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])
                    ->schema([
                        Placeholder::make('origin')
                            ->label('Origen')
                            ->content(function (Get $get) {
                                $city = $get('origin_city');
                                $countryId = $get('origin_country_id');
                                $country = $countryId ? Country::find($countryId)?->name : '';
                                return "{$city}, {$country}";
                            }),
                        Placeholder::make('destination')
                            ->label('Destino')
                            ->content(function (Get $get) {
                                $city = $get('destination_city');
                                $countryId = $get('destination_country_id');
                                $country = $countryId ? Country::find($countryId)?->name : '';
                                return "{$city}, {$country}";
                            }),
                        Placeholder::make('departure')
                            ->label('Fecha de Salida')
                            ->content(function (Get $get) {
                                $date = $get('departure_date');
                                return $date ? Carbon::parse($date)->format('d/m/Y') : '';
                            }),
                        Placeholder::make('return')
                            ->label('Fecha de Regreso')
                            ->content(function (Get $get) {
                                $date = $get('return_date');
                                return $date ? Carbon::parse($date)->format('d/m/Y') : '';
                            }),
                    ]),
                Placeholder::make('duration')
                    ->label('Duración del Viaje')
                    ->content(function (Get $get) {
                        $departureDate = $get('departure_date');
                        $returnDate = $get('return_date');
                        if ($departureDate && $returnDate) {
                            $departure = Carbon::parse($departureDate)->startOfDay();
                            $return = Carbon::parse($returnDate)->startOfDay();
                            $totalDays = max(1, $departure->diffInDays($return) + 1);
                            return $totalDays.($totalDays == 1 ? ' día' : ' días');
                        }
                        return 'No calculado';
                    })
                    ->columnSpanFull(),
            ])
            ->collapsible(),

        // Notas
        Section::make('Notas y Justificación')
            ->icon('heroicon-o-document-text')
            ->schema([
                Placeholder::make('notes_content')
                    ->label('')
                    ->content(fn (Get $get) => $get('notes') ?: 'Sin notas')
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->visible(fn (Get $get) => ! empty($get('notes'))),

        // Servicios Administrados
        Section::make('Servicios Administrados Solicitados')
            ->icon('heroicon-o-check-circle')
            ->schema([
                Placeholder::make('services_list')
                    ->label('')
                    ->content(function (Get $get) {
                        $managedServices = ExpenseConcept::where('is_unmanaged', false)->get();
                        $requestedServices = [];
                        foreach ($managedServices as $service) {
                            if ($get("additional_services.{$service->id}.enabled")) {
                                $notes = $get("additional_services.{$service->id}.notes") ?: 'Sin notas específicas';
                                $requestedServices[] = "<div class='mb-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg'><strong class='text-primary-600 dark:text-primary-400'>{$service->name}</strong><div class='text-sm text-gray-600 dark:text-gray-400 mt-1'>{$notes}</div></div>";
                            }
                        }
                        return new HtmlString(count($requestedServices) > 0 ? implode('', $requestedServices) : '<p class="text-gray-500">No se solicitaron servicios administrados</p>');
                    })
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->visible(function (Get $get) {
                $managedServices = ExpenseConcept::where('is_unmanaged', false)->get();
                foreach ($managedServices as $service) {
                    if ($get("additional_services.{$service->id}.enabled")) {
                        return true;
                    }
                }
                return false;
            }),

        // Viáticos Estándar
        Section::make('Viáticos Estándar')
            ->icon('heroicon-o-banknotes')
            ->schema([
                Placeholder::make('per_diems_table')
                    ->label('')
                    ->content(function (Get $get) {
                        $user = auth()->user();
                        $requestType = $get('request_type');
                        $departureDate = $get('departure_date');
                        $returnDate = $get('return_date');
                        $destinationCountryId = $get('destination_country_id');

                        if (! $requestType && $destinationCountryId) {
                            $destCountryObj = Country::find($destinationCountryId);
                            $requestType = $destCountryObj && $destCountryObj->is_foreign ? 'foreign' : 'domestic';
                        }

                        $totalDays = 0;
                        if ($departureDate && $returnDate) {
                            $departure = Carbon::parse($departureDate)->startOfDay();
                            $return = Carbon::parse($returnDate)->startOfDay();
                            $totalDays = max(1, $departure->diffInDays($return) + 1);
                        }

                        if ($requestType && $totalDays > 0) {
                            $perDiems = PerDiem::with(['detail.concept'])
                                ->where('position_id', $user->position_id)
                                ->where('scope', $requestType)
                                ->get();

                            $rows = '';
                            $total = 0;
                            foreach ($perDiems as $perDiem) {
                                if ($get("per_diem_data.{$perDiem->id}.enabled")) {
                                    $amount = $totalDays * $perDiem->amount;
                                    $total += $amount;
                                    $name = $perDiem->detail?->name ?? 'Viático';
                                    $concept = $perDiem->detail?->concept?->name ?? '';
                                    $notes = $get("per_diem_data.{$perDiem->id}.notes") ?: 'Sin notas';

                                    $rows .= "
                                        <div class='mb-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700'>
                                            <div class='flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2'>
                                                <div class='flex-1'>
                                                    <div class='font-semibold text-gray-900 dark:text-white'>{$name}</div>
                                                    ".($concept ? "<div class='text-sm text-gray-500 dark:text-gray-400'>({$concept})</div>" : '')."
                                                    <div class='text-xs text-gray-500 dark:text-gray-400 mt-1'>{$notes}</div>
                                                </div>
                                                <div class='flex flex-row sm:flex-col gap-4 sm:gap-1 sm:text-right'>
                                                    <div class='text-sm text-gray-600 dark:text-gray-400'>$".number_format($perDiem->amount, 2)." × {$totalDays} días</div>
                                                    <div class='text-lg font-bold text-success-600 dark:text-success-400'>$".number_format($amount, 2)."</div>
                                                </div>
                                            </div>
                                        </div>
                                    ";
                                }
                            }

                            if ($rows) {
                                $rows .= "
                                    <div class='mt-4 p-4 bg-success-50 dark:bg-success-900/20 rounded-lg border-2 border-success-200 dark:border-success-800'>
                                        <div class='flex justify-between items-center'>
                                            <span class='font-semibold text-gray-900 dark:text-white'>Subtotal Viáticos:</span>
                                            <span class='text-xl font-bold text-success-600 dark:text-success-400'>$".number_format($total, 2)."</span>
                                        </div>
                                    </div>
                                ";
                                return new HtmlString($rows);
                            }
                        }

                        return new HtmlString('<p class="text-gray-500">No hay viáticos seleccionados</p>');
                    })
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->visible(function (Get $get) {
                $user = auth()->user();
                $requestType = $get('request_type');
                $destinationCountryId = $get('destination_country_id');

                if (! $requestType && $destinationCountryId) {
                    $destCountryObj = Country::find($destinationCountryId);
                    $requestType = $destCountryObj && $destCountryObj->is_foreign ? 'foreign' : 'domestic';
                }

                if ($requestType) {
                    $perDiems = PerDiem::where('position_id', $user->position_id)
                        ->where('scope', $requestType)
                        ->get();

                    foreach ($perDiems as $perDiem) {
                        if ($get("per_diem_data.{$perDiem->id}.enabled")) {
                            return true;
                        }
                    }
                }
                return false;
            }),

        // Gastos Personalizados
        Section::make('Gastos Personalizados')
            ->icon('heroicon-o-currency-dollar')
            ->schema([
                Placeholder::make('custom_expenses_table')
                    ->label('')
                    ->content(function (Get $get) {
                        $customExpensesData = $get('custom_expenses_data') ?: [];

                        if (count($customExpensesData) > 0) {
                            $rows = '';
                            $total = 0;

                            foreach ($customExpensesData as $expense) {
                                if (! empty($expense['amount'])) {
                                    $amount = floatval($expense['amount']);
                                    $total += $amount;
                                    $concept = $expense['concept'] ?? 'Sin concepto';
                                    $justification = $expense['justification'] ?? 'Sin justificación';

                                    $rows .= "
                                        <div class='mb-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700'>
                                            <div class='flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2'>
                                                <div class='flex-1'>
                                                    <div class='font-semibold text-gray-900 dark:text-white'>{$concept}</div>
                                                    <div class='text-sm text-gray-600 dark:text-gray-400 mt-1'>{$justification}</div>
                                                </div>
                                                <div class='text-lg font-bold text-danger-600 dark:text-danger-400 sm:text-right'>
                                                    $".number_format($amount, 2)."
                                                </div>
                                            </div>
                                        </div>
                                    ";
                                }
                            }

                            if ($rows) {
                                $rows .= "
                                    <div class='mt-4 p-4 bg-danger-50 dark:bg-danger-900/20 rounded-lg border-2 border-danger-200 dark:border-danger-800'>
                                        <div class='flex justify-between items-center'>
                                            <span class='font-semibold text-gray-900 dark:text-white'>Subtotal Gastos Personalizados:</span>
                                            <span class='text-xl font-bold text-danger-600 dark:text-danger-400'>$".number_format($total, 2)."</span>
                                        </div>
                                    </div>
                                ";
                                return new HtmlString($rows);
                            }
                        }

                        return new HtmlString('<p class="text-gray-500">No hay gastos personalizados</p>');
                    })
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->visible(fn (Get $get) => count($get('custom_expenses_data') ?: []) > 0),
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

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        
        // Determinar el autorizador basado en la configuración del usuario
        $authorizer = $record->actual_authorizer;
        if ($authorizer) {
            // Use updateQuietly to avoid triggering events that might send duplicate emails
            $record->updateQuietly(['authorizer_id' => $authorizer->id]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    public bool $isSubmittingAndContinuing = false;

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
        // No form actions needed - using wizard buttons
        return [];
    }

    protected function canCreateRecord(): bool
    {
        $currentStep = $this->getCurrentStep();
        
        // Only allow creation on the final step (step 3)
        if ($currentStep < 2) {
            return false;
        }

        // Validate that required fields from all steps are filled
        $data = $this->form->getState();
        
        // Step 1 validation
        if (empty($data['branch_id']) || 
            empty($data['origin_country_id']) || 
            empty($data['origin_city']) ||
            empty($data['destination_country_id']) || 
            empty($data['destination_city']) ||
            empty($data['departure_date']) || 
            empty($data['return_date'])) {
            return false;
        }

        return true;
    }

    protected function getCurrentStep(): int
    {
        return $this->form->getComponent('wizard')?->getCurrentStep() ?? 0;
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Crear Solicitud')
            ->action(function () {
                $this->isSubmittingAndContinuing = false;
                $this->create();
            });
    }

    protected function getCreateAndSubmitFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('createAndSubmit')
            ->label('Crear y Continuar')
            ->action(function () {
                $this->isSubmittingAndContinuing = true;
                $this->create();

                // Redirect to edit page for "create and continue" functionality
                return redirect(self::$resource::getUrl('edit', ['record' => $this->getRecord()]));
            })
            ->color('primary')
            ->icon('heroicon-o-arrow-right-circle');
    }
}
