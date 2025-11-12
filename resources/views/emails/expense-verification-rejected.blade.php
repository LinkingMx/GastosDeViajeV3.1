@component('mail::message')
# Comprobación de Gastos Rechazada

Estimado/a {{ $verification->creator->name }},

Su comprobación de gastos ha sido **rechazada** y requiere correcciones.

## Detalles de la Comprobación

**Folio:** {{ $verification->folio }}
**Solicitud de Viaje:** {{ $verification->travelRequest->folio }}
**Rechazado por:** {{ $verification->approver->name }}
**Fecha de Rechazo:** {{ $verification->rejected_at->format('d/m/Y H:i') }}

@if($verification->approval_notes)
**Motivo del Rechazo:**
{{ $verification->approval_notes }}
@endif

@component('mail::button', ['url' => $url])
Ver y Corregir Comprobación
@endcomponent

Por favor, revise los comentarios y realice las correcciones necesarias antes de reenviar su comprobación.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
