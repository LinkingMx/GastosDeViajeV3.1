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
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\Actions as InfolistActions;
use Filament\Infolists\Components\Actions\Action as InfolistAction;

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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'user.department.authorizer', 'authorizer']))
            ->columns([
               
                Tables\Columns\TextColumn::make('folio')
                    ->label('Folio')
                    ->getStateUsing(fn ($record) => $record->folio)
                    ->badge()
                    ->color('primary')
                    ->searchable(['uuid'])
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('uuid', $direction);
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Solicitante')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\ViewColumn::make('origin_destination')
                    ->label('Origen / Destino')
                    ->view('filament.tables.columns.origin-destination')
                    ->searchable(['origin_city', 'destination_city']),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Fecha de Salida')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_date')
                    ->label('Fecha de Regreso')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('actualAuthorizer.name')
                    ->label('Autorizador')
                    ->sortable()
                    ->placeholder('Sin autorizador')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
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
                        'pending_verification' => 'info',
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
                            'pending_verification' => 'Por Comprobar',
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
                            'pending_verification' => 'info',
                            default => 'gray',
                        };
                    })
                    ->icon(function ($record) {
                        return null;
                    })
                    ->tooltip(function ($record) {
                        return null;
                    })
                    ->visible(fn ($record) => $record && in_array($record->status, ['approved', 'travel_review', 'travel_approved', 'travel_rejected', 'pending_verification'])),
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
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
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
                        'pending_verification' => 'Por Comprobar',
                    ]),
            ])
            ->actions([
                // Todas las acciones agrupadas en un solo botón "Acciones"
                Tables\Actions\ActionGroup::make([
                    // Acciones del usuario propietario
                    Tables\Actions\EditAction::make()
                        ->visible(fn ($record) => $record && $record->canBeEdited() && auth()->id() === $record->user_id),

                    Tables\Actions\Action::make('submitForAuthorization')
                        ->label('Enviar a Autorización')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Enviar Solicitud a Autorización')
                        ->modalDescription('¿Estás seguro de que deseas enviar esta solicitud para autorización? Una vez enviada, no podrás realizar más cambios.')
                        ->modalSubmitActionLabel('Sí, Enviar')
                        ->action(function ($record) {
                            try {
                                // Enviar a autorización usando el método del modelo
                                $record->submitForAuthorization();

                                \Filament\Notifications\Notification::make()
                                    ->title('Solicitud Enviada')
                                    ->body('Tu solicitud ha sido enviada para autorización a '.$record->actual_authorizer->name)
                                    ->success()
                                    ->send();

                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error al Enviar')
                                    ->body('Ocurrió un error al enviar la solicitud: '.$e->getMessage())
                                    ->danger()
                                    ->send();

                                \Log::error('Error submitting travel request: '.$e->getMessage(), [
                                    'record_id' => $record->id,
                                    'user_id' => auth()->id(),
                                    'trace' => $e->getTraceAsString(),
                                ]);
                            }
                        })
                        ->visible(fn ($record) => $record && $record->canBeSubmitted() && $record->actual_authorizer && auth()->id() === $record->user_id),

                    // Separador visual
                    Tables\Actions\Action::make('separator1')
                        ->label('— Autorización Departamental —')
                        ->disabled()
                        ->visible(fn ($record) => $record && $record->canBeAuthorizedBy(auth()->user())),

                    // Acciones de autorización departamental
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

                    // Separador visual
                    Tables\Actions\Action::make('separator2')
                        ->label('— Equipo de Viajes —')
                        ->disabled()
                        ->visible(fn ($record) => $record && $record->canBeTravelReviewedBy(auth()->user())),

                    // Acciones del equipo de viajes
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

                    // Separador visual
                    Tables\Actions\Action::make('separator3')
                        ->label('— Gestión de Archivos —')
                        ->disabled()
                        ->visible(fn ($record) => $record && $record->canUploadAttachments(auth()->user())),

                    // Acción de subir archivos (movida del ActionGroup anidado)
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
                                $attachment = $record->attachments()->create([
                                    'uploaded_by' => auth()->id(),
                                    'file_name' => $originalName,
                                    'file_path' => $filePath,
                                    'file_type' => $mimeType,
                                    'file_size' => $fileSize,
                                    'attachment_type_id' => $data['attachment_type_id'],
                                    'description' => $data['description'] ?? null,
                                ]);

                                // Obtener el tipo de archivo para la notificación
                                $attachmentType = \App\Models\AttachmentType::find($data['attachment_type_id']);
                                $attachmentTypeName = $attachmentType ? $attachmentType->name : 'Documento';

                                // Notificar al solicitante si el que sube es del equipo de tesorería O del equipo de viajes
                                \Log::info("Usuario que sube archivo: " . auth()->user()->name . " (ID: " . auth()->id() . ")");
                                \Log::info("Es del equipo de tesorería: " . (auth()->user()->isTreasuryTeamMember() ? 'SÍ' : 'NO'));
                                \Log::info("Es del equipo de viajes: " . (auth()->user()->isTravelTeamMember() ? 'SÍ' : 'NO'));
                                
                                if (auth()->user()->isTreasuryTeamMember() || auth()->user()->isTravelTeamMember()) {
                                    \Log::info("Enviando notificación a usuario solicitante: " . $record->user->email);
                                    
                                    // Determinar el equipo que sube el archivo
                                    $uploaderTeam = auth()->user()->isTreasuryTeamMember() ? 'tesorería' : 'viajes';
                                    

                                    // Enviar correo al solicitante
                                    try {
                                        \Log::info("Intentando enviar correo de archivo adjunto");
                                        \Illuminate\Support\Facades\Mail::to($record->user->email)
                                            ->send(new \App\Mail\TeamFileUploadedMail(
                                                $record,
                                                $attachmentTypeName,
                                                $originalName,
                                                auth()->user()->name,
                                                $uploaderTeam,
                                                $filePath
                                            ));
                                        \Log::info("Correo de archivo adjunto enviado exitosamente");
                                    } catch (\Exception $e) {
                                        \Log::error("Error enviando correo de archivo adjunto: " . $e->getMessage());
                                    }

                                    // Crear notificación de campanita al solicitante
                                    try {
                                        \Log::info("Enviando notificación de campanita al usuario");
                                        
                                        $record->user->notify(new \App\Notifications\TravelRequestNotification(
                                            '📄 Nuevo Archivo Adjunto',
                                            "El equipo de {$uploaderTeam} ha adjuntado un archivo ({$attachmentTypeName}) a tu solicitud {$record->folio}",
                                            $record
                                        ));
                                            
                                        \Log::info("Notificación de campanita guardada en base de datos");
                                    } catch (\Exception $e) {
                                        \Log::error("Error enviando notificación de campanita: " . $e->getMessage());
                                    }
                                }

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
                        ->visible(fn ($record) => $record && $record->attachments()->count() > 0)
                        ->modalHeading(fn ($record) => 'Archivos Adjuntos - '.$record->folio)
                        ->modalWidth('4xl')
                        ->infolist([
                            RepeatableEntry::make('attachments')
                                ->label('')
                                ->schema([
                                    InfolistSection::make()
                                        ->schema([
                                            // Tipo de archivo
                                            TextEntry::make('attachmentType.name')
                                                ->label('Tipo de Archivo')
                                                ->badge()
                                                ->color('info')
                                                ->size(TextEntry\TextEntrySize::Large)
                                                ->weight('semibold'),

                                            // Información de subida
                                            TextEntry::make('uploader.name')
                                                ->label('Subido por')
                                                ->icon('heroicon-o-user')
                                                ->formatStateUsing(fn ($record) =>
                                                    $record->uploader->name .
                                                    ' el ' .
                                                    $record->created_at->format('d/m/Y \a \l\a\s H:i')
                                                ),

                                            // Nombre del archivo y tamaño
                                            TextEntry::make('file_name')
                                                ->label('Archivo')
                                                ->icon('heroicon-o-document')
                                                ->formatStateUsing(fn ($record) =>
                                                    $record->file_name . ' (' . $record->formatted_file_size . ')'
                                                ),

                                            // Descripción (condicional)
                                            TextEntry::make('description')
                                                ->label('Descripción')
                                                ->visible(fn ($record) => !empty($record->description))
                                                ->icon('heroicon-o-information-circle')
                                                ->color('gray'),

                                            // Botón de descarga
                                            InfolistActions::make([
                                                InfolistAction::make('download')
                                                    ->label('Descargar Archivo')
                                                    ->icon('heroicon-o-arrow-down-tray')
                                                    ->color('primary')
                                                    ->url(fn ($record) => $record->download_url)
                                                    ->openUrlInNewTab()
                                            ])
                                        ])
                                        ->columns(1)
                                ])
                                ->contained(true)
                                ->grid(1)
                        ])
                        ->modalCancelAction(false)
                        ->modalSubmitAction(false),

                    Tables\Actions\Action::make('delete_attachment')
                        ->label('Eliminar Archivo')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->visible(fn ($record) => $record && auth()->user()->isTravelTeamMember() && $record->attachments()->count() > 0)
                        ->form([
                            \Filament\Forms\Components\Select::make('attachment_id')
                                ->label('Archivo a eliminar')
                                ->required()
                                ->options(function ($record) {
                                    return $record->attachments()
                                        ->with('attachmentType')
                                        ->get()
                                        ->mapWithKeys(function ($attachment) {
                                            $typeLabel = $attachment->attachmentType?->name ?? 'Documento';
                                            return [$attachment->id => "{$typeLabel} - {$attachment->file_name}"];
                                        });
                                })
                                ->searchable(),
                            \Filament\Forms\Components\Textarea::make('reason')
                                ->label('Motivo de eliminación (opcional)')
                                ->placeholder('Explica por qué eliminas este archivo...')
                                ->rows(3),
                        ])
                        ->action(function ($record, array $data) {
                            try {
                                $attachment = \App\Models\TravelRequestAttachment::findOrFail($data['attachment_id']);
                                
                                // Verificar que el archivo pertenece a esta solicitud
                                if ($attachment->travel_request_id !== $record->id) {
                                    throw new \Exception('El archivo no pertenece a esta solicitud');
                                }

                                // Guardar información del archivo antes de eliminarlo
                                $attachmentType = $attachment->attachmentType?->name ?? 'Documento';
                                $fileName = $attachment->file_name;
                                $reason = $data['reason'] ?? null;

                                // Eliminar el archivo físico y el registro
                                $attachment->delete();

                                // Registrar actividad
                                $record->activities()->create([
                                    'user_id' => auth()->id(),
                                    'comment' => 'Archivo eliminado: ' . $fileName . ($reason ? ' - Motivo: ' . $reason : ''),
                                    'type' => 'file_deleted',
                                ]);

                                // Enviar correo al solicitante
                                try {
                                    \Log::info("Enviando correo de archivo eliminado");
                                    \Illuminate\Support\Facades\Mail::to($record->user->email)
                                        ->send(new \App\Mail\TeamFileDeletedMail(
                                            $record,
                                            $attachmentType,
                                            $fileName,
                                            auth()->user()->name,
                                            'viajes',
                                            $reason
                                        ));
                                    \Log::info("Correo de archivo eliminado enviado exitosamente");
                                } catch (\Exception $e) {
                                    \Log::error("Error enviando correo de archivo eliminado: " . $e->getMessage());
                                }

                                // Crear notificación de campanita al solicitante
                                try {
                                    \Log::info("Enviando notificación de campanita por archivo eliminado");
                                    
                                    $record->user->notify(new \App\Notifications\TravelRequestNotification(
                                        '🗑️ Archivo Eliminado',
                                        "El equipo de viajes ha eliminado el archivo '{$fileName}' ({$attachmentType}) de tu solicitud {$record->folio}" . ($reason ? ". Motivo: {$reason}" : '.'),
                                        $record
                                    ));
                                        
                                    \Log::info("Notificación de campanita por archivo eliminado enviada");
                                } catch (\Exception $e) {
                                    \Log::error("Error enviando notificación de campanita por archivo eliminado: " . $e->getMessage());
                                }

                                \Filament\Notifications\Notification::make()
                                    ->title('Archivo eliminado exitosamente')
                                    ->body("El archivo '{$fileName}' ha sido eliminado y se ha notificado al solicitante.")
                                    ->success()
                                    ->send();

                            } catch (\Exception $e) {
                                \Log::error("Error eliminando archivo: " . $e->getMessage());
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Error al eliminar archivo')
                                    ->body('Ocurrió un error al eliminar el archivo: '.$e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // Separador visual
                    Tables\Actions\Action::make('separator4')
                        ->label('— Tesorería —')
                        ->disabled()
                        ->visible(fn ($record) => $record && auth()->user()->isTreasuryTeamMember()),

                    // Acciones de tesorería (movidas del ActionGroup anidado)
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
                                ->default(function ($record) {
                                    if (!$record) return 0;

                                    // Calculate per diem total
                                    $perDiemTotal = 0;
                                    $perDiemData = $record->per_diem_data ?? [];
                                    $departureDate = $record->departure_date;
                                    $returnDate = $record->return_date;

                                    if ($departureDate && $returnDate) {
                                        $totalDays = max(1, $departureDate->diffInDays($returnDate) + 1);

                                        foreach ($perDiemData as $perDiemId => $data) {
                                            if ($data['enabled'] ?? false) {
                                                $perDiem = \App\Models\PerDiem::find($perDiemId);
                                                if ($perDiem) {
                                                    $perDiemTotal += $totalDays * $perDiem->amount;
                                                }
                                            }
                                        }
                                    }

                                    // Calculate custom expenses total
                                    $customExpensesTotal = 0;
                                    $customExpensesData = $record->custom_expenses_data ?? [];
                                    foreach ($customExpensesData as $expense) {
                                        if (!empty($expense['amount'])) {
                                            $customExpensesTotal += floatval($expense['amount']);
                                        }
                                    }

                                    return $perDiemTotal + $customExpensesTotal;
                                })
                                ->placeholder('0.00')
                                ->required()
                                ->helperText('Monto total calculado automáticamente. Puedes modificarlo si es necesario.'),
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
                ->label('')
                ->icon('heroicon-m-ellipsis-vertical')
                ->button(),
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
        $query = parent::getEloquentQuery()
            ->with(['user', 'user.department.authorizer', 'authorizer']); // Cargar relaciones necesarias
        
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
                })
                ->orWhere(function (Builder $query) use ($user) {
                    // NUEVO: Solicitudes para miembros del equipo de tesorería
                    if ($user->isTreasuryTeamMember()) {
                        $query->whereIn('status', ['travel_approved', 'pending_verification']);
                    }
                });
        });
    }
}
