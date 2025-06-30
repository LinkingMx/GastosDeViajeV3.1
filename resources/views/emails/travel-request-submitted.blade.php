<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud de Viaje para Autorización</title>
</head>

<body>
    <h1>Nueva Solicitud de Viaje para Autorización</h1>
    <p>Hola {{ $travelRequest->authorizer->name }},</p>
    <p>Se ha enviado una nueva solicitud de viaje para tu autorización.</p>

    <h2>Detalles de la Solicitud:</h2>
    <ul>
        <li><strong>Solicitante:</strong> {{ $travelRequest->user->name }}</li>
        <li><strong>Destino:</strong> {{ $travelRequest->destination_city }},
            {{ $travelRequest->destinationCountry->name }}</li>
        <li><strong>Fechas:</strong> Del {{ $travelRequest->departure_date->format('d/m/Y') }} al
            {{ $travelRequest->return_date->format('d/m/Y') }}</li>
    </ul>

    <p>Para revisar y procesar esta solicitud, por favor haz clic en el siguiente enlace:</p>
    <p><a href="{{ route('filament.admin.resources.travel-requests.view', $travelRequest) }}">Ver Solicitud de Viaje</a>
    </p>

    <p>Gracias,</p>
    <p>El equipo de Gastos de Viaje</p>
</body>

</html>
