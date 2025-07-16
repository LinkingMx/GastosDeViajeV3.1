<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Viaje Creada</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 15px;
            filter: brightness(0) invert(1);
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .message {
            font-size: 16px;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .details-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            border-left: 4px solid #667eea;
        }
        
        .details-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #555;
            flex: 1;
        }
        
        .detail-value {
            color: #333;
            flex: 2;
            text-align: right;
        }
        
        .folio {
            background-color: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .status {
            background-color: #28a745;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        
        .footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 25px 20px;
        }
        
        .footer-logo {
            max-width: 100px;
            height: auto;
            margin-bottom: 10px;
            filter: brightness(0) invert(1);
        }
        
        .footer p {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .footer .company {
            font-weight: 600;
            opacity: 1;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .details-card {
                padding: 20px;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('images/costeno_logo.svg') }}" alt="Costeño Group Logo" class="logo">
            <h1>¡Solicitud Creada Exitosamente!</h1>
            <p>Tu solicitud de viaje ha sido registrada en el sistema</p>
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <div class="greeting">
                Hola, {{ $travelRequest->user->name }}
            </div>
            
            <div class="message">
                Has creado una nueva solicitud de Gastos de Viaje. A continuación encontrarás los detalles de tu solicitud:
            </div>
            
            <!-- Details Card -->
            <div class="details-card">
                <div class="details-title">Detalles de la Solicitud</div>
                
                <div class="detail-row">
                    <span class="detail-label">Folio:</span>
                    <span class="detail-value">
                        <span class="folio">{{ $travelRequest->folio }}</span>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Fecha de Creación:</span>
                    <span class="detail-value">{{ $travelRequest->created_at->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY [a las] HH:mm') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Destino:</span>
                    <span class="detail-value">
                        {{ $travelRequest->destination_city }}
                        @if($travelRequest->destinationCountry)
                            , {{ $travelRequest->destinationCountry->name }}
                        @endif
                    </span>
                </div>
                
                @if($travelRequest->departure_date)
                <div class="detail-row">
                    <span class="detail-label">Fecha de Salida:</span>
                    <span class="detail-value">{{ $travelRequest->departure_date->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
                </div>
                @endif
                
                @if($travelRequest->return_date)
                <div class="detail-row">
                    <span class="detail-label">Fecha de Regreso:</span>
                    <span class="detail-value">{{ $travelRequest->return_date->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
                </div>
                @endif
                
                <div class="detail-row">
                    <span class="detail-label">Estado:</span>
                    <span class="detail-value">
                        <span class="status">{{ ucfirst($travelRequest->status_display) }}</span>
                    </span>
                </div>
            </div>
            
            <div class="message">
                Tu solicitud ha sido guardada como <strong>borrador</strong>. Puedes continuar editándola hasta que decidas enviarla para autorización.
            </div>
            
            <div class="message">
                Recibirás notificaciones por correo electrónico a medida que tu solicitud avance en el proceso de autorización.
            </div>
            
            <!-- Call to Action -->
            <div class="cta-section">
                <a href="{{ config('app.url') }}/admin/travel-requests/{{ $travelRequest->id }}" class="cta-button">
                    Ver Solicitud
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <img src="{{ asset('images/costeno_logo.svg') }}" alt="Costeño Group Logo" class="footer-logo">
            <p class="company">Gastos de Viaje by Costeño Group</p>
            <p>Sistema de Gestión de Solicitudes de Viaje</p>
        </div>
    </div>
</body>
</html>