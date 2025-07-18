<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Solicitud Autorizada</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            color: #374151;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header img {
            max-height: 60px;
            width: auto;
            margin-bottom: 20px;
            filter: brightness(0) invert(1);
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            margin-bottom: 30px;
            color: #4b5563;
        }
        .details-box {
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 24px;
            margin: 25px 0;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
        }
        .details-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .details-label {
            font-weight: 600;
            color: #374151;
            min-width: 140px;
        }
        .details-value {
            color: #6b7280;
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            background-color: #d1fae5;
            color: #065f46;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .footer p {
            margin: 8px 0;
        }
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 30px 0;
        }
        .success-box {
            background-color: #d1fae5;
            border: 1px solid #10b981;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .success-box p {
            margin: 0;
            color: #065f46;
            font-weight: 500;
        }
        @media (max-width: 600px) {
            .container {
                margin: 0 10px;
            }
            .header, .content {
                padding: 20px;
            }
            .details-row {
                flex-direction: column;
                text-align: left;
            }
            .details-value {
                text-align: left;
                margin-top: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDI2LjAuMiwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCA0MzkuMSAxMDYuNiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDM5LjEgMTA2LjY7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4KCS5zdDB7ZmlsbDojODk3MDUzO30KPC9zdHlsZT4KPGc+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNDM0LjUsMTVoMC42bDEsMS42aDAuN2wtMS0xLjdjMC41LTAuMSwwLjgtMC42LDAuOC0xLjFjMC0wLjctMC41LTEuMi0xLjItMS4yaC0xLjV2NGgwLjZWMTV6IE00MzQuNSwxMy4xCgkJaDAuOWMwLjMsMCwwLjYsMC4zLDAuNiwwLjdzLTAuMywwLjctMC42LDAuN2gtMC45VjEzLjF6Ii8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNDM1LjIsMTguNmMyLjIsMCwzLjktMS44LDMuOS0zLjlzLTEuOC0zLjktMy45LTMuOWMtMi4yLDAtMy45LDEuOC0zLjksMy45UzQzMywxOC42LDQzNS4yLDE4LjYgTTQzNS4yLDExLjIKCQljMS45LDAsMy40LDEuNSwzLjQsMy40cy0xLjUsMy40LTMuNCwzLjRzLTMuNC0xLjUtMy40LTMuNEM0MzEuOCwxMi43LDQzMy4zLDExLjIsNDM1LjIsMTEuMiIvPgoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTEyNC45LDU3LjRjMC0yNy4yLTEzLjYtNDkuMi0zMC4zLTQ5LjJzLTMwLjMsMjItMzAuMyw0OS4yczEzLjYsNDkuMiwzMC4zLDQ5LjIKCQlDMTExLjMsMTA2LjYsMTI0LjksODQuNiwxMjQuOSw1Ny40IE0xMDEuNiwxMDMuMWMtOC4yLDEuMy0xNy45LTE4LjItMjEuOC00My40Yy0zLjktMjUuMi0wLjQtNDYuNyw3LjctNDcuOQoJCWM4LjItMS4zLDE3LjksMTguMiwyMS44LDQzLjRDMTEzLjMsODAuNCwxMDkuOCwxMDEuOCwxMDEuNiwxMDMuMSIvPgoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTQwOC4yLDguMmMtMTYuNywwLTMwLjMsMjItMzAuMyw0OS4yczEzLjYsNDkuMiwzMC4zLDQ5LjJjMTYuNywwLDMwLjMtMjIsMzAuMy00OS4yUzQyNC45LDguMiw0MDguMiw4LjIKCQkgTTQxNS4yLDEwMy4xYy04LjIsMS4zLTE3LjktMTguMi0yMS44LTQzLjRTMzkzLDEzLDQwMS4xLDExLjhjOC4yLTEuMywxNy45LDE4LjIsMjEuOCw0My40QzQyNi44LDgwLjQsNDIzLjMsMTAxLjgsNDE1LjIsMTAzLjEiLz4KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik01OS4xLDkyLjNjMC4xLTAuMiwwLTAuNC0wLjEtMC42bC0xLTAuOGMtMC4yLTAuMi0wLjUtMC4yLTAuNiwwYy0xLjUsMS40LTguNCw3LTE4LjQtMC40CgkJQzI1LDgwLjIsMjEuNCw1OC4xLDIxLjQsNTguMWMtNC42LTI2LjItMC42LTQyLjgsOC44LTQ1LjJjMy43LTAuOSw3LjUsMiw3LjUsMmMyLjQsMi4xLDQuNyw0LjYsNi42LDcuMmMyLjgsMy44LDUuMSw3LjksNy4xLDExLjgKCQlsMS45LDMuOGMwLjEsMC4yLDAuMywwLjMsMC42LDAuMmwxLjgtMC43YzAuMi0wLjEsMC4zLTAuMiwwLjMtMC40bC0xLjEtMjQuNGMwLTAuNi0wLjItMS43LTEtMS45QzUwLjYsOS44LDQxLDguNCw0MSw4LjQKCQljLTExLTEtMjAuMiwyLjYtMjcsMTEuNWMwLDAtMTIuMiwxNS40LTEwLjksMzUuOEM0LjksODMuOSwxOS4yLDEwNywzOSwxMDYuOEM1MS40LDEwNi43LDU4LDk0LjQsNTkuMSw5Mi4zIi8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMzMyLjgsNy41YzAuMSwwLjMsMC41LDAuNCwwLjcsMC4zYzEuNy0wLjgsMy4yLTAuOCw1LjcsMEwzNDQsOWM0LjMsMSw2LjUtMS43LDcuMi00LjcKCQljMC4xLTAuMi0wLjEtMC41LTAuMy0wLjZsLTEtMC41Yy0wLjItMC4xLTAuNS0wLjEtMC42LDAuMWMtMS43LDEuNC0zLjMsMS44LTYsMWMtMS44LTAuNi0zLjctMS4xLTUuNS0xLjZjMCwwLTMuNi0wLjktNS43LDMKCQljLTAuMSwwLjItMC4xLDAuNCwwLDAuNUwzMzIuOCw3LjV6Ii8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMjQwLjEsMzcuOGwwLjktMC41YzAuNC0wLjIsMC42LTAuNiwwLjYtMWMtMC41LTguMS0wLjgtMTYuMy0xLjMtMjQuNWMtMC4xLTEuNS0xLjMtMi42LTIuOC0yLjZoLTI0LjFoLTI0LjEKCQljLTEuNSwwLTIuNywxLjEtMi44LDIuNmMtMC41LDguMi0wLjgsMTYuNS0xLjMsMjQuN2MwLDAuNCwwLjIsMC44LDAuNiwxbDEsMC41YzAuNSwwLjMsMS4xLDAuMSwxLjQtMC41bDExLjUtMjMuNwoJCWMwLjEtMC4xLDAuMi0wLjIsMC4yLTAuM2MxLjYtMi43LDUuMy0wLjcsNS43LDEuOGMwLjIsMS4zLDAuMywyLjYsMC4zLDMuOWMwLDIwLjgsMC4xLDI1LjcsMCw0Ni41YzAsNS4xLTAuNSwyNi4xLTAuOSwzMS4yCgkJYy0wLjMsMi44LTEuMSw0LjUtMy40LDUuOWMtMC41LDAuMy0wLjYsMC45LTAuNCwxLjRsMC42LDEuMWMwLjIsMC40LDAuNiwwLjYsMSwwLjZoMTAuN2gxMC43YzAuNCwwLDAuOC0wLjIsMS0wLjZsMC42LTEuMQoJCWMwLjItMC41LDAuMS0xLjEtMC40LTEuNGMtMi4zLTEuNS0zLjEtMy4xLTMuNC01LjljLTAuNS01LjEtMC45LTI2LjEtMC45LTMxLjJjLTAuMS0yMC44LDAtMjUuNywwLTQ2LjVjMC0xLjMsMC4xLTIuNiwwLjMtMy45CgkJYzAuMy0yLjUsNC4xLTQuNSw1LjctMS44YzAuMSwwLjEsMC4yLDAuMiwwLjIsMC4zbDExLjUsMjMuNUMyMzguOSwzNy45LDIzOS42LDM4LjEsMjQwLjEsMzcuOCIvPgoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTI2OS4zLDYxLjZjMC0yLjMsMC4zLTIuNSwyLj43LTIuNWM1LjQsMC4xLDEwLjUsMSwxNC41LDUuMmMyLjEsMi4yLDMuMyw0LjQsNC40LDcuMgoJCWMwLjEsMC4zLDAuMywwLjUsMC41LDAuN2wxLDAuNWMwLjUsMC4yLDAuOC0wLjQsMC44LTAuOFY1Ny42bDAsMFY0My4zYzAtMC40LTAuMy0xLTAuOC0wLjhsLTEsMC41Yy0wLjIsMC4yLTAuNCwwLjQtMC41LDAuNwoJCWMtMSwyLjgtMi4yLDUtNC40LDcuMmMtNCw0LjEtOSw1LjEtMTQuNSw1LjJjLTIuMywwLTIuNy0wLjItMi43LTIuNWMwLTEuNCwwLTIuNywwLTQuMWMwLTcuNSwwLTE1LjcsMC4yLTIzLjMKCQljMCwwLTAuMS0xMi42LDcuNy0xNC4xYzEwLjQtMS45LDE0LjMsOSwxNC4zLDljMy4yLDQuNiw1LjEsMTEuMSw3LDE2LjRjMC4yLDAuNCwwLjcsMC42LDEuMSwwLjRsMS4zLTAuN2MwLjMtMC4xLDAuNS0wLjQsMC40LTAuOAoJCWMtMC42LTguMy0xLjEtMTYuNi0xLjctMjQuOWMtMC4xLTEuMy0xLjItMi40LTIuNS0yLjRoLTQ0Yy0wLjMsMC0wLjYsMC4yLTAuNywwLjRsLTEsMS45Yy0wLjEsMC4yLTAuMSwwLjUsMCwwLjgKCQljMS4zLDIuMSwxLjgsNC41LDIuMSw3LjFjMC4zLDMuMiwwLjYsNi40LDAuNiw5LjZjMC4xLDE2LDAuMSwzMi4zLDAuMSw0OC4zYy0wLjEsNC4xLTAuMywxMC43LTAuNCwxNC44Yy0wLjIsMy44LTAuNSw3LjctMi42LDEwLjcKCQljLTAuMiwwLjItMC4yLDAuNi0wLjEsMC44bDAuOSwxLjdjMC4xLDAuMywwLjQsMC40LDAuNywwLjRoNDQuNWMxLjMsMCwyLjMtMC45LDIuNS0yLjJjMS03LjQsMi0xNC45LDIuOS0yMi4zCgkJYzAtMC4zLTAuMS0wLjYtMC40LTAuOGwtMS40LTAuN2MtMC40LTAuMi0wLjksMC0xLjEsMC40Yy0xLjYsMy40LTMuMiw4LjEtNSwxMS4zYy0yLjQsNC40LTUuOCw4LjQtMTAuNywxMC4zCgkJYy03LjEsMi44LTEzLjksMS4zLTE0LjItNy43TDI2OS4zLDYxLjZ6Ii8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMTcyLjcsNjkuNGMtNi4yLTkuOS0xMi43LTE5LjYtMTkuMS0yOS41Yy0zLTQuNi01LjctOS41LTYuOC0xNWMtMC43LTMuMi0wLjgtNi40LDAuNy05LjUKCQljMi4xLTQuMSw2LjMtNSw5LjgtMmMwLDAsNy42LDUuNiwxMy43LDI0LjNjMC4xLDAuNCwwLjcsMC42LDEuMSwwLjRsMS4zLTAuN2MwLjMtMC4xLDAuNC0wLjQsMC40LTAuOGwtMS0yNGMwLTAuNy0wLjUtMS44LTEuNi0yLjEKCQljLTYuNS0xLjQtMTIuNC0zLjEtMTkuNS0yLjFDMTQwLjksMTAsMTMzLjQsMTguMiwxMzMsMjljLTAuMyw2LjUsMiwxMi4yLDUuNCwxNy41YzUuOCw5LDExLjcsMTcuOSwxNy41LDI2LjgKCQljMi4zLDMuNiw0LjUsNy4yLDYuNCwxMWMxLjgsMy42LDIuOCw3LjUsMi4xLDExLjdjLTEuMiw2LjctNy43LDkuNS0xMy4xLDUuM2MtMi4zLTEuOC00LjMtNC4yLTYuMS02LjZjLTQuMy01LjctNi45LTEzLjUtOC44LTE4LjgKCQljLTAuMS0wLjQtMC41LTAuNi0wLjktMC41Yy0wLjMsMC0wLjYsMC4xLTAuOSwwLjFjLTAuNCwwLTAuNywwLjQtMC43LDAuOGwwLjksMjMuM2MwLjEsMi4zLDAuOSwzLjQsMyw0LjJjOS40LDMuNSwxOSwzLjgsMjguNCwwLjUKCQljMTAuOS0zLjgsMTUuMy0xMywxMS43LTI0QzE3Ni44LDc2LjUsMTc0LjksNzIuOCwxNzIuNyw2OS40Ii8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMzcwLjUsOS44Yy0wLjEtMC4zLTAuNC0wLjQtMC43LTAuNGgtMTAuOWMtMC4zLDAtMC42LDAuMi0wLjcsMC40bC0wLjgsMS43Yy0wLjIsMC4zLTAuMSwwLjcsMC4yLDEKCQljMS44LDEuNSwzLjgsMy43LDQuMiw2LjFjMCwwLDAuMywzLjgsMC4zLDguNnYzOS45YzAsMC44LTEuMSwxLjEtMS41LDAuNGwtMzMuOC01Ni44Yy0wLjQtMC44LTEuMi0xLjQtMi4xLTEuNGgtMTAuOQoJCWMtMC40LDAtMC43LDAuMi0wLjksMC41bC0wLjcsMS4zYy0wLjIsMC40LTAuMSwwLjksMC4yLDEuMmMxLjcsMS41LDIuNSwyLjIsMi44LDQuOWMwLjgsNy4xLDEsMTYuNCwxLDE2LjRsMC4yLDE0LjJsMC4zLDQwLjEKCQljLTAuMSwyLjUtMC4xLDYuNi0wLjIsOC42Yy0wLjMsMy4yLTIuNCw0LjItNC41LDYuMWMtMC4zLDAuMy0wLjMsMC43LTAuMiwxbDAuOSwxLjdjMC4xLDAuMywwLjQsMC40LDAuNywwLjRoMTAuOQoJCWMwLjMsMCwwLjYtMC4yLDAuNy0wLjRsMC44LTEuN2MwLjItMC4zLDAuMS0wLjctMC4yLTFjLTEuOC0xLjUtMy44LTMuNy00LjItNi4xYzAsMC0wLjMtMy44LTAuMy04LjZsLTAuNS01My4yCgkJYzAtMC45LDEuMi0xLjMsMS43LTAuNWw0Myw3Mi4zYzAuMSwwLjIsMC4zLDAuMywwLjUsMC4zaDAuN2MwLjMsMCwwLjUtMC4yLDAuNS0wLjVsLTAuNi03OS4xYzAuMS0yLjUsMC4xLTYuNiwwLjItOC42CgkJYzAuMy0zLjIsMi40LTQuMiw0LjUtNi4xYzAuMy0wLjMsMC4zLTAuNywwLjItMUwzNzAuNSw5Ljh6Ii8+CjwvZz4KPC9zdmc+Cg==" alt="{{ config('app.name') }} Logo">
            <h1>{{ config('app.name') }}</h1>
            <p>Sistema de Gestión de Viajes</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                ¡Hola {{ $travelRequest->user->name }}! 👋
            </div>

            <div class="success-box">
                <p>🎉 <strong>¡Excelentes noticias! Tu solicitud de viaje ha sido AUTORIZADA.</strong></p>
            </div>

            <div class="message">
                <p>Tu solicitud ha sido aprobada por <strong>{{ $travelRequest->authorizer->name }}</strong> y ahora pasa a revisión del equipo de viajes para la gestión de reservas y logística.</p>
                
                <p>El equipo de viajes se encargará de:</p>
                <ul style="color: #4b5563; padding-left: 20px;">
                    <li>Revisar los detalles de tu viaje</li>
                    <li>Gestionar las reservas necesarias (vuelos, hospedaje, etc.)</li>
                    <li>Coordinar la logística del viaje</li>
                    <li>Procesar el anticipo si es requerido</li>
                </ul>
            </div>

            <div class="details-box">
                <div class="details-row">
                    <span class="details-label">📋 Folio:</span>
                    <span class="details-value"><strong>{{ $travelRequest->folio }}</strong></span>
                </div>
                <div class="details-row">
                    <span class="details-label">🗓️ Fecha de Salida:</span>
                    <span class="details-value">{{ $travelRequest->departure_date->format('d/m/Y') }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">🏁 Fecha de Regreso:</span>
                    <span class="details-value">{{ $travelRequest->return_date->format('d/m/Y') }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">📍 Origen:</span>
                    <span class="details-value">{{ $travelRequest->origin_city }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">🎯 Destino:</span>
                    <span class="details-value">{{ $travelRequest->destination_city }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">✅ Autorizado por:</span>
                    <span class="details-value">{{ $travelRequest->authorizer->name }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">📅 Fecha de Autorización:</span>
                    <span class="details-value">{{ $travelRequest->authorized_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">📊 Estado Actual:</span>
                    <span class="details-value">
                        <span class="status-badge">Autorizada</span>
                    </span>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $viewUrl }}" class="cta-button">
                    📋 Ver Detalles de la Solicitud
                </a>
            </div>

            <div class="divider"></div>

            <div class="message">
                <p>📅 <strong>Próximos pasos:</strong></p>
                <ol style="color: #4b5563; padding-left: 20px;">
                    <li>El equipo de viajes revisará tu solicitud en las próximas horas</li>
                    <li>Recibirás notificaciones sobre el avance de las gestiones</li>
                    <li>Se te informará cuando las reservas estén confirmadas</li>
                    <li>Si aplica, se procesará el depósito del anticipo</li>
                </ol>
            </div>

            <div class="message" style="background-color: #fef3c7; padding: 15px; border-radius: 8px; margin-top: 20px;">
                <p style="margin: 0; color: #92400e;">
                    <strong>⚠️ Importante:</strong> No realices ninguna reserva por tu cuenta hasta recibir confirmación del equipo de viajes. Ellos se encargarán de todas las gestiones necesarias.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>Este correo fue enviado automáticamente desde nuestro sistema de gestión de viajes.</p>
            <p>Por favor, no respondas a este correo. Si necesitas ayuda, contacta a tu administrador.</p>
            <p style="margin-top: 20px; font-size: 12px; color: #9ca3af;">
                © {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.<br>
                Este mensaje es confidencial y está dirigido únicamente al destinatario.
            </p>
        </div>
    </div>
</body>
</html>