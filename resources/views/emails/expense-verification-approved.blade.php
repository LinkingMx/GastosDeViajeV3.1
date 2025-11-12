@component('mail::message')
# Comprobación de Gastos Aprobada

Estimado/a {{ $verification->creator->name }},

Su comprobación de gastos ha sido **aprobada**.

## Detalles de la Comprobación

**Folio:** {{ $verification->folio }}
**Solicitud de Viaje:** {{ $verification->travelRequest->folio }}
**Monto Total Comprobado:** ${{ number_format($verification->getTotalVerifiedAmount(), 2) }}
**Aprobado por:** {{ $verification->approver->name }}
**Fecha de Aprobación:** {{ $verification->approved_at->format('d/m/Y H:i') }}

@if($verification->approval_notes)
**Comentarios del Revisor:**
{{ $verification->approval_notes }}
@endif

@component('mail::button', ['url' => $url])
Ver Comprobación
@endcomponent

@if($verification->needsReimbursement())
**Nota:** Su comprobación requiere reembolso. El equipo de Tesorería procesará el pago correspondiente.
@endif

Saludos,<br>
{{ config('app.name') }}
@endcomponent
