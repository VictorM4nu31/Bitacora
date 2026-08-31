<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Reporte de servicio #{{ $report->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #f3f4f6; color: #1f2937; margin: 0; padding: 24px; }
        .card { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .meta { color: #6b7280; margin-bottom: 24px; }
        h2 { font-size: 12px; text-transform: uppercase; color: #6b7280; letter-spacing: 1px; margin: 20px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        td.label { width: 150px; color: #6b7280; }
        .total { font-weight: 700; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Reporte de servicio #{{ $report->id }}</h1>
        @if ($report->serviceOrder?->customer)
            <p class="meta">{{ $report->serviceOrder->customer->name }}</p>
        @endif

        <h2>Información</h2>
        <table>
            <tr><td class="label">Fecha</td><td>{{ $report->created_at->format('d/m/Y') }}</td></tr>
            @if ($report->arrival_time)<tr><td class="label">Hora de llegada</td><td>{{ $report->arrival_time->format('H:i') }}</td></tr>@endif
            @if ($report->equipment_type)<tr><td class="label">Equipo</td><td>{{ App\Enums\EquipmentType::tryFrom($report->equipment_type)?->label() ?? $report->equipment_type }}</td></tr>@endif
        </table>

        <h2>Diagnóstico</h2>
        <table>
            @if ($report->problem)<tr><td class="label">Problema</td><td>{{ $report->problem }}</td></tr>@endif
            @if ($report->diagnosis)<tr><td class="label">Diagnóstico</td><td>{{ $report->diagnosis }}</td></tr>@endif
            @if ($report->work_done)<tr><td class="label">Trabajo realizado</td><td>{{ $report->work_done }}</td></tr>@endif
            @if ($report->tests_performed)<tr><td class="label">Pruebas</td><td>{{ $report->tests_performed }}</td></tr>@endif
            @if ($report->result)<tr><td class="label">Resultado</td><td>{{ $report->result }}</td></tr>@endif
        </table>

        @if ($report->total_cost)
            <h2>Costo</h2>
            <table>
                <tr><td class="label total">Total</td><td class="total">{{ $report->currency }} {{ number_format((float) $report->total_cost, 2) }}</td></tr>
            </table>
        @endif

        <p class="footer">Documento generado de forma electrónica</p>
    </div>
</body>
</html>
