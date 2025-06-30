<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Registro de Solicitud de Viaje</title>
</head>

<body>
    <h1>Confirmación de Registro de Solicitud de Viaje</h1>
    <p>Hola {{ $travelRequest->user->name }},</p>
    <p>Te confirmamos que tu solicitud de viaje ha sido registrada exitosamente en el sistema con el estado de
        <strong>{{ $travelRequest->status }}</strong>.</p>
    <p>Recibirás notificaciones a medida que tu solicitud avance en el proceso de autorización.</p>
    <p>Gracias,</p>
    <p>El equipo de Gastos de Viaje</p>
</body>

</html>
