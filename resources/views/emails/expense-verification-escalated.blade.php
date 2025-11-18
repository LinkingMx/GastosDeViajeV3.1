<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Comprobación Escalada</title>
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
        .alert-box {
            background-color: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 16px;
            margin: 20px 0;
        }
        .alert-box p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
        }
        .notes-box {
            background-color: #fafafa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .notes-box h3 {
            margin: 0 0 12px 0;
            font-size: 16px;
            font-weight: 500;
            color: #000000;
        }
        .notes-box p {
            margin: 0;
            color: #000000;
            font-style: italic;
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
            <h1>Comprobación Escalada</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Estimado/a {{ $verification->creator->name }},
            </div>

            <div class="message">
                <p>Le informamos que su comprobación de gastos ha sido escalada para autorización por parte del Autorizador Mayor debido a consideraciones especiales en la revisión.</p>
            </div>

            <div class="alert-box">
                <p><strong>Su comprobación requiere autorización del Autorizador Mayor</strong></p>
            </div>

            <div class="details-box">
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 11H15M9 15H15M17 3H7C5.89543 3 5 3.89543 5 5V19C5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V5C19 3.89543 18.1046 3 17 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Folio
                    </span>
                    <span class="details-value"><strong>{{ $verification->folio }}</strong></span>
                </div>
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Escalado por
                    </span>
                    <span class="details-value">{{ $verification->approver->name }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 11H15M9 15H15M17 3H7C5.89543 3 5 3.89543 5 5V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V5C19 3.89543 18.1046 3 17 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Solicitud de Viaje
                    </span>
                    <span class="details-value">{{ $verification->travelRequest->folio }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Monto Total Comprobado
                    </span>
                    <span class="details-value"><strong>${{ number_format($verification->getTotalVerifiedAmount(), 2) }}</strong></span>
                </div>
            </div>

            @if($verification->approval_notes)
            <div class="notes-box">
                <h3>Comentarios del Revisor</h3>
                <p>{{ $verification->approval_notes }}</p>
            </div>
            @endif

            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ $url }}" class="cta-button">
                    Ver Comprobación Completa
                </a>
            </div>

            <div class="divider"></div>

            <div class="message">
                <p><strong>Próximos Pasos:</strong></p>
                <p>Su comprobación está pendiente de revisión y aprobación por el Autorizador Mayor. Le notificaremos una vez que se tome una decisión sobre su comprobación.</p>
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
