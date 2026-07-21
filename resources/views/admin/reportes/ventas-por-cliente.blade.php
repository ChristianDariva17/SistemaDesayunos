<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas por Cliente</title>
    <style>
        @page { margin: 24px; }
        * { box-sizing: border-box; }
        body {
            color: #1f2937;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            margin: 0;
        }
        h1 { color: #166534; font-size: 20px; margin: 0 0 4px; }
        .period { color: #6b7280; margin-bottom: 18px; }
        .summary { margin-bottom: 16px; width: 100%; }
        .summary td {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 9px 12px;
            width: 50%;
        }
        .summary strong { color: #166534; display: block; font-size: 14px; }
        table { border-collapse: collapse; width: 100%; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th {
            background: #166534;
            color: #fff;
            font-size: 9px;
            padding: 7px 6px;
            text-align: left;
        }
        td { border-bottom: 1px solid #e5e7eb; padding: 7px 6px; vertical-align: top; }
        .number { text-align: right; white-space: nowrap; }
        .muted { color: #6b7280; }
        .empty { border: 1px solid #d1d5db; padding: 18px; text-align: center; }
        footer { color: #6b7280; font-size: 8px; margin-top: 16px; text-align: right; }
    </style>
</head>
<body>
    <header>
        <h1>Ventas por Cliente</h1>
        <div class="period">Período: {{ $fechaInicio }} al {{ $fechaFin }}</div>
    </header>

    <table class="summary">
        <tr>
            <td>Total de clientes<strong>{{ number_format($totalClientes) }}</strong></td>
            <td>Ventas generales<strong>S/ {{ $ventasGenerales }}</strong></td>
        </tr>
    </table>

    @if($ventasPorCliente->isEmpty())
        <div class="empty">No se registraron ventas en el período seleccionado.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Contacto</th>
                    <th class="number">Pedidos</th>
                    <th class="number">Total vendido</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventasPorCliente as $venta)
                    <tr>
                        <td>{{ $venta->nombre }}</td>
                        <td>
                            <div>{{ $venta->email ?: 'Sin correo' }}</div>
                            <div class="muted">{{ $venta->telefono ?: 'Sin teléfono' }}</div>
                        </td>
                        <td class="number">{{ number_format((int) $venta->total_pedidos) }}</td>
                        <td class="number">S/ {{ $venta->total_ventas }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <footer>Generado el {{ now()->format('d/m/Y H:i') }}</footer>
</body>
</html>
