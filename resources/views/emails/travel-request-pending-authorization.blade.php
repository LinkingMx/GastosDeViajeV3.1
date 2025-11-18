<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Solicitud Pendiente de Autorización</title>
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
        .alert-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .alert-icon {
            display: inline-block;
            margin-right: 8px;
            vertical-align: middle;
        }
        .alert-content {
            display: inline-block;
            vertical-align: middle;
            font-size: 15px;
            color: #92400e;
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
            border: 1px solid #f59e0b;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #fef3c7;
            color: #92400e;
        }
        .purpose-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .purpose-label {
            font-weight: 500;
            color: #000000;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .purpose-content {
            color: #000000;
            font-size: 14px;
            line-height: 1.6;
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
        .options-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #f9fafb;
            border-radius: 8px;
        }
        .options-title {
            font-weight: 500;
            color: #000000;
            margin-bottom: 16px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .options-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .options-list li {
            margin-bottom: 12px;
            padding-left: 24px;
            position: relative;
            font-size: 14px;
            color: #000000;
        }
        .options-list li:before {
            content: "•";
            position: absolute;
            left: 8px;
            font-weight: bold;
            color: #897053;
        }
        .options-list li strong {
            font-weight: 500;
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
            <img src="{{ url('/images/costeno_logo.svg') }}" alt="COSTENO Logo">
            <h1>Solicitud Pendiente de Autorización</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                ¡Hola {{ $authorizer->name }}! 👋
            </div>

            <div class="alert-box">
                <span class="alert-icon">⚠️</span>
                <span class="alert-content">Tienes una solicitud de viaje pendiente de autorización</span>
            </div>

            <div class="message">
                <p><strong>{{ $travelRequest->user->name }}</strong> ha enviado una solicitud de viaje que requiere tu autorización. Por favor, revisa los detalles y toma la acción correspondiente lo antes posible.</p>
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
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Solicitante
                    </span>
                    <span class="details-value">{{ $travelRequest->user->name }}</span>
                </div>
                @if($travelRequest->user->branch)
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Departamento
                    </span>
                    <span class="details-value">{{ $travelRequest->user->branch->name }}</span>
                </div>
                @endif
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
                            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Estado
                    </span>
                    <span class="details-value">
                        <span class="status-badge">PENDIENTE DE AUTORIZACIÓN</span>
                    </span>
                </div>
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Enviada el
                    </span>
                    <span class="details-value">{{ $travelRequest->submitted_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            @if($travelRequest->purpose)
            <div class="purpose-box">
                <div class="purpose-label">
                    <svg class="icon" style="display: inline-block; vertical-align: middle; margin-right: 4px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Motivo del viaje:
                </div>
                <div class="purpose-content">{{ $travelRequest->purpose }}</div>
            </div>
            @endif

            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ $authorizationUrl }}" class="cta-button">
                    ✓ Revisar y Autorizar Solicitud
                </a>
            </div>

            <div class="options-section">
                <div class="options-title">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Opciones disponibles:
                </div>
                <ul class="options-list">
                    <li><strong>Aprobar:</strong> La solicitud continuará con el proceso de aprobación</li>
                    <li><strong>Rechazar:</strong> La solicitud será cancelada y el solicitante será notificado</li>
                    <li><strong>Poner en revisión:</strong> Solicitar cambios o aclaraciones al solicitante</li>
                </ul>
            </div>

            <div class="message">
                <p><strong>Nota:</strong> Esta solicitud requiere tu atención. El solicitante está a la espera de tu respuesta para poder continuar con los preparativos de su viaje.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>GastosViajeGC</strong></p>
            <p>Sistema de Gestión de Viajes</p>
            <p>Este correo fue enviado automáticamente desde nuestro sistema de gestión de viajes.</p>
            <p>Por favor, no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
