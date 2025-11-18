<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Anticipo Depositado</title>
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
        .divider {
            height: 1px;
            background-color: #e5e5e5;
            margin: 40px 0;
        }
        .success-box {
            background-color: #d1fae5;
            border-left: 3px solid #10b981;
            padding: 16px;
            margin: 20px 0;
        }
        .success-box p {
            margin: 0;
            color: #065f46;
            font-size: 14px;
        }
        .amount-box {
            background-color: #fafafa;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .amount-box .amount {
            font-size: 28px;
            font-weight: 500;
            color: #000000;
            margin-bottom: 8px;
        }
        .amount-box .label {
            font-size: 14px;
            color: #666666;
        }
        .process-section {
            background-color: #fafafa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .process-section h3 {
            margin: 0 0 16px 0;
            font-size: 16px;
            font-weight: 500;
            color: #000000;
        }
        .process-section ul {
            margin: 0;
            padding-left: 20px;
            color: #000000;
        }
        .process-section li {
            margin-bottom: 8px;
            font-size: 14px;
        }
        strong {
            font-weight: 500;
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
            <h1>Anticipo Depositado</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Estimado/a {{ $travelRequest->user->name }},
            </div>

            <div class="message">
                <p>Le confirmamos que el depósito del anticipo para su solicitud de viaje ha sido procesado exitosamente por el equipo de tesorería.</p>
            </div>

            <div class="success-box">
                <p><strong>El anticipo ha sido depositado y su solicitud está lista para el viaje</strong></p>
            </div>

            @if($travelRequest->advance_deposit_amount)
            <div class="amount-box">
                <div class="amount">${{ number_format($travelRequest->advance_deposit_amount, 2) }}</div>
                <div class="label">Monto del anticipo depositado</div>
            </div>
            @endif

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
                        Periodo
                    </span>
                    <span class="details-value">{{ $travelRequest->departure_date->format('d/m/Y') }} - {{ $travelRequest->return_date->format('d/m/Y') }}</span>
                </div>
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
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Destino
                    </span>
                    <span class="details-value">{{ $travelRequest->destination_city }}, {{ $travelRequest->destinationCountry->name }}</span>
                </div>
                @if($travelRequest->advance_deposit_amount)
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Anticipo Depositado
                    </span>
                    <span class="details-value"><strong>${{ number_format($travelRequest->advance_deposit_amount, 2) }}</strong></span>
                </div>
                @endif
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Depositado por
                    </span>
                    <span class="details-value">{{ $travelRequest->advanceDepositMadeByUser->name }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Fecha de Depósito
                    </span>
                    <span class="details-value">{{ $travelRequest->advance_deposit_made_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Estado
                    </span>
                    <span class="details-value">
                        <span class="status-badge">Pendiente de Verificación</span>
                    </span>
                </div>
            </div>

            @if($travelRequest->advance_deposit_notes)
            <div class="details-box">
                <h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 500;">Notas del Depósito</h3>
                <p style="margin: 0; color: #666666; font-style: italic;">{{ $travelRequest->advance_deposit_notes }}</p>
            </div>
            @endif

            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ $viewUrl }}" class="cta-button">
                    Ver Detalles de la Solicitud
                </a>
            </div>

            <div class="divider"></div>

            <div class="process-section">
                <h3>Próximos Pasos</h3>
                <ul>
                    <li>Realice su viaje según lo planificado</li>
                    <li>Conserve todos los comprobantes de gastos (facturas, recibos, etc.)</li>
                    <li>Al regresar, suba los comprobantes al sistema para verificación</li>
                    <li>El equipo de contabilidad revisará y procesará los reembolsos correspondientes</li>
                </ul>
            </div>

            <div class="message">
                <p><strong>Nota importante:</strong> Es fundamental que conserve todos los comprobantes originales de sus gastos durante el viaje. Estos serán necesarios para la verificación final y el cálculo de reembolsos.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>Sistema Corporativo de Gestión de Viajes</p>
            <p>Este es un mensaje automático. Por favor, no responda a este correo.</p>
            <p style="margin-top: 20px;">
                © {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
