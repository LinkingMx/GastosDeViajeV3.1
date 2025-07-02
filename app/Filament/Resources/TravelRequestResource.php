<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TravelRequestResource\Pages;
use App\Models\TravelRequest;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TravelRequestResource extends Resource
{
    protected static ?string $model = TravelRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = null; // Sin grupo para que aparezca al inicio

    protected static ?string $navigationLabel = 'Solicitudes de Viaje'; // Label específico

    protected static ?string $modelLabel = 'Solicitud de Viaje';

    protected static ?string $pluralModelLabel = 'Solicitudes de Viaje';

    protected static ?int $navigationSort = 0; // Orden 0 para ser el primero

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Schema will be defined in Create/Edit pages
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('folio')
                    ->label('Folio')
                    ->searchable(['uuid'])
                    ->copyable()
                    ->tooltip(fn ($record): string => $record->uuid ?? 'UUID no disponible'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('destination_city')
                    ->label('Destino')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Fecha Salida')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_date')
                    ->label('Fecha Regreso')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('actualAuthorizer.name')
                    ->label('Autorizador')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin autorizador'),
                Tables\Columns\TextColumn::make('status_display')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($record) => match ($record ? $record->status : null) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'revision' => 'info',
                        'travel_review' => 'info',
                        'travel_approved' => 'success',
                        'travel_rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('travel_status')
                    ->label('Estado Viajes')
                    ->getStateUsing(function ($record) {
                        if (! $record) {
                            return null;
                        }

                        return match ($record->status) {
                            'draft', 'pending', 'rejected', 'revision' => null,
                            'approved' => 'Pendiente',
                            'travel_review' => 'En Revisión',
                            'travel_approved' => $record->travel_review_comments ? 'Aprobada*' : 'Aprobada',
                            'travel_rejected' => 'Rechazada',
                            default => null,
                        };
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (! $record) {
                            return 'gray';
                        }

                        return match ($record->status) {
                            'approved' => 'warning',
                            'travel_review' => 'info',
                            'travel_approved' => 'success',
                            'travel_rejected' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->icon(function ($record) {
                        if (! $record) {
                            return null;
                        }

                        // Mostrar icono si hubo cambios (travel_review_comments existe)
                        if ($record->status === 'travel_approved' && $record->travel_review_comments) {
                            return 'heroicon-o-pencil-square';
                        }

                        return null;
                    })
                    ->tooltip(function ($record) {
                        if (! $record || ! $record->travel_review_comments) {
                            return null;
                        }

                        return 'Revisado por: '.($record->travelReviewer?->name ?? 'Equipo de Viajes').
                               "\nComentarios: ".$record->travel_review_comments;
                    })
                    ->visible(fn ($record) => $record && in_array($record->status, ['approved', 'travel_review', 'travel_approved', 'travel_rejected'])),
                Tables\Columns\TextColumn::make('attachments_count')
                    ->label('Archivos')
                    ->counts('attachments')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-paper-clip')
                    ->visible(fn ($record) => $record && $record->status === 'travel_approved')
                    ->tooltip('Archivos adjuntos subidos por el equipo de viajes'),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Enviada')
                    ->date('d/m/Y H:i')
                    ->placeholder('No enviada')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'pending' => 'Pendiente',
                        'approved' => 'Autorizada',
                        'rejected' => 'Rechazada',
                        'revision' => 'En Revisión',
                        'travel_review' => 'En Revisión de Viajes',
                        'travel_approved' => 'Aprobada Final',
                        'travel_rejected' => 'Rechazada por Viajes',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record && $record->canBeEdited() && auth()->id() === $record->user_id),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record && $record->canBeAuthorizedBy(auth()->user()))
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comment')
                            ->label('Comentarios (opcional)')
                            ->placeholder('Agregar comentarios sobre la aprobación...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->approve($data['comment'] ?? null);
                        \Filament\Notifications\Notification::make()
                            ->title('Solicitud Aprobada')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record && $record->canBeAuthorizedBy(auth()->user()))
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comment')
                            ->label('Motivo del rechazo')
                            ->placeholder('Explica el motivo del rechazo...')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject($data['comment']);
                        \Filament\Notifications\Notification::make()
                            ->title('Solicitud Rechazada')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('put_in_revision')
                    ->label('Revisar')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(fn ($record) => $record && $record->canBeRevisedBy(auth()->user()))
                    ->action(function ($record) {
                        $record->putInRevision();
                        \Filament\Notifications\Notification::make()
                            ->title('Solicitud puesta en revisión')
                            ->success()
                            ->send();
                    }),

                // ===============================================
                // ACCIONES DEL EQUIPO DE VIAJES
                // ===============================================
                Tables\Actions\Action::make('travel_approve')
                    ->label('Aprobar Viajes')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record && $record->canBeTravelReviewedBy(auth()->user()))
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comment')
                            ->label('Comentarios (opcional)')
                            ->placeholder('Agregar comentarios sobre la aprobación del equipo de viajes...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->travelApprove(auth()->user(), $data['comment'] ?? null);
                        \Filament\Notifications\Notification::make()
                            ->title('Solicitud Aprobada por Equipo de Viajes')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('travel_reject')
                    ->label('Rechazar Viajes')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record && $record->canBeTravelReviewedBy(auth()->user()))
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Motivo del rechazo')
                            ->placeholder('Explica el motivo del rechazo por parte del equipo de viajes...')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->travelReject(auth()->user(), $data['reason']);
                        \Filament\Notifications\Notification::make()
                            ->title('Solicitud Rechazada por Equipo de Viajes')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('travel_edit_expenses')
                    ->label('Editar Gastos')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->visible(fn ($record) => $record && $record->canBeTravelReviewedBy(auth()->user()) && ! empty($record->custom_expenses_data))
                    ->form([
                        \Filament\Forms\Components\Repeater::make('custom_expenses')
                            ->label('Gastos Especiales')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('concept')
                                    ->label('Concepto')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('amount')
                                    ->label('Monto')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required(),
                                \Filament\Forms\Components\Textarea::make('justification')
                                    ->label('Justificación')
                                    ->required(),
                            ])
                            ->minItems(1)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable(),
                        \Filament\Forms\Components\Textarea::make('comment')
                            ->label('Comentarios de la revisión')
                            ->placeholder('Explica los cambios realizados...')
                            ->required(),
                    ])
                    ->fillForm(function ($record) {
                        return [
                            'custom_expenses' => $record->custom_expenses_data ?? [],
                            'comment' => '',
                        ];
                    })
                    ->action(function ($record, array $data) {
                        $record->travelEditAndApprove(auth()->user(), $data['custom_expenses'], $data['comment']);
                        \Filament\Notifications\Notification::make()
                            ->title('Gastos Editados y Solicitud Aprobada')
                            ->success()
                            ->send();
                    }),

                // ===============================================
                // ACCIÓN PARA ARCHIVOS ADJUNTOS
                // ===============================================
                Tables\Actions\Action::make('upload_attachments')
                    ->label('Subir Archivos')
                    ->icon('heroicon-o-paper-clip')
                    ->color('primary')
                    ->visible(fn ($record) => $record && $record->canUploadAttachments(auth()->user()))
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('attachment')
                            ->label('Archivo')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB máximo
                            ->directory('travel-attachments')
                            ->disk('public')
                            ->preserveFilenames()
                            ->openable()
                            ->downloadable(),
                        \Filament\Forms\Components\Select::make('attachment_type_id')
                            ->label('Tipo de Documento')
                            ->required()
                            ->options(\App\Models\AttachmentType::getSelectOptions())
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\Textarea::make('description')
                            ->label('Descripción (opcional)')
                            ->placeholder('Agrega una descripción del documento...')
                            ->rows(3),
                    ])->action(function ($record, array $data) {
                        try {
                            // El archivo ya está almacenado por Filament
                            $filePath = $data['attachment'];

                            // Obtener información del archivo almacenado
                            $fullPath = storage_path('app/public/'.$filePath);

                            // Verificar que el archivo existe
                            if (! file_exists($fullPath)) {
                                throw new \Exception('Archivo no encontrado después de la subida');
                            }

                            $originalName = basename($filePath);
                            $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
                            $fileSize = filesize($fullPath);

                            // Crear el registro del attachment
                            $record->attachments()->create([
                                'uploaded_by' => auth()->id(),
                                'file_name' => $originalName,
                                'file_path' => $filePath,
                                'file_type' => $mimeType,
                                'file_size' => $fileSize,
                                'attachment_type_id' => $data['attachment_type_id'],
                                'description' => $data['description'] ?? null,
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Archivo adjuntado exitosamente')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error al adjuntar archivo')
                                ->body('Ocurrió un error al procesar el archivo: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('manage_attachments')
                    ->label('Ver Archivos')
                    ->icon('heroicon-o-folder')
                    ->color('gray')
                    ->visible(fn ($record) => $record && $record->status === 'travel_approved' && $record->attachments()->count() > 0)
                    ->modalHeading(fn ($record) => 'Archivos Adjuntos - '.$record->folio)
                    ->modalWidth('4xl')
                    ->modalContent(function ($record) {
                        $attachments = $record->attachments()->with('uploader')->get();
                        $content = '<div class="space-y-4">';

                        foreach ($attachments as $attachment) {
                            $typeLabel = $attachment->attachmentType?->name ?? 'Documento';

                            $fileSize = $attachment->file_size;
                            if ($fileSize >= 1048576) {
                                $formattedSize = number_format($fileSize / 1048576, 2).' MB';
                            } elseif ($fileSize >= 1024) {
                                $formattedSize = number_format($fileSize / 1024, 2).' KB';
                            } else {
                                $formattedSize = $fileSize.' bytes';
                            }

                            $downloadUrl = \Storage::url($attachment->file_path);

                            $content .= '
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">'.htmlspecialchars($attachment->file_name).'</h4>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200 mr-2">
                                                '.$typeLabel.'
                                            </span>
                                            '.$formattedSize.' • Subido por '.htmlspecialchars($attachment->uploader->name).' • '.$attachment->created_at->format('d/m/Y H:i').'
                                        </div>';

                            if ($attachment->description) {
                                $content .= '<p class="text-sm text-gray-600 dark:text-gray-300 mt-2">'.htmlspecialchars($attachment->description).'</p>';
                            }

                            $content .= '
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="'.$downloadUrl.'" 
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-700">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Descargar
                                        </a>
                                    </div>
                                </div>
                            </div>';
                        }

                        $content .= '</div>';

                        return new \Illuminate\Support\HtmlString($content);
                    })
                    ->modalActions([
                        \Filament\Actions\Action::make('close')
                            ->label('Cerrar')
                            ->color('gray')
                            ->close(),
                    ]),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->can('delete_travel_requests')),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTravelRequests::route('/'),
            'create' => Pages\CreateTravelRequest::route('/create'),
            'view' => Pages\ViewTravelRequest::route('/{record}'),
            'edit' => Pages\EditTravelRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Los super_admin pueden ver todo
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Para otros usuarios, mostrar solicitudes visibles según su rol:
        return $query->where(function (Builder $query) use ($user) {
            $query->where('user_id', $user->id) // Mis propias solicitudes
                ->orWhere(function (Builder $query) use ($user) {
                    // Solicitudes pendientes que puedo autorizar (departamental)
                    $query->where('status', 'pending')
                        ->where(function (Builder $query) use ($user) {
                            $query->whereHas('user', function (Builder $query) use ($user) {
                                // Si el usuario tiene override_authorizer_id, esas solicitudes van a él
                                $query->where('override_authorizer_id', $user->id);
                            })
                                ->orWhereHas('user.department', function (Builder $query) use ($user) {
                                    // Si no tiene override, verificar si el usuario es autorizador del departamento
                                    $query->where('authorizer_id', $user->id);
                                });
                        });
                })
                ->orWhere(function (Builder $query) use ($user) {
                    // NUEVO: Solicitudes en revisión de viajes para miembros del equipo de viajes
                    if ($user->isTravelTeamMember()) {
                        $query->where('status', 'travel_review');
                    }
                })
                ->orWhere(function (Builder $query) use ($user) {
                    // NUEVO: Solicitudes aprobadas/rechazadas por el equipo de viajes (para seguimiento)
                    if ($user->isTravelTeamMember()) {
                        $query->whereIn('status', ['travel_approved', 'travel_rejected']);
                    }
                });
        });
    }
}
