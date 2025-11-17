<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Solicitud Autorizada</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #000000;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 0;
        }
        .header {
            background: #ffffff;
            padding: 40px 30px;
            text-align: center;
            border-bottom: 1px solid #e5e5e5;
        }
        .header img {
            max-height: 40px;
            width: auto;
            min-width: 150px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 300;
            color: #000000;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 16px;
            color: #000000;
            margin-bottom: 20px;
            font-weight: 300;
        }
        .success-box {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 16px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success-icon {
            display: inline-block;
            margin-right: 8px;
            vertical-align: middle;
        }
        .success-content {
            display: inline-block;
            vertical-align: middle;
            font-size: 15px;
            color: #065f46;
            font-weight: 500;
        }
        .message {
            font-size: 14px;
            margin-bottom: 30px;
            color: #000000;
            font-weight: 300;
            line-height: 1.8;
        }
        .details-box {
            background-color: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 24px;
            margin: 30px 0;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f0f0f0;
        }
        .details-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .details-label {
            font-weight: 300;
            color: #000000;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .details-value {
            color: #000000;
            text-align: right;
            font-weight: 400;
        }
        .icon {
            width: 16px;
            height: 16px;
            display: inline-block;
            vertical-align: middle;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border: 1px solid #10b981;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #d1fae5;
            color: #065f46;
        }
        .cta-button {
            display: inline-block;
            background: #897053;
            color: #ffffff;
            padding: 14px 28px;
            text-decoration: none;
            font-weight: 400;
            font-size: 14px;
            margin: 20px 0;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            border-radius: 8px;
        }
        .cta-button:hover {
            background: #6d5940;
            transform: translateY(-1px);
        }
        .next-steps-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #f9fafb;
            border-radius: 8px;
        }
        .next-steps-title {
            font-weight: 500;
            color: #000000;
            margin-bottom: 16px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .next-steps-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .next-steps-list li {
            margin-bottom: 12px;
            padding-left: 24px;
            position: relative;
            font-size: 14px;
            color: #000000;
        }
        .next-steps-list li:before {
            content: "•";
            position: absolute;
            left: 8px;
            font-weight: bold;
            color: #897053;
        }
        .warning-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-title {
            font-weight: 500;
            color: #92400e;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .warning-content {
            font-size: 14px;
            color: #78350f;
            line-height: 1.6;
        }
        .footer {
            background-color: #ffffff;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e5e5;
            color: #666666;
            font-size: 12px;
            font-weight: 300;
        }
        .footer p {
            margin: 8px 0;
        }
        @media (max-width: 600px) {
            .container {
                margin: 20px 10px;
            }
            .header, .content {
                padding: 30px 20px;
            }
            .details-row {
                flex-direction: column;
                text-align: left;
            }
            .details-value {
                text-align: left;
                margin-top: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjYwIiB2aWV3Qm94PSIwIDAgMjAwIDYwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgogIDx0ZXh0IHg9IjEwMCIgeT0iMzgiIGZvbnQtZmFtaWx5PSJHYXJhbW9uZCwgVHJhamFuLCBUaW1lcyBOZXcgUm9tYW4sIHNlcmlmIiBmb250LXNpemU9IjMyIiBmb250LXdlaWdodD0iNTAwIiBmaWxsPSIjODk3MDUzIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBsZXR0ZXItc3BhY2luZz0iMiI+Q09TVEVOTzwvdGV4dD4KPC9zdmc+" alt="COSTENO Logo">
            <h1>Solicitud de Viaje Autorizada</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                ¡Hola {{ $travelRequest->user->name }}! 👋
            </div>

            <div class="success-box">
                <span class="success-icon">🎉</span>
                <span class="success-content">¡Excelentes noticias! Tu solicitud de viaje ha sido AUTORIZADA.</span>
            </div>

            <div class="message">
                <p>Tu solicitud ha sido aprobada por <strong>{{ $travelRequest->authorizer->name }}</strong> y ahora pasa a revisión del equipo de viajes para la gestión de reservas y logística.</p>
            </div>

            <div class="details-box">
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 11H15M9 15H15M17 3H7C5.89543 3 5 3.89543 5 5V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V5C19 3.89543 18.1046 3 17 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Folio
                    </span>
                    <span class="details-value"><strong>{{ $travelRequest->folio }}</strong></span>
                </div>
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Fecha de Salida
                    </span>
                    <span class="details-value">{{ $travelRequest->departure_date->format('d/m/Y') }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Fecha de Regreso
                    </span>
                    <span class="details-value">{{ $travelRequest->return_date->format('d/m/Y') }}</span>
                </div>
                @if($travelRequest->origin_city)
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Origen
                    </span>
                    <span class="details-value">{{ $travelRequest->origin_city }}</span>
                </div>
                @endif
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Destino
                    </span>
                    <span class="details-value">{{ $travelRequest->destination_city }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Autorizado por
                    </span>
                    <span class="details-value">{{ $travelRequest->authorizer->name }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Fecha de Autorización
                    </span>
                    <span class="details-value">{{ $travelRequest->authorized_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Estado Actual
                    </span>
                    <span class="details-value">
                        <span class="status-badge">AUTORIZADA</span>
                    </span>
                </div>
            </div>

            <div class="next-steps-section">
                <div class="next-steps-title">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Próximos pasos:
                </div>
                <ul class="next-steps-list">
                    <li>El equipo de viajes revisará tu solicitud en las próximas horas</li>
                    <li>Recibirás notificaciones sobre el avance de las gestiones</li>
                    <li>Se te informará cuando las reservas estén confirmadas</li>
                    <li>Si aplica, se procesará el depósito del anticipo si es requerido</li>
                </ul>
            </div>

            @if(!$travelRequest->requires_advance_payment)
            <div class="warning-box">
                <div class="warning-title">⚠️ Importante:</div>
                <div class="warning-content">
                    No realices ninguna reserva por tu cuenta hasta recibir confirmación del equipo de viajes. Ellos se encargarán de todas las gestiones necesarias.
                </div>
            </div>
            @endif

            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ $viewUrl }}" class="cta-button">
                    📄 Ver Detalles de la Solicitud
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>GastosViajeGC</strong></p>
            <p>Sistema de Gestión de Viajes</p>
            <p>Este correo fue enviado automáticamente desde nuestro sistema de gestión de viajes.</p>
            <p>Por favor, no respondas a este correo. Si necesitas ayuda, contacta al administrador.</p>
        </div>
    </div>
</body>
</html>
