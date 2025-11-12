<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Solicitud de Viaje Pendiente de Autorización</title>
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
            border: 1px solid #897053;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #f8f6f3;
            color: #897053;
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
        .highlight-box {
            background-color: #fafafa;
            border-left: 3px solid #D4AF37;
            padding: 16px;
            margin: 20px 0;
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
            <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDI2LjAuMiwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCA0MzkuMSAxMDYuNiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDM5LjEgMTA2LjY7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4KCS5zdDB7ZmlsbDojODk3MDUzO30KPC9zdHlsZT4KPGc+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNDM0LjUsMTVoMC42bDEsMS42aDAuN2wtMS0xLjdjMC41LTAuMSwwLjgtMC42LDAuOC0xLjFjMC0wLjctMC41LTEuMi0xLjItMS4yaC0xLjV2NGgwLjZWMTV6IE00MzQuNSwxMy4xCgkJaDAuOWMwLjMsMCwwLjYsMC4zLDAuNiwwLjdzLTAuMywwLjctMC42LDAuN2gtMC45VjEzLjF6Ii8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNDM1LjIsMTguNmMyLjIsMCwzLjktMS44LDMuOS0zLjlzLTEuOC0zLjktMy45LTMuOWMtMi4yLDAtMy45LDEuOC0zLjksMy45UzQzMywxOC42LDQzNS4yLDE4LjYgTTQzNS4yLDExLjIKCQljMS45LDAsMy40LDEuNSwzLjQsMy40cy0xLjUsMy40LTMuNCwzLjRzLTMuNC0xLjUtMy40LTMuNEM0MzEuOCwxMi43LDQzMy4zLDExLjIsNDM1LjIsMTEuMiIvPgoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTEyNC45LDU3LjRjMC0yNy4yLTEzLjYtNDkuMi0zMC4zLTQ5LjJzLTMwLjMsMjItMzAuMyw0OS4yczEzLjYsNDkuMiwzMC4zLDQ5LjIKCQlDMTExLjMsMTA2LjYsMTI0LjksODQuNiwxMjQuOSw1Ny40IE0xMDEuNiwxMDMuMWMtOC4yLDEuMy0xNy45LTE4LjItMjEuOC00My40Yy0zLjktMjUuMi0wLjQtNDYuNyw3LjctNDcuOQoJCWM4LjItMS4zLDE3LjksMTguMiwyMS44LDQzLjRDMTEzLjMsODAuNCwxMDkuOCwxMDEuOCwxMDEuNiwxMDMuMSIvPgoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTQwOC4yLDguMmMtMTYuNywwLTMwLjMsMjItMzAuMyw0OS4yczEzLjYsNDkuMiwzMC4zLDQ5LjJjMTYuNywwLDMwLjMtMjIsMzAuMy00OS4yUzQyNC45LDguMiw0MDguMiw4LjIKCQkgTTQxNS4yLDEwMy4xYy04LjIsMS4zLTE3LjktMTguMi0yMS44LTQzLjRTMzkzLDEzLDQwMS4xLDExLjhjOC4yLTEuMywxNy45LDE4LjIsMjEuOCw0My40QzQyNi44LDgwLjQsNDIzLjMsMTAxLjgsNDE1LjIsMTAzLjEiLz4KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik01OS4xLDkyLjNjMC4xLTAuMiwwLTAuNC0wLjEtMC42bC0xLTAuOGMtMC4yLTAuMi0wLjUtMC4yLTAuNiwwYy0xLjUsMS40LTguNCw3LTE4LjQtMC40CgkJQzI1LDgwLjIsMjEuNCw1OC4xLDIxLjQsNTguMWMtNC42LTI2LjItMC42LTQyLjgsOC44LTQ1LjJjMy43LTAuOSw3LjUsMiw3LjUsMmMyLjQsMi4xLDQuNyw0LjYsNi42LDcuMmMyLjgsMy44LDUuMSw3LjksNy4xLDExLjgKCQlsMS45LDMuOGMwLjEsMC4yLDAuMywwLjMsMC42LDAuMmwxLjgtMC43YzAuMi0wLjEsMC4zLTAuMiwwLjMtMC40bC0xLjEtMjQuNGMwLTAuNi0wLjItMS43LTEtMS45QzUwLjYsOS44LDQxLDguNCw0MSw4LjQKCQljLTExLTEtMjAuMiwyLjYtMjcsMTEuNWMwLDAtMTIuMiwxNS40LTEwLjksMzUuOEM0LjksODMuOSwxOS4yLDEwNywzOSwxMDYuOEM1MS40LDEwNi43LDU4LDk0LjQsNTkuMSw5Mi4zIi8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMzMyLjgsNy41YzAuMSwwLjMsMC41LDAuNCwwLjcsMC4zYzEuNy0wLjgsMy4yLTAuOCw1LjcsMEwzNDQsOWM0LjMsMSw2LjUtMS43LDcuMi00LjcKCQljMC4xLTAuMi0wLjEtMC41LTAuMy0wLjZsLTEtMC41Yy0wLjItMC4xLTAuNS0wLjEtMC42LDAuMWMtMS43LDEuNC0zLjMsMS44LTYsMWMtMS44LTAuNi0zLjctMS4xLTUuNS0xLjZjMCwwLTMuNi0wLjktNS43LDMKCQljLTAuMSwwLjItMC4xLDAuNCwwLDAuNUwzMzIuOCw3LjV6Ii8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMjQwLjEsMzcuOGwwLjktMC41YzAuNC0wLjIsMC42LTAuNiwwLjYtMWMtMC41LTguMS0wLjgtMTYuMy0xLjMtMjQuNWMtMC4xLTEuNS0xLjMtMi42LTIuOC0yLjZoLTI0LjFoLTI0LjEKCQljLTEuNSwwLTIuNywxLjEtMi44LDIuNmMtMC41LDguMi0wLjgsMTYuNS0xLjMsMjQuN2MwLDAuNCwwLjIsMC44LDAuNiwxbDEsMC41YzAuNSwwLjMsMS4xLDAuMSwxLjQtMC41bDExLjUtMjMuNwoJCWMwLjEtMC4xLDAuMi0wLjIsMC4yLTAuM2MxLjYtMi43LDUuMy0wLjcsNS43LDEuOGMwLjIsMS4zLDAuMywyLjYsMC4zLDMuOWMwLDIwLjgsMC4xLDI1LjcsMCw0Ni41YzAsNS4xLTAuNSwyNi4xLTAuOSwzMS4yCgkJYy0wLjMsMi44LTEuMSw0LjUtMy40LDUuOWMtMC41LDAuMy0wLjYsMC45LTAuNCwxLjRsMC42LDEuMWMwLjIsMC40LDAuNiwwLjYsMSwwLjZoMTAuN2gxMC43YzAuNCwwLDAuOC0wLjIsMS0wLjZsMC42LTEuMQoJCWMwLjItMC41LDAuMS0xLjEtMC40LTEuNGMtMi4zLTEuNS0zLjEtMy4xLTMuNC01LjljLTAuNS01LjEtMC45LTI2LjEtMC45LTMxLjJjLTAuMS0yMC44LDAtMjUuNywwLTQ2LjVjMC0xLjMsMC4xLTIuNiwwLjMtMy45CgkJYzAuMy0yLjUsNC4xLTQuNSw1LjctMS44YzAuMSwwLjEsMC4yLDAuMiwwLjIsMC4zbDExLjUsMjMuNUMyMzguOSwzNy45LDIzOS42LDM4LjEsMjQwLjEsMzcuOCIvPgoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTI2OS4zLDYxLjZjMC0yLjMsMC4zLTIuNSwyLj43LTIuNWM1LjQsMC4xLDEwLjUsMC4xLDE0LjUsNS4yYzIuMSwyLjIsMy4zLDQuNCw0LjQsNy4yCgkJYzAuMSwwLjMsMC4zLDAuNSwwLjUsMC43bDEsMC41YzAuNSwwLjIsMC44LTAuNCwwLjgtMC44VjU3LjZsMCwwVjQzLjNjMC0wLjQtMC4zLTEtMC44LTAuOGwtMSwwLjVjLTAuMiwwLjItMC40LDAuNC0wLjUsMC43CgkJYy0xLDIuOC0yLjIsNS00LjQsNy4yYy00LDQuMS05LDUuMS0xNC41LDUuMmMtMi4zLDAtMi43LTAuMi0yLjctMi41YzAtMS40LDAtMi43LDAtNC4xYzAtNy41LDAtMTUuNywwLjItMjMuMwoJCWMwLDAtMC4xLTEyLjYsNy43LTE0LjFjMTAuNC0xLjksMTQuMyw5LDE0LjMsOWMzLjIsNC42LDUuMSwxMS4xLDcsMTYuNGMwLjIsMC40LDAuNywwLjYsMS4xLDAuNGwxLjMtMC43YzAuMy0wLjEsMC41LTAuNCwwLjQtMC44CgkJYy0wLjYtOC4zLTEuMS0xNi42LTEuNy0yNC45Yy0wLjEtMS4zLTEuMi0yLjQtMi41LTIuNGgtNDRjLTAuMywwLTAuNiwwLjItMC43LDAuNGwtMSwxLjljLTAuMSwwLjItMC4xLDAuNSwwLDAuOAoJCWMxLjMsMi4xLDEuOCw0LjUsMi4xLDcuMWMwLjMsMy4yLDAuNiw2LjQsMC42LDkuNmMwLjEsMTYsMC4xLDMyLjMsMC4xLDQ4LjNjLTAuMSw0LjEtMC4zLDEwLjctMC40LDE0LjhjLTAuMiwzLjgtMC41LDcuNy0yLjYsMTAuNwoJCWMtMC4yLDAuMi0wLjIsMC42LTAuMSwwLjhsMC45LDEuN2MwLjEsMC4zLDAuNCwwLjQsMC43LDAuNGg0NC41YzEuMywwLDIuMy0wLjksMi41LTIuMmMxLTcuNCwyLTE0LjksMi45LTIyLjMKCQljMC0wLjMtMC4xLTAuNi0wLjQtMC44bC0xLjQtMC43Yy0wLjQtMC4yLTAuOSwwLTEuMSwwLjRjLTEuNiwzLjQtMy4yLDguMS01LDExLjNjLTIuNCw0LjQtNS44LDguNC0xMC43LDEwLjMKCQljLTcuMSwyLjgtMTMuOSwxLjMtMTQuMi03LjdMMjY5LjMsNjEuNnoiLz4KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xNzIuNyw2OS40Yy02LjItOS45LTEyLjctMTkuNi0xOS4xLTI5LjVjLTMtNC42LTUuNy05LjUtNi44LTE1Yy0wLjctMy4yLTAuOC02LjQsMC43LTkuNQoJCWMyLjEtNC4xLDYuMy01LDkuOC0yYzAsMCw3LjYsNS42LDEzLjcsMjQuM2MwLjEsMC40LDAuNywwLjYsMS4xLDAuNGwxLjMtMC43YzAuMy0wLjEsMC40LTAuNCwwLjQtMC44bC0xLTI0YzAtMC43LTAuNS0xLjgtMS42LTIuMQoJCWMtNi41LTEuNC0xMi40LTMuMS0xOS41LTIuMUMxNDAuOSwxMCwxMzMuNCwxOC4yLDEzMywyOWMtMC4zLDYuNSwyLDEyLjIsNS40LDE3LjVjNS44LDksMTEuNywxNy45LDE3LjUsMjYuOAoJCWMyLjMsMy42LDQuNSw3LjIsNi40LDExYzEuOCwzLjYsMi44LDcuNSwyLjEsMTEuN2MtMS4yLDYuNy03LjcsOS41LTEzLjEsNS4zYy0yLjMtMS44LTQuMy00LjItNi4xLTYuNmMtNC4zLTUuNy02LjktMTMuNS04LjgtMTguOAoJCWMtMC4xLTAuNC0wLjUtMC42LTAuOS0wLjVjLTAuMywwLTAuNiwwLjEtMC45LDAuMWMtMC40LDAtMC43LDAuNC0wLjcsMC44bDAuOSwyMy4zYzAuMSwyLjMsMC45LDMuNCwzLDQuMmM5LjQsMy41LDE5LDMuOCwyOC40LDAuNQoJCWMxMC45LTMuOCwxNS4zLTEzLDExLjctMjRDMTc2LjgsNzYuNSwxNzQuOSw3Mi44LDE3Mi43LDY5LjQiLz4KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0zNzAuNSw5LjhjLTAuMS0wLjMtMC40LTAuNC0wLjctMC40aC0xMC45Yy0wLjMsMC0wLjYsMC4yLTAuNywwLjRsLTAuOCwxLjdjLTAuMiwwLjMtMC4xLDAuNywwLjIsMAoJCWMxLjgsMS41LDMuOCwzLjcsNC4yLDYuMWMwLDAsMC4zLDMuOCwwLjMsOC42djM5LjljMCwwLjgtMS4xLDEuMS0xLjUsMC40bC0zMy44LTU2LjhjLTAuNC0wLjgtMS4yLTEuNC0yLjEtMS40aC0xMC45CgkJYy0wLjQsMC0wLjcsMC4yLTAuOSwwLjVsLTAuNywxLjNjLTAuMiwwLjQtMC4xLDAuOSwwLjIsMS4yYzEuNywxLjUsMi41LDIuMiwyLjgsNC45YzAuOCw3LjEsMSwxNi40LDEsMTYuNGwwLjIsMTQuMmwwLjMsNDAuMQoJCWMtMC4xLDIuNS0wLjEsNi42LTAuMiw4LjZjLTAuMywzLjItMi40LDQuMi00LjUsNi4xYy0wLjMsMC4zLTAuMywwLjctMC4yLDFsMC45LDEuN2MwLjEsMC4zLDAuNCwwLjQsMC43LDAuNGgxMC45CgkJYzAuMywwLDAuNi0wLjIsMC43LTAuNGwwLjgtMS43YzAuMi0wLjMsMC4xLTAuNy0wLjItMWMtMS44LTEuNS0zLjgtMy43LTQuMi02LjFjMCwwLTAuMy0zLjgtMC4zLTguNmwtMC41LTUzLjIKCQljMC0wLjksMS4yLTEuMywxLjctMC41bDQzLDcyLjNjMC4xLDAuMiwwLjMsMC4zLDAuNSwwLjNoMC43YzAuMywwLDAuNS0wLjIsMC41LTAuNWwtMC42LTc5LjFjMC4xLTIuNSwwLjEtNi42LDAuMi04LjYKCQljMC4zLTMuMiwyLjQtNC4yLDQuNS02LjFjMC4zLTAuMywwLjMtMC43LDAuMi0xTDM3MC41LDkuOHoiLz4KPC9nPgo8L3N2Zz4K" alt="{{ config('app.name') }} Logo">
            <h1>Solicitud Pendiente de Autorización</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Estimado/a {{ $travelRequest->authorizer->name }},
            </div>

            <div class="message">
                <p>Se ha recibido una solicitud de viaje que requiere su autorización.</p>
            </div>

            <div class="highlight-box">
                <p style="margin: 0;"><strong>Acción requerida:</strong> Autorización de solicitud de viaje</p>
            </div>

            <div class="details-box">
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Solicitante
                    </span>
                    <span class="details-value">{{ $travelRequest->user->name }}</span>
                </div>
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
                            <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Destino
                    </span>
                    <span class="details-value">{{ $travelRequest->destination_city }}, {{ $travelRequest->destinationCountry->name }}</span>
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
                            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Estado
                    </span>
                    <span class="details-value">
                        <span class="status-badge">Borrador</span>
                    </span>
                </div>
                @if($travelRequest->purpose)
                <div class="details-row">
                    <span class="details-label">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 6V18M12 6L8 10M12 6L16 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 18C12 18 17 14 17 10C17 6 12 2 12 2C12 2 7 6 7 10C7 14 12 18 12 18Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Motivo
                    </span>
                    <span class="details-value">{{ $travelRequest->purpose }}</span>
                </div>
                @endif
            </div>

            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ route('filament.admin.resources.travel-requests.view', $travelRequest) }}" class="cta-button">
                    Revisar y Autorizar Solicitud
                </a>
            </div>

            <div class="divider"></div>

            <div class="message">
                <p><strong>Nota:</strong> Esta solicitud requiere su autorización para continuar con el proceso. Por favor, revise los detalles y tome la acción correspondiente a la brevedad posible.</p>
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