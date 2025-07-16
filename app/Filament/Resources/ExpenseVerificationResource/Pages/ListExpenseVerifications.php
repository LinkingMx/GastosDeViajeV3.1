<?php

namespace App\Filament\Resources\ExpenseVerificationResource\Pages;

use App\Filament\Resources\ExpenseVerificationResource;
use App\Filament\Resources\ExpenseVerificationResource\Concerns\HasTabs;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;

class ListExpenseVerifications extends ListRecords
{
    use HasTabs;
    
    protected static string $resource = ExpenseVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Comprobación'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            // Acción principal de editar (solo en draft/revision por el propietario)
            Tables\Actions\EditAction::make()
                ->visible(fn ($record) => $record && $record->canBeEdited() && auth()->id() === $record->created_by),

            // Grupo de acciones del propietario (solo en tab activas)
            Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('submit_for_authorization')
                    ->label('Enviar a Autorización')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn ($record) => $record && $record->canBeSubmitted() && auth()->id() === $record->created_by)
                    ->requiresConfirmation()
                    ->modalHeading('Enviar Comprobación a Autorización')
                    ->modalDescription('Al enviar esta comprobación, será revisada por el equipo de viajes. ¿Estás seguro?')
                    ->action(function ($record) {
                        $record->submitForAuthorization();
                        \Filament\Notifications\Notification::make()
                            ->title('Comprobación Enviada')
                            ->body('La comprobación ha sido enviada para autorización del equipo de viajes.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('put_in_revision')
                    ->label('Poner en Revisión')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(fn ($record) => $record && $record->canBeRevisedBy(auth()->user()))
                    ->requiresConfirmation()
                    ->modalHeading('Poner en Revisión')
                    ->modalDescription('Al poner la comprobación en revisión podrás editarla nuevamente.')
                    ->action(function ($record) {
                        $record->putInRevision();
                        \Filament\Notifications\Notification::make()
                            ->title('Comprobación en Revisión')
                            ->body('La comprobación ha sido puesta en revisión.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record && $record->canBeEdited() && auth()->id() === $record->created_by),
            ])
                ->label('Mis Acciones')
                ->icon('heroicon-m-user')
                ->color('info')
                ->button()
                ->visible(fn ($record) => $record && auth()->id() === $record->created_by && (
                    $record->canBeSubmitted() || 
                    $record->canBeRevisedBy(auth()->user()) || 
                    $record->canBeEdited()
                )),

            // Grupo de acciones de autorización (equipo de viajes)
            Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record && $record->canBeAuthorizedBy(auth()->user()))
                    ->form([
                        \Filament\Forms\Components\Textarea::make('approval_notes')
                            ->label('Comentarios (opcional)')
                            ->placeholder('Agregar comentarios sobre la aprobación...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->approve(auth()->user(), $data['approval_notes'] ?? null);
                        \Filament\Notifications\Notification::make()
                            ->title('Comprobación Aprobada')
                            ->body('La comprobación de gastos ha sido aprobada exitosamente.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record && $record->canBeAuthorizedBy(auth()->user()))
                    ->form([
                        \Filament\Forms\Components\Textarea::make('approval_notes')
                            ->label('Motivo del rechazo')
                            ->placeholder('Explica el motivo del rechazo...')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject(auth()->user(), $data['approval_notes']);
                        \Filament\Notifications\Notification::make()
                            ->title('Comprobación Rechazada')
                            ->body('La comprobación de gastos ha sido rechazada.')
                            ->success()
                            ->send();
                    }),
            ])
                ->label('Autorización')
                ->icon('heroicon-m-check-badge')
                ->color('warning')
                ->button()
                ->visible(fn ($record) => $record && $record->canBeAuthorizedBy(auth()->user())),

            // Grupo de acciones de reembolso (equipo de tesorería)
            Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('mark_reimbursement')
                    ->label('Marcar Reembolso')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => $record && $record->canBeReimbursedBy(auth()->user()))
                    ->form([
                        \Filament\Forms\Components\TextInput::make('reimbursement_amount')
                            ->label('Monto del Reembolso')
                            ->prefix('$')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->default(function ($record) {
                                return $record ? $record->getReimbursementAmountNeeded() : 0;
                            })
                            ->helperText(function ($record) {
                                if (!$record) return '';
                                $needed = $record->getReimbursementAmountNeeded();
                                return "Monto sugerido: $" . number_format($needed, 2);
                            }),
                        
                        \Filament\Forms\Components\Textarea::make('reimbursement_notes')
                            ->label('Comentarios del Reembolso')
                            ->placeholder('Agregar comentarios sobre el reembolso realizado...'),
                        
                        \Filament\Forms\Components\FileUpload::make('reimbursement_attachments')
                            ->label('Comprobantes del Reembolso')
                            ->multiple()
                            ->directory('reimbursement-attachments')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->helperText('Sube los comprobantes del reembolso (PDF, JPG, PNG)'),
                    ])
                    ->action(function ($record, array $data) {
                        $attachments = [];
                        if (!empty($data['reimbursement_attachments'])) {
                            foreach ($data['reimbursement_attachments'] as $file) {
                                $attachments[] = $file;
                            }
                        }
                        
                        $record->markReimbursementMade(
                            auth()->user(),
                            $data['reimbursement_amount'],
                            $data['reimbursement_notes'] ?? null,
                            $attachments ?: null
                        );
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Reembolso Registrado')
                            ->body('El reembolso ha sido registrado exitosamente. La comprobación está ahora cerrada.')
                            ->success()
                            ->send();
                    }),
            ])
                ->label('Reembolso')
                ->icon('heroicon-m-banknotes')
                ->color('info')
                ->button()
                ->visible(fn ($record) => $record && $record->canBeReimbursedBy(auth()->user())),

            // Acciones para históricos (visible en tabs historical y archived)
            Tables\Actions\ActionGroup::make([
                // Acción para reabrir
                Tables\Actions\Action::make('reopen')
                    ->label('Reabrir')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record && $record->canBeReopenedBy(auth()->user()))
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reopening_reason')
                            ->label('Motivo de la Reapertura')
                            ->placeholder('Explica el motivo por el cual necesitas reabrir esta comprobación...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reopen(auth()->user(), $data['reopening_reason']);
                        \Filament\Notifications\Notification::make()
                            ->title('Comprobación Reabierta')
                            ->body('La comprobación ha sido reabierta y está disponible para edición.')
                            ->success()
                            ->send();
                    }),

                // Acción para agregar notas administrativas
                Tables\Actions\Action::make('add_admin_note')
                    ->label('Agregar Nota')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('info')
                    ->visible(fn ($record) => $record && (auth()->user()->isTravelTeamMember() || auth()->user()->isTreasuryTeamMember()))
                    ->form([
                        \Filament\Forms\Components\Textarea::make('administrative_note')
                            ->label('Nota Administrativa')
                            ->placeholder('Agregar comentarios administrativos...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->addAdministrativeNote(auth()->user(), $data['administrative_note']);
                        \Filament\Notifications\Notification::make()
                            ->title('Nota Agregada')
                            ->body('La nota administrativa ha sido agregada exitosamente.')
                            ->success()
                            ->send();
                    }),

                // Acción para archivar
                Tables\Actions\Action::make('archive')
                    ->label('Archivar')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn ($record) => $record && $record->canBeArchivedBy(auth()->user()))
                    ->requiresConfirmation()
                    ->modalHeading('Archivar Comprobación')
                    ->modalDescription('Al archivar esta comprobación se moverá a la sección de archivados y no será visible en las vistas principales.')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('archive_reason')
                            ->label('Motivo del Archivado (opcional)')
                            ->placeholder('Razón para archivar esta comprobación...')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $record->archive(auth()->user(), $data['archive_reason'] ?? null);
                        \Filament\Notifications\Notification::make()
                            ->title('Comprobación Archivada')
                            ->body('La comprobación ha sido archivada exitosamente.')
                            ->success()
                            ->send();
                    }),

                // Acción para ver historial de auditoría
                Tables\Actions\Action::make('view_audit')
                    ->label('Ver Auditoría')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->visible(fn ($record) => $record && (auth()->user()->isTravelTeamMember() || auth()->user()->isTreasuryTeamMember()))
                    ->modalContent(function ($record) {
                        $auditHistory = $record->audit_history;
                        if (empty($auditHistory)) {
                            return view('filament.components.empty-audit');
                        }
                        return view('filament.components.audit-history', compact('auditHistory'));
                    })
                    ->modalHeading('Historial de Auditoría')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
            ])
                ->label('Gestión')
                ->icon('heroicon-m-cog-6-tooth')
                ->color('gray')
                ->button()
                ->visible(fn ($record) => $record && $record->isHistorical() && (auth()->user()->isTravelTeamMember() || auth()->user()->isTreasuryTeamMember())),
        ];
    }
}
