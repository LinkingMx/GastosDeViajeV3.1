@component('mail::message')
# Comprobación Escalada para Autorización Mayor

@if(isset($verification->travelRequest->generalSetting->autorizadorMayor))
Estimado/a {{ $verification->travelRequest->generalSetting->autorizadorMayor->name }},
@else
Estimado Autorizador Mayor,
@endif

Una comprobación de gastos ha sido escalada y requiere su autorización especial.

## Detalles de la Comprobación

**Folio:** {{ $verification->folio }}
**Solicitante:** {{ $verification->creator->name }}
**Solicitud de Viaje:** {{ $verification->travelRequest->folio }}
**Monto Total Comprobado:** ${{ number_format($verification->getTotalVerifiedAmount(), 2) }}
**Escalado por:** {{ $verification->approver->name }}
**Fecha de Escalación:** {{ $verification->approved_at->format('d/m/Y H:i') }}

@if($verification->approval_notes)
**Comentarios del Revisor:**
{{ $verification->approval_notes }}
@endif

@component('mail::button', ['url' => $url])
Revisar y Autorizar
@endcomponent

Esta comprobación requiere autorización de nivel superior. Por favor, revise y tome la acción correspondiente.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
