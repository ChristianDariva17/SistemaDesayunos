<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Inventario</title>
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

        /* HEADER */
        .header {
            background-color: #667eea;
            color: white;
            padding: 15px;
            margin-bottom: 15px;
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

        /* CARDS DE RESUMEN */
        .summary-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .summary-table td {
            background-color: #667eea;
            color: white;
            padding: 10px;
            text-align: center;
            border: 2px solid white;
        }

        .card-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .card-value {
            font-size: 20px;
            font-weight: bold;
        }

        /* TABLA DE PRODUCTOS */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #667eea;
            margin: 15px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #667eea;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .products-table thead th {
            background-color: #667eea;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #5568d3;
        }

        .products-table tbody td {
            padding: 6px 5px;
            font-size: 9px;
            border: 1px solid #ddd;
        }

        .products-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* ALERTAS DE STOCK */
        .stock-critical {
            background-color: #ffebee !important;
            color: #c62828;
            font-weight: bold;
        }

        .stock-low {
            background-color: #fff3e0 !important;
            color: #e65100;
            font-weight: bold;
        }

        .stock-good {
            color: #2e7d32;
            font-weight: bold;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #4caf50;
            color: white;
        }

        .badge-warning {
            background-color: #ff9800;
            color: white;
        }

        .badge-danger {
            background-color: #f44336;
            color: white;
        }

        /* TOTALES */
        .totals-box {
            background-color: #667eea;
            color: white;
            padding: 15px;
            margin-top: 15px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px;
            text-align: center;
            border-right: 2px solid white;
        }

        .totals-table td:last-child {
            border-right: 0;
        }

        .total-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .total-value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 3px;
        }

        /* OBSERVACIONES */
        .observations-box {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 10px;
            margin-top: 15px;
        }

        .observations-box h3 {
            color: #92400e;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .observations-box ul {
            margin-left: 15px;
            line-height: 1.6;
        }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #f5f5f5;
            border-top: 2px solid #667eea;
            padding: 8px 15px;
            font-size: 8px;
            color: #666;
        }

        .footer table {
            width: 100%;
        }

        /* UTILIDADES */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <table>
            <tr>
                <td style="width: 60%;">
                    <h1>PROYECTO DARIVA</h1>
                    <p>Sistema de Gestion de Restaurante</p>
                    <p>RUC: 12345678901 | Telefono: (01) 234-5678</p>
                </td>
                <td style="width: 40%;">
                    <div class="report-title">REPORTE DE INVENTARIO</div>
                    <p style="text-align: right;">Fecha: {{ now()->format('d/m/Y') }}</p>
                    <p style="text-align: right;">Hora: {{ now()->format('h:i A') }}</p>
                    <p style="text-align: right;">Usuario: {{ auth()->user()->name ?? 'Sistema' }}</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- RESUMEN EJECUTIVO --}}
    <table class="summary-table">
        <tr>
            <td style="width: 33.33%;">
                <div class="card-label">Total Productos</div>
                <div class="card-value">{{ $totalProductos }}</div>
            </td>
            <td style="width: 33.33%;">
                <div class="card-label">Stock Total</div>
                <div class="card-value">{{ number_format($stockTotal) }}</div>
            </td>
            <td style="width: 33.33%;">
                <div class="card-label">Valor Inventario</div>
                <div class="card-value">S/ {{ number_format($valorInventario, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- TABLA DE PRODUCTOS --}}
    <h2 class="section-title">Detalle de Productos</h2>

    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">PRODUCTO</th>
                <th style="width: 15%;">CATEGORIA</th>
                <th style="width: 12%; text-align: right;">PRECIO</th>
                <th style="width: 10%; text-align: center;">STOCK</th>
                <th style="width: 13%; text-align: right;">VALOR TOTAL</th>
                <th style="width: 15%; text-align: center;">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $contador = 1;
            @endphp

            @foreach($productos as $producto)
                @php
                    $valorTotal = $producto->precio * $producto->stock;
                    $stockClase = '';
                    $stockBadge = '';
                    
                    if ($producto->stock == 0) {
                        $stockClase = 'stock-critical';
                        $stockBadge = '<span class="badge badge-danger">AGOTADO</span>';
                    } elseif ($producto->stock < 10) {
                        $stockClase = 'stock-low';
                        $stockBadge = '<span class="badge badge-warning">STOCK BAJO</span>';
                    } else {
                        $stockClase = 'stock-good';
                        $stockBadge = '<span class="badge badge-success">DISPONIBLE</span>';
                    }
                @endphp

                <tr class="{{ $stockClase }}">
                    <td class="text-center">{{ $contador++ }}</td>
                    <td>
                        <strong>{{ $producto->nombre }}</strong>
                        @if($producto->descripcion)
                            <br><small style="color: #666;">{{ Str::limit($producto->descripcion, 40) }}</small>
                        @endif
                    </td>
                    <td>{{ ucfirst($producto->categoria ?? 'N/A') }}</td>
                    <td class="text-right">S/ {{ number_format($producto->precio, 2) }}</td>
                    <td class="text-center text-bold">{{ number_format($producto->stock) }}</td>
                    <td class="text-right text-bold">S/ {{ number_format($valorTotal, 2) }}</td>
                    <td class="text-center">{!! $stockBadge !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTALES GENERALES --}}
    <div class="totals-box">
        <table class="totals-table">
            <tr>
                <td style="width: 25%;">
                    <div class="total-label">Total Productos</div>
                    <div class="total-value">{{ $totalProductos }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="total-label">Stock Total</div>
                    <div class="total-value">{{ number_format($stockTotal) }} unid.</div>
                </td>
                <td style="width: 25%;">
                    <div class="total-label">Valor Inventario</div>
                    <div class="total-value">S/ {{ number_format($valorInventario, 2) }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="total-label">Productos Criticos</div>
                    <div class="total-value">{{ $productos->where('stock', '<', 10)->count() }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- OBSERVACIONES --}}
    <div class="observations-box">
        <h3>OBSERVACIONES</h3>
        <ul>
            <li>Hay <strong>{{ $productos->where('stock', '<', 10)->count() }}</strong> productos con stock bajo (menos de 10 unidades).</li>
            <li>Se recomienda reabastecer los productos marcados en rojo y naranja.</li>
            <li>El valor total del inventario es de <strong>S/ {{ number_format($valorInventario, 2) }}</strong>.</li>
            <li>Este reporte fue generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('h:i A') }}.</li>
        </ul>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <table>
            <tr>
                <td style="width: 50%;">
                    <strong>Proyecto Dariva</strong> | Sistema de Gestion | www.proyectodariva.com
                </td>
                <td style="width: 50%; text-align: right;">
                    Generado: {{ now()->format('d/m/Y h:i A') }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
