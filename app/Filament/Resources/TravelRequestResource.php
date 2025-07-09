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
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-hashtag'),

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
                            'travel_approved' => 'Aprobada',
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
                        return null;
                    })
                    ->tooltip(function ($record) {
                        return null;
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
                Tables\Columns\IconColumn::make('advance_deposit_made')
                    ->label('Anticipo')
                    ->boolean()
                    ->trueIcon('heroicon-o-banknotes')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(function ($record) {
                        if (! $record || ! $record->advance_deposit_made) {
                            return 'Anticipo no depositado';
                        }
                        $tooltip = 'Anticipo depositado';
                        if ($record->advance_deposit_amount) {
                            $tooltip .= ' - $'.number_format($record->advance_deposit_amount, 2);
                        }
                        if ($record->advanceDepositMadeByUser) {
                            $tooltip .= ' por '.$record->advanceDepositMadeByUser->name;
                        }
                        if ($record->advance_deposit_made_at) {
                            $tooltip .= ' el '.$record->advance_deposit_made_at->format('d/m/Y H:i');
                        }

                        // Verificar si hay comprobante
                        $depositAttachmentType = \App\Models\AttachmentType::where('slug', 'advance_deposit_receipt')->first();
                        if ($depositAttachmentType) {
                            $hasReceipt = $record->attachments()
                                ->where('attachment_type_id', $depositAttachmentType->id)
                                ->exists();

                            if ($hasReceipt) {
                                $receipt = $record->attachments()
                                    ->where('attachment_type_id', $depositAttachmentType->id)
                                    ->latest()
                                    ->first();
                                $tooltip .= "\n📄 Comprobante subido el ".$receipt->created_at->format('d/m/Y H:i');
                            } else {
                                $tooltip .= "\n⚠️ Comprobante pendiente";
                            }
                        }

                        return $tooltip;
                    }),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Enviada')
                    ->date('d/m/Y H:i')
                    ->placeholder('No enviada')
                    ->sortable(),
            ])
            ->recordUrl(fn ($record): string => static::getUrl('view', ['record' => $record]))
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
                // Acciones principales siempre visibles
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record && $record->canBeEdited() && auth()->id() === $record->user_id),

                // Grupo de acciones de autorización departamental
                Tables\Actions\ActionGroup::make([
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
                ])
                    ->label('Autorización')
                    ->icon('heroicon-m-check-badge')
                    ->color('warning')
                    ->button()
                    ->visible(fn ($record) => $record && (
                        $record->canBeAuthorizedBy(auth()->user()) ||
                        $record->canBeRevisedBy(auth()->user())
                    )),

                // Grupo de acciones del equipo de viajes
                Tables\Actions\ActionGroup::make([
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
                ])
                    ->label('Equipo de Viajes')
                    ->icon('heroicon-m-map')
                    ->color('primary')
                    ->button()
                    ->visible(fn ($record) => $record && $record->canBeTravelReviewedBy(auth()->user())),

                // Grupo de acciones de archivos
                Tables\Actions\ActionGroup::make([
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

                                $downloadUrl = $attachment->download_url;

                                $content .= '
                                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <!-- Tipo de archivo - MÁS IMPORTANTE -->
                                            <div class="flex items-center mb-2">
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200">
                                                    '.$typeLabel.'
                                                </span>
                                            </div>
                                            
                                            <!-- Información de subida - IMPORTANCIA MEDIA -->
                                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                Subido por <span class="font-medium">'.htmlspecialchars($attachment->uploader->name).'</span> el '.$attachment->created_at->format('d/m/Y \a \l\a\s H:i').'
                                            </div>
                                            
                                            <!-- Nombre del archivo - MENOS IMPORTANTE -->
                                            <div class="text-xs text-gray-500 dark:text-gray-500">
                                                📄 '.htmlspecialchars($attachment->file_name).' ('.$formattedSize.')
                                            </div>';

                                if ($attachment->description) {
                                    $content .= '<div class="text-sm text-gray-600 dark:text-gray-300 mt-2 p-2 bg-gray-100 dark:bg-gray-700 rounded">
                                        <span class="font-medium">Descripción:</span> '.htmlspecialchars($attachment->description).'
                                    </div>';
                                }

                                $content .= '
                                        </div>
                                        <div class="flex flex-col space-y-2">
                                            <a href="'.$downloadUrl.'" 
                                               target="_blank"
                                               class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    ->label('Archivos')
                    ->icon('heroicon-m-paper-clip')
                    ->color('gray')
                    ->button()
                    ->visible(fn ($record) => $record && (
                        $record->canUploadAttachments(auth()->user()) ||
                        ($record->status === 'travel_approved' && $record->attachments()->count() > 0)
                    )),

                // Grupo de acciones de tesorería
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('mark_advance_deposit')
                        ->label('Marcar Depósito')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn ($record) => $record && $record->canMarkAdvanceDeposit(auth()->user()))
                        ->form([
                            \Filament\Forms\Components\TextInput::make('advance_deposit_amount')
                                ->label('Monto del Depósito')
                                ->numeric()
                                ->prefix('$')
                                ->step(0.01)
                                ->minValue(0)
                                ->placeholder('0.00')
                                ->required()
                                ->helperText('Ingresa el monto exacto depositado'),
                            \Filament\Forms\Components\Textarea::make('advance_deposit_notes')
                                ->label('Notas del Depósito')
                                ->placeholder('Referencia, número de transferencia, banco, etc.')
                                ->rows(3)
                                ->helperText('Información adicional sobre el depósito'),
                            \Filament\Forms\Components\FileUpload::make('deposit_receipt')
                                ->label('Comprobante de Depósito')
                                ->disk('local')
                                ->directory('deposit-receipts')
                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                                ->maxSize(5120) // 5MB
                                ->preserveFilenames()
                                ->openable()
                                ->downloadable()
                                ->helperText('Sube el comprobante del depósito (PDF, JPG, PNG - máx. 5MB)')
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            try {
                                // Mark the deposit as made
                                $record->markAdvanceDepositMade(
                                    auth()->user(),
                                    $data['advance_deposit_amount'] ?? null,
                                    $data['advance_deposit_notes'] ?? null
                                );

                                // Upload the deposit receipt attachment if provided
                                if (! empty($data['deposit_receipt'])) {
                                    // Get the "Comprobante de Depósito" attachment type
                                    $depositAttachmentType = \App\Models\AttachmentType::where('slug', 'advance_deposit_receipt')->first();

                                    if ($depositAttachmentType) {
                                        // Get file information
                                        $filePath = $data['deposit_receipt'];
                                        $fileName = basename($filePath);
                                        $fileSize = \Storage::disk('local')->size($filePath);
                                        $fileType = \Storage::disk('local')->mimeType($filePath);

                                        \App\Models\TravelRequestAttachment::create([
                                            'travel_request_id' => $record->id,
                                            'attachment_type_id' => $depositAttachmentType->id,
                                            'file_path' => $filePath,
                                            'file_name' => $fileName,
                                            'file_type' => $fileType,
                                            'file_size' => $fileSize,
                                            'uploaded_by' => auth()->id(),
                                            'description' => 'Comprobante de depósito de anticipo',
                                        ]);
                                    }
                                }

                                \Filament\Notifications\Notification::make()
                                    ->title('Depósito de anticipo marcado')
                                    ->body('El depósito y el comprobante han sido registrados exitosamente.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error al marcar depósito')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('unmark_advance_deposit')
                        ->label('Desmarcar Depósito')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record && $record->canUnmarkAdvanceDeposit(auth()->user()))
                        ->requiresConfirmation()
                        ->modalHeading('Desmarcar depósito de anticipo')
                        ->modalDescription('¿Estás seguro de que deseas desmarcar este depósito? Esta acción eliminará toda la información del depósito y los comprobantes asociados.')
                        ->action(function ($record) {
                            try {
                                // Remove deposit receipt attachments before unmarking
                                $depositAttachmentType = \App\Models\AttachmentType::where('slug', 'advance_deposit_receipt')->first();
                                if ($depositAttachmentType) {
                                    $depositAttachments = $record->attachments()->where('attachment_type_id', $depositAttachmentType->id)->get();
                                    foreach ($depositAttachments as $attachment) {
                                        // Delete the physical file
                                        if (\Storage::disk('local')->exists($attachment->file_path)) {
                                            \Storage::disk('local')->delete($attachment->file_path);
                                        }
                                        // Delete the record
                                        $attachment->delete();
                                    }
                                }

                                $record->unmarkAdvanceDeposit(auth()->user());

                                \Filament\Notifications\Notification::make()
                                    ->title('Depósito desmarcado')
                                    ->body('El depósito y los comprobantes han sido eliminados exitosamente.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error al desmarcar depósito')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('manage_deposit_receipt')
                        ->label('Comprobante de Depósito')
                        ->icon('heroicon-o-document-arrow-up')
                        ->color('info')
                        ->visible(fn ($record) => $record && $record->advance_deposit_made && auth()->user()->isTreasuryTeamMember())
                        ->action(function ($record) {
                            // Get existing deposit receipts
                            $depositAttachmentType = \App\Models\AttachmentType::where('slug', 'advance_deposit_receipt')->first();
                            $existingReceipts = $depositAttachmentType
                                ? $record->attachments()->where('attachment_type_id', $depositAttachmentType->id)->get()
                                : collect();

                            if ($existingReceipts->isNotEmpty()) {
                                // Show existing receipts
                                $receiptsList = $existingReceipts->map(function ($attachment) {
                                    return "• {$attachment->original_name} (subido el {$attachment->created_at->format('d/m/Y H:i')})";
                                })->join("\n");

                                \Filament\Notifications\Notification::make()
                                    ->title('Comprobantes de depósito existentes')
                                    ->body("Los siguientes comprobantes están registrados:\n\n{$receiptsList}")
                                    ->info()
                                    ->persistent()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Sin comprobantes')
                                    ->body('No hay comprobantes de depósito registrados para esta solicitud.')
                                    ->warning()
                                    ->send();
                            }
                        }),
                ])
                    ->label('Tesorería')
                    ->icon('heroicon-m-banknotes')
                    ->color('success')
                    ->button()
                    ->visible(fn ($record) => $record && auth()->user()->isTreasuryTeamMember() &&
                        ($record->canMarkAdvanceDeposit(auth()->user()) ||
                         $record->canUnmarkAdvanceDeposit(auth()->user()) ||
                         ($record->advance_deposit_made && $record->status === 'travel_approved'))),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->can('delete_travel_requests')),
            ])
            ->recordUrl(
                fn ($record): string => static::getUrl('view', ['record' => $record])
            )
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
