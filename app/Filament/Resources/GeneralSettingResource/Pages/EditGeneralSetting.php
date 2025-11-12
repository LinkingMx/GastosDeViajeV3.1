<?php

namespace App\Filament\Resources\GeneralSettingResource\Pages;

use App\Filament\Resources\GeneralSettingResource;
use App\Models\GeneralSetting;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class EditGeneralSetting extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = GeneralSettingResource::class;

    protected static string $view = 'filament.resources.general-setting-resource.pages.edit-general-setting';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(GeneralSetting::get()->toArray());
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('Configuración de Viajes')
                    ->description('Configura los parámetros generales para las solicitudes de viaje')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('dias_minimos_anticipacion')
                            ->label('Días Mínimos de Anticipación')
                            ->prefixIcon('heroicon-o-calendar')
                            ->placeholder('5')
                            ->helperText('Número de días mínimos de anticipación requeridos para crear una solicitud de viaje')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(30)
                            ->default(5),
                    ])
                    ->columns(1),

                \Filament\Forms\Components\Section::make('Configuración de Comprobaciones de Gastos')
                    ->description('Configura los usuarios y parámetros para el proceso de comprobación de gastos')
                    ->schema([
                        \Filament\Forms\Components\Select::make('autorizador_mayor_id')
                            ->label('Autorizador Mayor')
                            ->prefixIcon('heroicon-o-user-circle')
                            ->placeholder('Selecciona un usuario')
                            ->helperText('Usuario responsable de aprobar comprobaciones que excedan el monto límite estándar')
                            ->relationship('autorizadorMayor', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = GeneralSetting::get();
        $setting->update($data);

        Notification::make()
            ->success()
            ->icon('heroicon-o-check-circle')
            ->iconColor('primary')
            ->title('Configuración Actualizada')
            ->body('Los cambios han sido guardados correctamente.')
            ->send();
    }
}
