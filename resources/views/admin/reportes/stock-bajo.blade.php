<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Stock Bajo</title>
    <style>
        @page {
            margin: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.3;
        }

        /* ==========================================
           HEADER DE ALERTA
           ========================================== */
        .header {
            background-color: #ef4444;
            color: white;
            padding: 15px;
            margin-bottom: 15px;
            border: 3px solid #991b1b;
        }

        .header table {
            width: 100%;
            border: 0;
        }

        .header td {
            vertical-align: top;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 9px;
        }

        .header .report-title {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
        }

        .alert-icon {
            font-size: 32px;
            font-weight: bold;
        }

        /* ==========================================
           ALERTA PRINCIPAL
           ========================================== */
        .alert-box {
            background-color: #fef3c7;
            border-left: 6px solid #f59e0b;
            padding: 15px;
            margin-bottom: 15px;
        }

        .alert-box h3 {
            color: #92400e;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .alert-box p {
            color: #78350f;
            font-size: 10px;
            line-height: 1.5;
        }

        /* ==========================================
           CARDS DE RESUMEN
           ========================================== */
        .summary-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 10px;
            text-align: center;
            border: 2px solid white;
        }

        .summary-table td.red {
            background-color: #ef4444;
            color: white;
        }

        .summary-table td.orange {
            background-color: #f97316;
            color: white;
        }

        .summary-table td.yellow {
            background-color: #fbbf24;
            color: #78350f;
        }

        .card-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .card-value {
            font-size: 24px;
            font-weight: bold;
        }

        /* ==========================================
           TABLA DE PRODUCTOS
           ========================================== */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #dc2626;
            margin: 15px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #dc2626;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .products-table thead th {
            background-color: #ef4444;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #dc2626;
        }

        .products-table tbody td {
            padding: 6px 5px;
            font-size: 9px;
            border: 1px solid #ddd;
        }

        /* ==========================================
           NIVELES DE CRITICIDAD
           ========================================== */
        .criticidad-agotado {
            background-color: #fee2e2 !important;
            border-left: 5px solid #dc2626;
        }

        .criticidad-critico {
            background-color: #ffedd5 !important;
            border-left: 5px solid #f97316;
        }

        .criticidad-bajo {
            background-color: #fef3c7 !important;
            border-left: 5px solid #f59e0b;
        }

        /* ==========================================
           BADGES
           ========================================== */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-agotado {
            background-color: #dc2626;
            color: white;
        }

        .badge-critico {
            background-color: #f97316;
            color: white;
        }

        .badge-bajo {
            background-color: #f59e0b;
            color: white;
        }

        /* ==========================================
           BARRA DE URGENCIA SIMPLE
           ========================================== */
        .urgencia-bar-container {
            width: 100%;
            background-color: #e5e7eb;
            height: 6px;
            margin-top: 3px;
        }

        .urgencia-bar-fill {
            height: 100%;
        }

        .urgencia-agotado { background-color: #dc2626; }
        .urgencia-critico { background-color: #f97316; }
        .urgencia-bajo { background-color: #f59e0b; }

        /* ==========================================
           ACCIONES RECOMENDADAS
           ========================================== */
        .actions-box {
            background-color: #dbeafe;
            border-left: 6px solid #3b82f6;
            padding: 15px;
            margin-top: 15px;
        }

        .actions-box h3 {
            color: #1e40af;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .action-item {
            background-color: white;
            padding: 8px;
            margin-bottom: 8px;
            border-left: 4px solid #3b82f6;
        }

        /* ==========================================
           CHECKLIST
           ========================================== */
        .checklist {
            background-color: white;
            padding: 15px;
            border: 2px dashed #d1d5db;
            margin-top: 15px;
        }

        .checklist-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
        }

        .checklist-item {
            padding: 6px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }

        /* ==========================================
           OBSERVACIONES
           ========================================== */
        .observations-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 10px;
            margin-top: 15px;
        }

        .observations-box ul {
            margin-left: 15px;
            line-height: 1.6;
        }

        /* ==========================================
           FOOTER
           ========================================== */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #fef2f2;
            border-top: 2px solid #dc2626;
            padding: 8px 15px;
            font-size: 8px;
            color: #666;
        }

        .footer table {
            width: 100%;
        }

        /* ==========================================
           MENSAJE SIN PRODUCTOS
           ========================================== */
        .success-box {
            background-color: #d1fae5;
            border: 3px solid #10b981;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
        }

        .success-box .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .success-box h2 {
            color: #065f46;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .success-box p {
            color: #047857;
            font-size: 11px;
        }

        /* ==========================================
           UTILIDADES
           ========================================== */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>

    {{-- ==========================================
        HEADER DE ALERTA
        ========================================== --}}
    <div class="header">
        <table>
            <tr>
                <td style="width: 10%;">
                    <div class="alert-icon">(!)</div>
                </td>
                <td style="width: 50%;">
                    <h1>PROYECTO DARIVA</h1>
                    <p>Sistema de Gestion de Restaurante</p>
                    <p>RUC: 12345678901 | Telefono: (01) 234-5678</p>
                </td>
                <td style="width: 40%;">
                    <div class="report-title">[ALERTA] STOCK BAJO</div>
                    <p style="text-align: right;">Fecha: {{ now()->format('d/m/Y') }}</p>
                    <p style="text-align: right;">Hora: {{ now()->format('h:i A') }}</p>
                    <p style="text-align: right;">Usuario: {{ auth()->user()->name ?? 'Sistema' }}</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- ==========================================
        ALERTA PRINCIPAL
        ========================================== --}}
    @if($productos->count() > 0)
        <div class="alert-box">
            <h3>(!) ATENCION: Productos con stock critico detectados</h3>
            <p>
                <strong>{{ $productos->count() }}</strong> producto(s) requieren reabastecimiento urgente.
                Se recomienda revisar el inventario para evitar quiebres de stock que puedan afectar las operaciones del restaurante.
                Los productos estan ordenados por nivel de criticidad (de mayor a menor urgencia).
            </p>
        </div>
    @endif

    {{-- ==========================================
        RESUMEN EJECUTIVO
        ========================================== --}}
    <table class="summary-table">
        <tr>
            <td class="red" style="width: 33.33%;">
                <div class="card-label">AGOTADOS</div>
                <div class="card-value">{{ $productos->where('stock', 0)->count() }}</div>
            </td>
            <td class="orange" style="width: 33.33%;">
                <div class="card-label">BAJO MINIMO</div>
                <div class="card-value">{{ $productos->filter(fn ($producto) => $producto->stock > 0 && $producto->stock < $producto->stock_minimo)->count() }}</div>
            </td>
            <td class="yellow" style="width: 33.33%;">
                <div class="card-label">EN MINIMO</div>
                <div class="card-value">{{ $productos->filter(fn ($producto) => $producto->stock > 0 && $producto->stock === $producto->stock_minimo)->count() }}</div>
            </td>
        </tr>
    </table>

    {{-- ==========================================
        TABLA DE PRODUCTOS CRITICOS
        ========================================== --}}
    @if($productos->count() > 0)
        <h2 class="section-title">Listado de Productos Criticos</h2>

        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 22%;">PRODUCTO</th>
                    <th style="width: 12%;">CATEGORIA</th>
                    <th style="width: 8%; text-align: center;">STOCK</th>
                    <th style="width: 8%; text-align: center;">MINIMO</th>
                    <th style="width: 14%;">NIVEL</th>
                    <th style="width: 10%; text-align: right;">PRECIO</th>
                    <th style="width: 15%; text-align: right;">FALTANTE</th>
                    <th style="width: 10%; text-align: center;">URGENCIA</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $contador = 1;
                @endphp

                @foreach($productos as $producto)
                    @php
                        // Determinar nivel de criticidad
                        $cantidadSugerida = $producto->report_reorder_quantity;
                        if ($producto->stock == 0) {
                            $criticidad = 'agotado';
                            $badge = '<span class="badge badge-agotado">AGOTADO</span>';
                            $urgenciaColor = 'urgencia-agotado';
                            $urgenciaPorcentaje = 100;
                        } elseif ($producto->stock < $producto->stock_minimo) {
                            $criticidad = 'critico';
                            $badge = '<span class="badge badge-critico">BAJO MINIMO</span>';
                            $urgenciaColor = 'urgencia-critico';
                            $urgenciaPorcentaje = 80;
                        } else {
                            $criticidad = 'bajo';
                            $badge = '<span class="badge badge-bajo">EN MINIMO</span>';
                            $urgenciaColor = 'urgencia-bajo';
                            $urgenciaPorcentaje = 50;
                        }

                    @endphp

                    <tr class="criticidad-{{ $criticidad }}">
                        <td class="text-center text-bold">{{ $contador++ }}</td>
                        <td>
                            <strong>{{ $producto->nombre }}</strong>
                            @if($producto->descripcion)
                                <br><small style="color: #666;">{{ Str::limit($producto->descripcion, 30) }}</small>
                            @endif
                        </td>
                        <td>{{ ucfirst($producto->categoria ?? 'N/A') }}</td>
                        <td class="text-center">
                            <strong style="font-size: 12px; color: #dc2626;">{{ $producto->stock }}</strong>
                            <br><small style="color: #666;">unidades</small>
                        </td>
                        <td class="text-center">
                            <strong>{{ $producto->stock_minimo }}</strong>
                            <br><small style="color: #666;">unidades</small>
                        </td>
                        <td>
                            {!! $badge !!}
                            <table class="urgencia-bar-container" style="border: 0; margin-top: 3px;">
                                <tr>
                                    <td class="urgencia-bar-fill {{ $urgenciaColor }}" style="width: {{ $urgenciaPorcentaje }}%; border: 0;"></td>
                                    <td style="width: {{ 100 - $urgenciaPorcentaje }}%; border: 0;"></td>
                                </tr>
                            </table>
                        </td>
                        <td class="text-right">S/ {{ $producto->report_price }}</td>
                        <td class="text-right">
                            <strong style="color: #1e40af;">{{ $cantidadSugerida }} unid.</strong>
                            <br><small style="color: #666;">S/ {{ $producto->report_reorder_cost }}</small>
                        </td>
                        <td class="text-center">
                            @if($producto->stock == 0)
                                <strong style="color: #dc2626;">MAXIMA</strong>
                            @elseif($producto->stock < $producto->stock_minimo)
                                <strong style="color: #f97316;">ALTA</strong>
                            @else
                                <strong style="color: #f59e0b;">MEDIA</strong>
                            @endif
                        </td>
                    </tr>
                @endforeach

                {{-- Fila de totales --}}
                <tr style="background-color: #dbeafe; font-weight: bold;">
                    <td colspan="7" class="text-right">
                        <strong>COSTO ESTIMADO PARA ALCANZAR MINIMOS:</strong>
                    </td>
                    <td class="text-right" style="font-size: 11px; color: #1e40af;">
                        S/ {{ $valorEnRiesgo }}
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        {{-- ==========================================
            ACCIONES RECOMENDADAS
            ========================================== --}}
        <div class="actions-box">
            <h3>ACCIONES RECOMENDADAS</h3>

            @if($productos->where('stock', 0)->count() > 0)
                <div class="action-item">
                    <strong>(!) PRIORIDAD MAXIMA:</strong>
                    Revisar inmediatamente los {{ $productos->where('stock', 0)->count() }} producto(s) agotado(s)
                    para evitar perdida de ventas.
                </div>
            @endif

            @if($productos->filter(fn ($producto) => $producto->stock > 0 && $producto->stock < $producto->stock_minimo)->count() > 0)
                <div class="action-item">
                    <strong>(!) PRIORIDAD ALTA:</strong>
                    Revisar los {{ $productos->filter(fn ($producto) => $producto->stock > 0 && $producto->stock < $producto->stock_minimo)->count() }} producto(s)
                    por debajo de su minimo configurado.
                </div>
            @endif

            @if($productos->filter(fn ($producto) => $producto->stock > 0 && $producto->stock === $producto->stock_minimo)->count() > 0)
                <div class="action-item">
                    <strong>(!) PRIORIDAD MEDIA:</strong>
                    Vigilar los {{ $productos->filter(fn ($producto) => $producto->stock > 0 && $producto->stock === $producto->stock_minimo)->count() }} producto(s)
                    que estan exactamente en su minimo configurado.
                </div>
            @endif

            <div class="action-item">
                <strong>$ COSTO ESTIMADO:</strong>
                Se estima <strong>S/ {{ $valorEnRiesgo }}</strong>
                para llevar los productos alertados a su minimo configurado.
            </div>
        </div>

        {{-- ==========================================
            CHECKLIST DE COMPRA
            ========================================== --}}
        <div class="checklist">
            <div class="checklist-title">--- CHECKLIST DE COMPRA (Marcar al completar) ---</div>
            @foreach($productos->sortBy('stock') as $producto)
                <div class="checklist-item">
                    [ ] <strong>{{ $producto->nombre }}</strong> - 
                    Stock actual: {{ $producto->stock }} unid. | 
                    Minimo: {{ $producto->stock_minimo }} unid. |
                    Faltante: {{ max($producto->stock_minimo - $producto->stock, 0) }} unid.
                    | Costo: S/ {{ $producto->report_reorder_cost }}
                </div>
            @endforeach
        </div>

    @else
        {{-- Sin productos criticos --}}
        <div class="success-box">
            <div class="icon">[OK]</div>
            <h2>Excelente!</h2>
            <p>
                No hay productos con stock bajo en este momento. Todos los productos tienen inventario suficiente.
            </p>
        </div>
    @endif

    {{-- ==========================================
        OBSERVACIONES FINALES
        ========================================== --}}
    @if($productos->count() > 0)
        <div class="observations-box">
            <h3 style="color: #92400e; font-size: 12px; margin-bottom: 8px;">OBSERVACIONES FINALES</h3>
            <ul style="font-size: 9px;">
                <li><strong>Nivel de Stock Minimo:</strong> configurable por producto.</li>
                <li><strong>Productos Criticos:</strong> {{ $productos->count() }} requieren atencion.</li>
                <li><strong>Inversion Estimada:</strong> S/ {{ $valorEnRiesgo }} para alcanzar los minimos configurados.</li>
                <li>Este reporte fue generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('h:i A') }}.</li>
            </ul>
        </div>
    @endif

    {{-- ==========================================
        FOOTER
        ========================================== --}}
    <div class="footer">
        <table>
            <tr>
                <td style="width: 50%;">
                    <strong>Proyecto Dariva</strong> | Reporte de Stock Bajo | www.proyectodariva.com
                </td>
                <td style="width: 50%; text-align: right;">
                    {{ now()->format('d/m/Y h:i A') }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
