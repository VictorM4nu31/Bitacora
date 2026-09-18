<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Reporte de servicio #{{ $report->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; background: #F7F3EB; color: #1B1E1D; margin: 0; padding: 24px; }
        .card { max-width: 640px; margin: 0 auto; background: #fff; border: 1.5px solid #DED4BE; border-radius: 10px; overflow: hidden; box-shadow: 2px 2px 0 rgba(27,30,29,.08); }
        .band { background: #0C3B3A; color: #fff; padding: 20px 28px; }
        .brand { font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #FFB224; }
        h1 { font-size: 22px; margin: 4px 0 0; letter-spacing: -0.5px; }
        .folio { font-family: ui-monospace, monospace; font-size: 12px; color: #D8E8E5; }
        .body { padding: 8px 28px 28px; }
        .meta { color: #5C6563; margin-bottom: 8px; }
        h2 { font-size: 11px; text-transform: uppercase; color: #0C3B3A; letter-spacing: 1.5px; margin: 20px 0 8px; border-bottom: 2px solid #DED4BE; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border-bottom: 1px solid #EFE8D8; font-size: 14px; vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        td.label { width: 150px; color: #5C6563; }
        td.mono { font-family: ui-monospace, monospace; font-size: 13px; }
        .stamp { display: inline-block; border: 2px double #1A7A4C; color: #1A7A4C; font-weight: 700; font-size: 11px; letter-spacing: 2px; padding: 3px 10px; transform: rotate(-4deg); margin-top: 12px; }
        .total { font-weight: 700; }
        .footer { margin-top: 28px; padding-top: 12px; border-top: 1px solid #DED4BE; color: #5C6563; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="band">
            <div class="brand">Bitácora · Cuaderno de taller</div>
            <h1>Reporte de servicio</h1>
            <div class="folio">FOLIO #{{ str_pad((string) $report->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div class="body">
            @if ($report->serviceOrder?->customer)
                <p class="meta">{{ $report->serviceOrder->customer->name }}</p>
            @endif

            <span class="stamp">FINALIZADO</span>

            <h2>Información</h2>
            <table>
                <tr><td class="label">Fecha</td><td class="mono">{{ $report->created_at->format('d/m/Y') }}</td></tr>
                @if ($report->arrival_time)<tr><td class="label">Hora de llegada</td><td class="mono">{{ $report->arrival_time->format('H:i') }}</td></tr>@endif
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
                    <tr><td class="label total">Total</td><td class="total mono">{{ $report->currency }} {{ number_format((float) $report->total_cost, 2) }}</td></tr>
                </table>
            @endif

            <p class="footer">Documento generado de forma electrónica por Bitácora</p>
        </div>
    </div>
</body>
</html>
