@component('mail::message')
# Comprobación de Gastos Enviada

Estimado Equipo de Viajes,

Se ha recibido una nueva comprobación de gastos que requiere su revisión.

## Detalles de la Comprobación

**Folio:** {{ $verification->folio }}
**Solicitante:** {{ $verification->creator->name }}
**Solicitud de Viaje:** {{ $verification->travelRequest->folio }}
**Monto Total Comprobado:** ${{ number_format($verification->getTotalVerifiedAmount(), 2) }}
**Fecha de Envío:** {{ $verification->submitted_at->format('d/m/Y H:i') }}

@component('mail::button', ['url' => $url])
Ver Comprobación
@endcomponent

Por favor, revise los comprobantes adjuntos y tome la acción correspondiente.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
