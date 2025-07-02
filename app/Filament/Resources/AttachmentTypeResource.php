<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttachmentTypeResource\Pages;
use App\Models\AttachmentType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AttachmentTypeResource extends Resource
{
    protected static ?string $model = AttachmentType::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Tipos de Documentos';

    protected static ?string $modelLabel = 'Tipo de Documento';

    protected static ?string $pluralModelLabel = 'Tipos de Documentos';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej. Reserva de Hotel'),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(255)
                            ->helperText('Se genera automáticamente si se deja vacío')
                            ->placeholder('ej. reserva-hotel'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->placeholder('Describe para qué se usa este tipo de documento')
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Configuración Visual')
                    ->schema([
                        Forms\Components\TextInput::make('icon')
                            ->label('Ícono (Heroicon)')
                            ->placeholder('ej. heroicon-o-home')
                            ->helperText('Nombre del ícono de Heroicons'),

                        Forms\Components\Select::make('color')
                            ->label('Color')
                            ->options([
                                'gray' => 'Gris',
                                'red' => 'Rojo',
                                'orange' => 'Naranja',
                                'amber' => 'Ámbar',
                                'yellow' => 'Amarillo',
                                'lime' => 'Lima',
                                'green' => 'Verde',
                                'emerald' => 'Esmeralda',
                                'teal' => 'Verde azulado',
                                'cyan' => 'Cian',
                                'sky' => 'Cielo',
                                'blue' => 'Azul',
                                'indigo' => 'Índigo',
                                'violet' => 'Violeta',
                                'purple' => 'Púrpura',
                                'fuchsia' => 'Fucsia',
                                'pink' => 'Rosa',
                                'rose' => 'Rosa claro',
                            ])
                            ->default('blue'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->helperText('Orden de aparición (menor número = aparece primero)'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Si está desactivado, no aparecerá en los formularios'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable()
                    ->tooltip('Haz clic para copiar'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(function (AttachmentType $record): ?string {
                        return $record->description;
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),

                Tables\Columns\TextColumn::make('travelRequestAttachments_count')
                    ->label('Archivos')
                    ->counts('travelRequestAttachments')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (AttachmentType $record) {
                        // Verificar si tiene archivos adjuntos
                        if ($record->travelRequestAttachments()->count() > 0) {
                            throw new \Exception('No se puede eliminar este tipo de documento porque tiene archivos adjuntos asociados.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->travelRequestAttachments()->count() > 0) {
                                    throw new \Exception("No se puede eliminar el tipo '{$record->name}' porque tiene archivos adjuntos asociados.");
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttachmentTypes::route('/'),
            'create' => Pages\CreateAttachmentType::route('/create'),
            'edit' => Pages\EditAttachmentType::route('/{record}/edit'),
        ];
    }
}
