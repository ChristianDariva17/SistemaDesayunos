<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Ventas</title>
    <style>
        /* ==========================================
           CONFIGURACIÓN DE PÁGINA
           ========================================== */
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #1f2937;
            line-height: 1.4;
        }

        /* ==========================================
           HEADER PRINCIPAL
           ========================================== */
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
            padding: 0;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 8px;
            opacity: 0.95;
            line-height: 1.6;
        }

        .report-title {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-date {
            text-align: right;
            font-size: 8px;
            opacity: 0.95;
        }

        /* ==========================================
           PERIODO
           ========================================== */
        .period-banner {
            background-color: #dbeafe;
            border-left: 5px solid #3b82f6;
            padding: 12px 15px;
            margin-bottom: 15px;
            text-align: center;
        }

        .period-label {
            color: #1e40af;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .period-dates {
            color: #1e3a8a;
            font-size: 14px;
            font-weight: bold;
        }

        .period-days {
            color: #1e40af;
            font-size: 8px;
            margin-top: 3px;
        }

        /* ==========================================
           KPIs (4 TARJETAS)
           ========================================== */
        .kpi-grid {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .kpi-card {
            padding: 12px;
            text-align: center;
            border: 3px solid white;
            border-radius: 6px;
        }

        .kpi-green { background-color: #10b981; color: white; }
        .kpi-blue { background-color: #3b82f6; color: white; }
        .kpi-purple { background-color: #8b5cf6; color: white; }
        .kpi-orange { background-color: #f59e0b; color: white; }

        .kpi-icon {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .kpi-label {
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 5px;
            opacity: 0.95;
        }

        .kpi-value {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .kpi-subtitle {
            font-size: 7px;
            opacity: 0.9;
        }

        /* ==========================================
           ANÁLISIS POR ESTADO
           ========================================== */
        .status-section {
            background-color: #f9fafb;
            border: 2px solid #e5e7eb;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 3px solid #10b981;
        }

        .status-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .status-box {
            padding: 10px;
            text-align: center;
            border: 2px solid white;
            border-radius: 4px;
        }

        .status-completado {
            background-color: #d1fae5;
            border: 2px solid #10b981;
        }

        .status-pendiente {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
        }

        .status-cancelado {
            background-color: #fee2e2;
            border: 2px solid #ef4444;
        }

        .status-procesando {
            background-color: #dbeafe;
            border: 2px solid #3b82f6;
        }

        .status-number {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .status-completado .status-number { color: #059669; }
        .status-pendiente .status-number { color: #d97706; }
        .status-cancelado .status-number { color: #dc2626; }
        .status-procesando .status-number { color: #1d4ed8; }

        .status-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .status-amount {
            font-size: 10px;
            font-weight: bold;
            margin-top: 5px;
        }

        /* ==========================================
           GRID DE DOS COLUMNAS
           ========================================== */
        .two-col-grid {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .two-col-grid td {
            vertical-align: top;
            padding: 0 7px;
        }

        .info-card {
            background-color: #f9fafb;
            border: 2px solid #e5e7eb;
            padding: 12px;
            border-radius: 6px;
            height: 100%;
        }

        .card-title {
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #10b981;
        }

        /* ==========================================
           TOP CLIENTES
           ========================================== */
        .top-clients-table {
            width: 100%;
            border-collapse: collapse;
        }

        .client-row {
            background-color: white;
            border-left: 4px solid #10b981;
            margin-bottom: 5px;
        }

        .client-row td {
            padding: 8px 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .rank-badge {
            width: 30px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #10b981;
        }

        .client-name {
            font-size: 9px;
            font-weight: bold;
            color: #1f2937;
        }

        .client-orders {
            font-size: 7px;
            color: #6b7280;
            margin-top: 2px;
        }

        .client-amount {
            text-align: right;
            font-size: 10px;
            font-weight: bold;
            color: #059669;
            white-space: nowrap;
        }

        /* ==========================================
           GRÁFICO DE BARRAS (VENTAS POR DÍA)
           ========================================== */
        .chart-bar {
            margin-bottom: 8px;
        }

        .chart-bar-label {
            font-size: 8px;
            color: #6b7280;
            margin-bottom: 3px;
        }

        .chart-bar-container {
            width: 100%;
            border-collapse: collapse;
        }

        .chart-bar-fill {
            height: 14px;
            background-color: #10b981;
            border-radius: 3px;
        }

        .chart-bar-value {
            font-size: 9px;
            font-weight: bold;
            color: #1f2937;
            padding-left: 8px;
            white-space: nowrap;
        }

        /* ==========================================
           TABLA DE PEDIDOS
           ========================================== */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .orders-table thead th {
            background-color: #10b981;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            border: 1px solid #059669;
        }

        .orders-table tbody td {
            padding: 6px 5px;
            font-size: 8px;
            border: 1px solid #e5e7eb;
        }

        .orders-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .orders-table tbody tr:hover {
            background-color: #f0fdf4;
        }

        .order-id {
            font-weight: bold;
            color: #1f2937;
        }

        .client-info {
            font-weight: bold;
            color: #1f2937;
        }

        .client-phone {
            font-size: 7px;
            color: #6b7280;
        }

        .order-date {
            color: #1f2937;
        }

        .order-time {
            font-size: 7px;
            color: #6b7280;
        }

        .order-total {
            font-weight: bold;
            color: #059669;
            text-align: right;
        }

        .totals-row {
            background-color: #d1fae5 !important;
            font-weight: bold;
        }

        .totals-row td {
            font-size: 9px;
            color: #065f46;
            border: 2px solid #10b981 !important;
        }

        /* ==========================================
           BADGES DE ESTADO
           ========================================== */
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-completado {
            background-color: #10b981;
            color: white;
        }

        .badge-pendiente {
            background-color: #f59e0b;
            color: white;
        }

        .badge-cancelado {
            background-color: #ef4444;
            color: white;
        }

        .badge-procesando {
            background-color: #3b82f6;
            color: white;
        }

        /* ==========================================
           RESUMEN FINAL
           ========================================== */
        .summary-banner {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 15px;
            margin-top: 15px;
            border-radius: 6px;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-grid td {
            padding: 8px;
            text-align: center;
            border-right: 2px solid rgba(255,255,255,0.3);
        }

        .summary-grid td:last-child {
            border-right: 0;
        }

        .summary-label {
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            opacity: 0.95;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
        }

        /* ==========================================
           MENSAJE VACÍO
           ========================================== */
        .empty-state {
            background-color: #fef3c7;
            border: 3px solid #f59e0b;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
        }

        .empty-icon {
            font-size: 48px;
            color: #d97706;
            margin-bottom: 15px;
        }

        .empty-title {
            font-size: 16px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 8px;
        }

        .empty-text {
            font-size: 10px;
            color: #78350f;
        }

        /* ==========================================
           FOOTER
           ========================================== */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #f0fdf4;
            border-top: 2px solid #10b981;
            padding: 8px 15px;
            font-size: 7px;
            color: #6b7280;
        }

        .footer-table {
            width: 100%;
        }

        /* ==========================================
           UTILIDADES
           ========================================== */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>

    {{-- ==========================================
        HEADER PRINCIPAL
        ========================================== --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 60%;">
                    <div class="company-name">PROYECTO DARIVA</div>
                    <div class="company-info">
                        Sistema de Gestión de Restaurante<br>
                        RUC: 12345678901 | Tel: (01) 234-5678<br>
                        Email: info@proyectodariva.com
                    </div>
                </td>
                <td style="width: 40%;">
                    <div class="report-title">REPORTE DE VENTAS</div>
                    <div class="report-date">
                        Generado: {{ now()->format('d/m/Y') }} {{ now()->format('h:i A') }}<br>
                        Usuario: {{ auth()->user()->name ?? 'Sistema' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ==========================================
        PERÍODO DEL REPORTE
        ========================================== --}}
    <div class="period-banner">
        <div class="period-label">[CALENDARIO] PERÍODO DE ANÁLISIS</div>
        <div class="period-dates">
            {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}
            - 
            {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
        </div>
        <div class="period-days">({{ $diasPeriodo }} día{{ $diasPeriodo != 1 ? 's' : '' }})</div>
    </div>

    {{-- ==========================================
        KPIs PRINCIPALES
        ========================================== --}}
    <table class="kpi-grid">
        <tr>
            <td class="kpi-card kpi-green" style="width: 25%;">
                <div class="kpi-icon">$</div>
                <div class="kpi-label">Total Ventas</div>
                <div class="kpi-value">S/ {{ $totalVentas }}</div>
                <div class="kpi-subtitle">Ingresos del período</div>
            </td>
            <td class="kpi-card kpi-blue" style="width: 25%;">
                <div class="kpi-icon">#</div>
                <div class="kpi-label">Pedidos</div>
                <div class="kpi-value">{{ $cantidadPedidos }}</div>
                <div class="kpi-subtitle">Total de órdenes</div>
            </td>
            <td class="kpi-card kpi-purple" style="width: 25%;">
                <div class="kpi-icon">~</div>
                <div class="kpi-label">Ticket Promedio</div>
                <div class="kpi-value">S/ {{ $ticketPromedio }}</div>
                <div class="kpi-subtitle">Por pedido</div>
            </td>
            <td class="kpi-card kpi-orange" style="width: 25%;">
                <div class="kpi-icon">/</div>
                <div class="kpi-label">Ventas/Día</div>
                <div class="kpi-value">S/ {{ $promedioDiario }}</div>
                <div class="kpi-subtitle">Promedio diario</div>
            </td>
        </tr>
    </table>

    {{-- ==========================================
        ANÁLISIS POR ESTADO
        ========================================== --}}
    <div class="status-section">
        <div class="section-title">[GRÁFICO] Análisis por Estado de Pedidos</div>
        <table class="status-grid">
            <tr>
                <td class="status-box status-completado" style="width: 25%;">
                    <div class="status-number">{{ $pedidosCompletados }}</div>
                    <div class="status-label">Completados</div>
                    <div class="status-amount">S/ {{ $totalCompletados }}</div>
                </td>
                <td class="status-box status-pendiente" style="width: 25%;">
                    <div class="status-number">{{ $pedidosPendientes }}</div>
                    <div class="status-label">Pendientes</div>
                    <div class="status-amount">S/ {{ $totalPendientes }}</div>
                </td>
                <td class="status-box status-procesando" style="width: 25%;">
                    <div class="status-number">{{ $pedidosProcesando }}</div>
                    <div class="status-label">Procesando</div>
                    <div class="status-amount">S/ {{ $totalProcesando }}</div>
                </td>
                <td class="status-box status-cancelado" style="width: 25%;">
                    <div class="status-number">{{ $pedidosCancelados }}</div>
                    <div class="status-label">Cancelados</div>
                    <div class="status-amount">S/ {{ $totalCancelados }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ==========================================
        GRID DE DOS COLUMNAS
        ========================================== --}}
    <table class="two-col-grid">
        <tr>
            {{-- TOP 5 CLIENTES --}}
            <td style="width: 50%;">
                <div class="info-card">
                    <div class="card-title">[TROFEO] Top 5 Mejores Clientes</div>
                    @if($topClientes->count() > 0)
                        <table class="top-clients-table">
                            @foreach($topClientes as $index => $data)
                                <tr class="client-row">
                                    <td class="rank-badge">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="client-name">
                                            {{ $data['cliente']->nombre ?? 'Cliente Desconocido' }}
                                        </div>
                                        <div class="client-orders">
                                            {{ $data['pedidos'] }} pedido{{ $data['pedidos'] != 1 ? 's' : '' }}
                                        </div>
                                    </td>
                                    <td class="client-amount">
                                        S/ {{ $data['total'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <p style="text-align: center; color: #6b7280; padding: 20px; font-size: 8px;">
                            No hay datos de clientes disponibles
                        </p>
                    @endif
                </div>
            </td>

            {{-- VENTAS POR DÍA --}}
            <td style="width: 50%;">
                <div class="info-card">
                    <div class="card-title">[CALENDARIO] Ventas por Día (Últimos 7)</div>
                    @if($ventasPorDia->count() > 0)
                        @foreach($ventasPorDia as $venta)
                            <div class="chart-bar">
                                <div class="chart-bar-label">
                                    {{ \Carbon\Carbon::parse($venta['fecha'])->format('d/m/Y') }}
                                </div>
                                <table class="chart-bar-container">
                                    <tr>
                                        <td class="chart-bar-fill" style="width: {{ $venta['width'] }}%;"></td>
                                        <td class="chart-bar-value">S/ {{ $venta['total'] }}</td>
                                    </tr>
                                </table>
                            </div>
                        @endforeach
                    @else
                        <p style="text-align: center; color: #6b7280; padding: 20px; font-size: 8px;">
                            No hay datos de ventas por día
                        </p>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- ==========================================
        TABLA DETALLADA DE PEDIDOS
        ========================================== --}}
    @if($pedidos->count() > 0)
        <div class="section-title">[LISTA] Detalle de Pedidos</div>

        <table class="orders-table">
            <thead>
                <tr>
                    <th style="width: 8%;">PEDIDO</th>
                    <th style="width: 25%;">CLIENTE</th>
                    <th style="width: 15%;">FECHA</th>
                    <th style="width: 8%; text-align: center;">ITEMS</th>
                    <th style="width: 12%;">ESTADO</th>
                    <th style="width: 12%; text-align: right;">SUBTOTAL</th>
                    <th style="width: 10%; text-align: right;">IGV</th>
                    <th style="width: 12%; text-align: right;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                    @php
                        $estadoPedido = strtolower($pedido->estado ?? 'pendiente');
                        
                        switch($estadoPedido) {
                            case 'completado':
                                $estadoBadge = '<span class="badge badge-completado">Completado</span>';
                                break;
                            case 'pendiente':
                                $estadoBadge = '<span class="badge badge-pendiente">Pendiente</span>';
                                break;
                            case 'cancelado':
                                $estadoBadge = '<span class="badge badge-cancelado">Cancelado</span>';
                                break;
                            case 'procesando':
                                $estadoBadge = '<span class="badge badge-procesando">Procesando</span>';
                                break;
                            default:
                                $estadoBadge = '<span class="badge badge-pendiente">' . e(ucfirst($estadoPedido)) . '</span>';
                        }
                        
                        $cantidadItems = $pedido->productos?->count() ?? 0;
                    @endphp

                    <tr>
                        <td class="order-id">#{{ str_pad($pedido->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            <div class="client-info">
                                {{ $pedido->cliente->nombre ?? 'N/A' }}
                            </div>
                            @if(isset($pedido->cliente->telefono) && $pedido->cliente->telefono)
                                <div class="client-phone">{{ $pedido->cliente->telefono }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="order-date">{{ $pedido->fecha->format('d/m/Y') }}</div>
                            <div class="order-time">{{ \Carbon\Carbon::parse($pedido->fecha->format('Y-m-d') . ' ' . $pedido->hora)->format('h:i A') }}</div>
                        </td>
                        <td class="text-center">
                            <strong>{{ $cantidadItems }}</strong>
                        </td>
                        <td>{!! $estadoBadge !!}</td>
                        <td class="text-right">S/ {{ $pedido->report_net }}</td>
                        <td class="text-right" style="color: #6b7280;">S/ {{ $pedido->report_tax }}</td>
                        <td class="order-total">S/ {{ $pedido->report_total }}</td>
                    </tr>
                @endforeach

                {{-- Fila de totales --}}
                <tr class="totals-row">
                    <td colspan="5" class="text-right">
                        <strong>TOTALES DEL PERÍODO:</strong>
                    </td>
                    <td class="text-right">S/ {{ $subtotalGeneral }}</td>
                    <td class="text-right">S/ {{ $igvGeneral }}</td>
                    <td class="text-right">S/ {{ $totalVentas }}</td>
                </tr>
            </tbody>
        </table>

    @else
        {{-- Sin ventas --}}
        <div class="empty-state">
            <div class="empty-icon">⚠</div>
            <div class="empty-title">Sin Registros de Ventas</div>
            <div class="empty-text">
                No hay ventas registradas en el período seleccionado:<br>
                <strong>
                    {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} -
                    {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                </strong>
            </div>
        </div>
    @endif

    {{-- ==========================================
        RESUMEN FINAL
        ========================================== --}}
    @if($pedidos->count() > 0)
        <div class="summary-banner">
            <table class="summary-grid">
                <tr>
                    <td style="width: 25%;">
                        <div class="summary-label">Total Ventas</div>
                        <div class="summary-value">S/ {{ $totalVentas }}</div>
                    </td>
                    <td style="width: 25%;">
                        <div class="summary-label">Pedidos</div>
                        <div class="summary-value">{{ $cantidadPedidos }}</div>
                    </td>
                    <td style="width: 25%;">
                        <div class="summary-label">Ticket Promedio</div>
                        <div class="summary-value">S/ {{ $ticketPromedio }}</div>
                    </td>
                    <td style="width: 25%;">
                        <div class="summary-label">Tasa de Éxito</div>
                        @php
                            $tasaExito = $cantidadPedidos > 0
                                ? round(($pedidosCompletados / $cantidadPedidos) * 100)
                                : 0;
                        @endphp
                        <div class="summary-value">{{ $tasaExito }}%</div>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    {{-- ==========================================
        FOOTER
        ========================================== --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td style="width: 70%;">
                    <strong>Proyecto Dariva</strong> | Sistema de Gestión | www.proyectodariva.com
                </td>
                <td style="width: 30%; text-align: right;">
                    Página 1 | Generado: {{ now()->format('d/m/Y h:i A') }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
