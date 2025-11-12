@component('mail::message')
# Comprobación de Gastos Cerrada

Estimado/a {{ $verification->creator->name }},

Su comprobación de gastos ha sido **cerrada** y el proceso ha finalizado.

## Detalles de la Comprobación

**Folio:** {{ $verification->folio }}
**Solicitud de Viaje:** {{ $verification->travelRequest->folio }}
**Monto Total Comprobado:** ${{ number_format($verification->getTotalVerifiedAmount(), 2) }}
**Fecha de Cierre:** {{ $verification->closed_at->format('d/m/Y H:i') }}

@if($verification->closure_notes)
**Notas de Cierre:**
{{ $verification->closure_notes }}
@endif

@component('mail::button', ['url' => $url])
Ver Comprobación
@endcomponent

El proceso de comprobación de gastos ha sido completado. Si tiene alguna pregunta, por favor contacte al equipo correspondiente.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
