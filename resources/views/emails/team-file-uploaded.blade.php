<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Nuevo Archivo Adjunto</title>
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
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
        .file-box {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 2px solid #3b82f6;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .file-box h2 {
            margin: 0 0 10px 0;
            color: #1e40af;
            font-size: 24px;
        }
        .file-box p {
            margin: 5px 0;
            color: #1d4ed8;
            font-weight: 500;
        }
        .file-info {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
            <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDI2LjAuMiwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCA0MzkuMSAxMDYuNiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDM5LjEgMTA2LjY7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4KCS5zdDB7ZmlsbDojODk3MDUzO30KPC9zdHlsZT4KPGc+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNDM0LjUsMTVoMC42bDEsMS42aDAuN2wtMS0xLjdjMC41LTAuMSwwLjgtMC42LDAuOC0xLjFjMC0wLjctMC41LTEuMi0xLjItMS4yaC0xLjV2NGgwLjZWMTV6IE00MzQuNSwxMy4xCgkJaDAuOWMwLjMsMCwwLjYsMC4zLDAuNiwwLjdzLTAuMywwLjctMC42LDAuN2gtMC45VjEzLjF6Ii8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNDM1LjIsMTguNmMyLjIsMCwzLjktMS44LDMuOS0zLjlzLTEuOC0zLjktMy45LTMuOWMtMi4yLDAtMy45LDEuOC0zLjksMy45UzQzMywxOC42LDQzNS4yLDE4LjYgTTQzNS4yLDExLjIKCQljMS45LDAsMy40LDEuNSwzLjQsMy40cy0xLjUsMy40LTMuNCwzLjRzLTMuNC0xLjUtMy40LTMuNEM0MzEuOCwxMi43LDQzMy4zLDExLjIsNDM1LjIsMTEuMiIvPgoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTEyNC45LDU3LjRjMC0yNy4yLTEzLjYtNDkuMi0zMC4zLTQ5LjJzLTMwLjMsMjItMzAuMyw0OS4yczEzLjYsNDkuMiwzMC4zLDQ5LjIKCQlDMTExLjMsMTA2LjYsMTI0LjksODQuNiwxMjQuOSw1Ny40IE0xMDEuNiwxMDMuMWMtOC4yLDEuMy0xNy45LTE4LjItMjEuOC00My40Yy0zLjktMjUuMi0wLjQtNDYuNyw3LjctNDcuOQoJCWM4LjItMS4zLDE3LjksMTguMiwyMS44LDQzLjRDMTEzLjMsODAuNCwxMDkuOCwxMDEuOCwxMDEuNiwxMDMuMSIvPgoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTQwOC4yLDguMmMtMTYuNywwLTMwLjMsMjItMzAuMyw0OS4yczEzLjYsNDkuMiwzMC4zLDQ5LjJjMTYuNywwLDMwLjMtMjIsMzAuMy00OS4yUzQyNC45LDguMiw0MDguMiw4LjIKCQkgTTQxNS4yLDEwMy4xYy04LjIsMS4zLTE3LjktMTguMi0yMS44LTQzLjRTMzkzLDEzLDQwMS4xLDExLjhjOC4yLTEuMywxNy45LDE4LjIsMjEuOCw0My40QzQyNi44LDgwLjQsNDIzLjMsMTAxLjgsNDE1LjIsMTAzLjEiLz4KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik01OS4xLDkyLjNjMC4xLTAuMiwwLTAuNC0wLjEtMC42bC0xLTAuOGMtMC4yLTAuMi0wLjUtMC4yLTAuNiwwYy0xLjUsMS40LTguNCw3LTE4LjQtMC40CgkJQzI1LDgwLjIsMjEuNCw1OC4xLDIxLjQsNTguMWMtNC42LTI2LjItMC42LTQyLjgsOC44LTQ1LjJjMy43LTAuOSw3LjUsMiw3LjUsMmMyLjQsMi4xLDQuNyw0LjYsNi42LDcuMmMyLjgsMy44LDUuMSw3LjksNy4xLDExLjgKCQlsMS45LDMuOGMwLjEsMC4yLDAuMywwLjMsMC42LDAuMmwxLjgtMC43YzAuMi0wLjEsMC4zLTAuMiwwLjMtMC40bC0xLjEtMjQuNGMwLTAuNi0wLjItMS43LTEtMS45QzUwLjYsOS44LDQxLDguNCw0MSw4LjQKCQljLTExLTEtMjAuMiwyLjYtMjcsMTEuNWMwLDAtMTIuMiwxNS40LTEwLjksMzUuOEM0LjksODMuOSwxOS4yLDEwNywzOSwxMDYuOEM1MS40LDEwNi43LDU4LDk0LjQsNTkuMSw5Mi4zIi8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMzMyLjgsNy41YzAuMSwwLjMsMC41LDAuNCwwLjcsMC4zYzEuNy0wLjgsMy4yLTAuOCw1LjcsMEwzNDQsOWM0LjMsMSw2LjUtMS43LDcuMi00LjcKCQljMC4xLTAuMi0wLjEtMC41LTAuMy0wLjZsLTEtMC41Yy0wLjItMC4xLTAuNS0wLjEtMC42LDAuMWMtMS43LDEuNC0zLjMsMS44LTYsMWMtMS44LTAuNi0zLjctMS4xLTUuNS0xLjZjMCwwLTMuNi0wLjktNS43LDMKCQljLTAuMSwwLjItMC4xLDAuNCwwLDAuNUwzMzIuOCw3LjV6Ii8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMjQwLjEsMzcuOGwwLjktMC41YzAuNC0wLjIsMC42LTAuNiwwLjYtMWMtMC41LTguMS0wLjgtMTYuMy0xLjMtMjQuNWMtMC4xLTEuNS0xLjMtMi42LTIuOC0yLjZoLTI0LjFoLTI0LjEKCQljLTEuNSwwLTIuNywxLjEtMi44LDIuNmMtMC41LDguMi0wLjgsMTYuNS0xLjMsMjQuN2MwLDAuNCwwLjIsMC44LDAuNiwxbDEsMC41YzAuNSwwLjMsMS4xLDAuMSwxLjQtMC41bDExLjUtMjMuNwoJCWMwLjEtMC4xLDAuMi0wLjIsMC4yLTAuM2MxLjYtMi43LDUuMy0wLjcsNS43LDEuOGMwLjIsMS4zLDAuMywyLjYsMC4zLDMuOWMwLDIwLjgsMC4xLDI1LjcsMCw0Ni41YzAsNS4xLTAuNSwyNi4xLTAuOSwzMS4yCgkJYy0wLjMsMi44LTEuMSw0LjUtMy40LDUuOWMtMC41LDAuMy0wLjYsMC45LTAuNCwxLjRsMC42LDEuMWMwLjIsMC40LDAuNiwwLjYsMSwwLjZoMTAuN2gxMC43YzAuNCwwLDAuOC0wLjIsMS0wLjZsMC42LTEuMQoJCWMwLjItMC41LDAuMS0xLjEtMC40LTEuNGMtMi4zLTEuNS0zLjEtMy4xLTMuNC01LjljLTAuNS01LjEtMC45LTI2LjEtMC45LTMxLjJjLTAuMS0yMC44LDAtMjUuNywwLTQ2LjVjMC0xLjMsMC4xLTIuNiwwLjMtMy45CgkJYzAuMy0yLjUsNC4xLTQuNSw5LjctMS44YzAuMSwwLjEsMC4yLDAuMiwwLjIsMC4zbDExLjUsMjMuNUMyMzguOSwzNy45LDIzOS42LDM4LjEsMjQwLjEsMzcuOCIvPgoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTI2OS4zLDYxLjZjMC0yLjMsMC4zLTIuNSwyLjctMi41YzUuNCwwLjEsMTAuNSwxLDE0LjUsNS4yYzIuMSwyLjIsMy4zLDQuNCw0LjQsNy4yCgkJYzAuMSwwLjMsMC4zLDAuNSwwLjUsMC43bDEsMC41YzAuNSwwLjIsMC44LTAuNCwwLjgtMC44VjU3LjZsMCwwVjQzLjNjMC0wLjQtMC4zLTEtMC44LTAuOGwtMSwwLjVjLTAuMiwwLjItMC40LDAuNC0wLjUsMC43CgkJYy0xLDIuOC0yLjIsNS00LjQsNy4yYy00LDQuMS05LDUuMS0xNC41LDUuMmMtMi4zLDAtMi43LTAuMi0yLjctMi41YzAtMS40LDAtMi43LDAtNC4xYzAtNy41LDAtMTUuNywwLjItMjMuMwoJCWMwLDAtMC4xLTEyLjYsNy43LTE0LjFjMTAuNC0xLjksMTQuMyw5LDE0LjMsOWMzLjIsNC42LDUuMSwxMS4xLDcsMTYuNGMwLjIsMC40LDAuNywwLjYsMS4xLDAuNGwxLjMtMC43YzAuMy0wLjEsMC41LTAuNCwwLjQtMC44CgkJYy0wLjYtOC4zLTEuMS0xNi42LTEuNy0yNC45Yy0wLjEtMS4zLTEuMi0yLjQtMi41LTIuNGgtNDRjLTAuMywwLTAuNiwwLjItMC43LDAuNGwtMSwxLjljLTAuMSwwLjItMC4xLDAuNSwwLDAuOAoJCWMxLjMsMi4xLDEuOCw0LjUsMi4xLDcuMWMwLjMsMy4yLDAuNiw2LjQsMC42LDkuNmMwLjEsMTYsMC4xLDMyLjMsMC4xLDQ4LjNjLTAuMSw0LjEtMC4zLDEwLjctMC40LDE0LjhjLTAuMiwzLjgtMC41LDcuNy0yLjYsMTAuNwoJCWMtMC4yLDAuMi0wLjIsMC42LTAuMSwwLjhsMC45LDEuN2MwLjEsMC4zLDAuNCwwLjQsMC43LDAuNGg0NC41YzEuMywwLDIuMy0wLjksMi41LTIuMmMxLTcuNCwyLTE0LjksMi45LTIyLjMKCQljMC0wLjMtMC4xLTAuNi0wLjQtMC44bC0xLjQtMC43Yy0wLjQtMC4yLTAuOSwwLTEuMSwwLjRjLTEuNiwzLjQtMy4yLDguMS01LDExLjNjLTIuNCw0LjQtNS44LDguNC0xMC43LDEwLjMKCQljLTcuMSwyLjgtMTMuOSwxLjMtMTQuMi03LjdMMjY5LjMsNjEuNnoiLz4KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xNzIuNyw2OS40Yy02LjItOS45LTEyLjctMTkuNi0xOS4xLTI5LjVjLTMtNC42LTUuNy05LjUtNi44LTE1Yy0wLjctMy4yLTAuOC02LjQsMC43LTkuNQoJCWMyLjEtNC4xLDYuMy01LDkuOC0yYzAsMCw3LjYsNS42LDEzLjcsMjQuM2MwLjEsMC40LDAuNywwLjYsMS4xLDAuNGwxLjMtMC43YzAuMy0wLjEsMC40LTAuNCwwLjQtMC44bC0xLTI0YzAtMC43LTAuNS0xLjgtMS42LTIuMQoJCWMtNi41LTEuNC0xMi40LTMuMS0xOS41LTIuMUMxNDAuOSwxMCwxMzMuNCwxOC4yLDEzMywyOWMtMC4zLDYuNSwyLDEyLjIsNS40LDE3LjVjNS44LDksMTEuNywxNy45LDE3LjUsMjYuOAoJCWMyLjMsMy42LDQuNSw3LjIsNi40LDExYzEuOCwzLjYsMi44LDcuNSwyLjEsMTEuN2MtMS4yLDYuNy03LjcsOS41LTEzLjEsNS4zYy0yLjMtMS44LTQuMy00LjItNi4xLTYuNmMtNC4zLTUuNy02LjktMTMuNS04LjgtMTguOAoJCWMtMC4xLTAuNC0wLjUtMC42LTAuOS0wLjVjLTAuMywwLTAuNiwwLjEtMC45LDAuMWMtMC40LDAtMC43LDAuNC0wLjcsMC44bDAuOSwyMy4zYzAuMSwyLjMsMC45LDMuNCwzLDQuMmM5LjQsMy41LDE5LDMuOCwyOC40LDAuNQoJCWMxMC45LTMuOCwxNS4zLTEzLDExLjctMjRDMTc2LjgsNzYuNSwxNzQuOSw3Mi44LDE3Mi43LDY5LjQiLz4KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0zNzAuNSw5LjhjLTAuMS0wLjMtMC40LTAuNC0wLjctMC40aC0xMC45Yy0wLjMsMC0wLjYsMC4yLTAuNywwLjRsLTAuOCwxLjdjLTAuMiwwLjMtMC4xLDAuNywwLjIsMS4xCgkJYzEuOCwxLjUsMy44LDMuNyw0LjIsNi4xYzAsMCwwLjMsMy44LDAuMyw4LjZ2MzkuOWMwLDAuOC0xLjEsMS4xLTEuNSwwLjRsLTMzLjgtNTYuOGMtMC40LTAuOC0xLjItMS40LTIuMS0xLjRoLTEwLjkKCQljLTAuNCwwLTAuNywwLjItMC45LDAuNWwtMC43LDEuM2MtMC4yLDAuNC0wLjEsMC45LDAuMiwxLjJjMS43LDEuNSwyLjUsMi4yLDIuOCw0LjljMC44LDcuMSwxLDE2LjQsMSwxNi40bDAuMiwxNC4ybDAuMyw0MC4xCgkJYy0wLjEsMi41LTAuMSw2LjYtMC4yLDguNmMtMC4zLDMuMi0yLjQsNC4yLTQuNSw2LjFjLTAuMywwLjMtMC4zLDAuNy0wLjIsMWwwLjksMS43YzAuMSwwLjMsMC40LDAuNCwwLjcsMC40aDEwLjkKCQljMC4zLDAsMC42LTAuMiwwLjctMC40bDAuOC0xLjdjMC4yLTAuMywwLjEtMC43LTAuMi0xYy0xLjgtMS41LTMuOC0zLjctNC4yLTYuMWMwLDAtMC4zLTMuOC0wLjMtOC42bC0wLjUtNTMuMgoJCWMwLTAuOSwxLjItMS4zLDEuNy0wLjVsNDMsNzIuM2MwLjEsMC4yLDAuMywwLjMsMC41LDAuM2gwLjdjMC4zLDAsMC41LTAuMiwwLjUtMC41bC0wLjYtNzkuMWMwLjEtMi41LDAuMS02LjYsMC4yLTguNgoJCWMwLjMtMy4yLDIuNC00LjIsNC41LTYuMWMwLjMtMC4zLDAuMy0wLjcsMC4yLTFMMzcwLjUsOS44eiIvPgo8L2c+Cjwvc3ZnPgo=" alt="{{ config('app.name') }} Logo">
            <h1>{{ config('app.name') }}</h1>
            <p>Sistema de Gestión de Viajes</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                ¡Hola {{ $travelRequest->user->name }}! 👋
            </div>

            <div class="file-box">
                <h2>📄 Nuevo Archivo Adjunto</h2>
                <p>Se ha adjuntado un nuevo archivo a tu solicitud de viaje</p>
                <div class="file-info">
                    <p><strong>Tipo de archivo:</strong> {{ $attachmentType }}</p>
                    <p><strong>Nombre del archivo:</strong> {{ $fileName }}</p>
                    <p><strong>Subido por:</strong> {{ $uploaderName }}</p>
                </div>
            </div>

            <div class="message">
                <p>Te informamos que el equipo de <strong>{{ $uploaderTeam }}</strong> ha adjuntado un nuevo archivo a tu solicitud de viaje <strong>{{ $travelRequest->folio }}</strong>.</p>
                
                <p>Este archivo ha sido agregado como parte del proceso de gestión de tu viaje. Te recomendamos revisarlo para mantener toda la información actualizada sobre tu solicitud.</p>
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
                    <span class="details-label">📄 Tipo de Archivo:</span>
                    <span class="details-value">{{ $attachmentType }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">👤 Subido por:</span>
                    <span class="details-value">{{ $uploaderName }}</span>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $viewUrl }}" class="cta-button">
                    📄 Ver Solicitud y Archivos
                </a>
            </div>

            <div class="divider"></div>

            <div class="message">
                <p>📋 <strong>¿Qué hacer a continuación?</strong></p>
                <ul style="color: #4b5563; padding-left: 20px;">
                    <li><strong>Revisar archivo:</strong> Accede a tu solicitud para ver el nuevo archivo adjunto</li>
                    <li><strong>Verificar información:</strong> Asegúrate de que toda la información esté correcta</li>
                    <li><strong>Contactar si es necesario:</strong> Si tienes dudas sobre el archivo, contacta al equipo de {{ $uploaderTeam }}</li>
                </ul>
            </div>

            <div class="message" style="background-color: #dbeafe; padding: 15px; border-radius: 8px; margin-top: 20px;">
                <p style="margin: 0; color: #1e40af;">
                    <strong>💡 Recordatorio:</strong> Mantén todos los documentos de tu viaje organizados y revisa regularmente tu solicitud para estar al tanto de cualquier actualización.
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