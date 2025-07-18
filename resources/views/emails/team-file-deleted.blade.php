<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Archivo Eliminado</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .alert-box {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .alert-title {
            font-size: 18px;
            font-weight: 600;
            color: #dc2626;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-message {
            color: #7f1d1d;
            line-height: 1.6;
        }
        .details-section {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .details-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .details-label {
            font-weight: 600;
            color: #6b7280;
            flex-shrink: 0;
            margin-right: 15px;
        }
        .details-value {
            color: #374151;
            text-align: right;
            word-break: break-word;
        }
        .reason-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .reason-title {
            font-weight: 600;
            color: #d97706;
            margin-bottom: 8px;
        }
        .reason-text {
            color: #92400e;
            font-style: italic;
            line-height: 1.5;
        }
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            transition: transform 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        .footer {
            background-color: #f3f4f6;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .highlight {
            background-color: #fef3c7;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🗑️ Archivo Eliminado</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Alert -->
            <div class="alert-box">
                <div class="alert-title">
                    <span>⚠️</span>
                    Archivo Removido de tu Solicitud
                </div>
                <div class="alert-message">
                    El equipo de <strong>{{ $deleterTeam }}</strong> ha eliminado un archivo de tu solicitud de viaje <span class="highlight">{{ $travelRequest->folio }}</span>.
                </div>
            </div>

            <!-- Travel Request Details -->
            <div class="details-section">
                <div class="details-title">📋 Detalles de la Solicitud</div>
                <div class="details-row">
                    <span class="details-label">📄 Folio:</span>
                    <span class="details-value">{{ $travelRequest->folio }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">👤 Solicitante:</span>
                    <span class="details-value">{{ $travelRequest->user->name }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">🎯 Destino:</span>
                    <span class="details-value">{{ $travelRequest->destination_city }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">📅 Fecha de Viaje:</span>
                    <span class="details-value">{{ $travelRequest->start_date->format('d/m/Y') }} - {{ $travelRequest->end_date->format('d/m/Y') }}</span>
                </div>
            </div>

            <!-- File Details -->
            <div class="details-section">
                <div class="details-title">🗂️ Archivo Eliminado</div>
                <div class="details-row">
                    <span class="details-label">📎 Tipo de Archivo:</span>
                    <span class="details-value">{{ $attachmentType }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">📄 Nombre del Archivo:</span>
                    <span class="details-value">{{ $fileName }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">👤 Eliminado por:</span>
                    <span class="details-value">{{ $deleterName }} ({{ ucfirst($deleterTeam) }})</span>
                </div>
                <div class="details-row">
                    <span class="details-label">⏰ Fecha de eliminación:</span>
                    <span class="details-value">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            @if($reason)
            <!-- Reason -->
            <div class="reason-box">
                <div class="reason-title">💭 Motivo de la eliminación:</div>
                <div class="reason-text">{{ $reason }}</div>
            </div>
            @endif

            <!-- CTA Section -->
            <div class="cta-section">
                <a href="{{ $viewUrl }}" class="cta-button">
                    👁️ Ver Solicitud Completa
                </a>
            </div>

            <p style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 25px;">
                Si tienes alguna pregunta sobre esta eliminación, por favor contacta al equipo de {{ $deleterTeam }}.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Este es un mensaje automático del sistema de gestión de viajes.<br>
            Por favor, no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>