<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de servicio #{{ $report->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1B1E1D; background: #F7F3EB; margin: 0; padding: 32px; font-size: 13px; }
        .band { background: #0C3B3A; color: #FFFFFF; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; }
        .band .brand { font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #FFB224; }
        h1 { font-size: 20px; margin: 4px 0 0; }
        .folio { font-family: DejaVu Sans Mono, monospace; font-size: 11px; color: #D8E8E5; }
        .meta { color: #5C6563; margin-bottom: 20px; }
        h2 { font-size: 11px; text-transform: uppercase; color: #0C3B3A; letter-spacing: 1.5px; margin: 20px 0 8px; border-bottom: 2px solid #DED4BE; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; background: #FFFFFF; border: 1.5px solid #DED4BE; border-radius: 8px; }
        td { padding: 7px 10px; border-bottom: 1px solid #EFE8D8; vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        td.label { width: 160px; color: #5C6563; }
        td.mono { font-family: DejaVu Sans Mono, monospace; }
        .stamp { display: inline-block; border: 2px double #1A7A4C; color: #1A7A4C; font-weight: 700; font-size: 11px; letter-spacing: 2px; padding: 3px 10px; margin: 12px 0 0; }
        .total { font-weight: 700; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #DED4BE; color: #5C6563; font-size: 11px; }
    </style>
</head>
<body>
    <div class="band">
        <div class="brand">Bitácora · Cuaderno de taller</div>
        <h1>Reporte de servicio</h1>
        <div class="folio">FOLIO #{{ str_pad((string) $report->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>

    @if ($report->serviceOrder?->customer)
        <p class="meta">Cliente: {{ $report->serviceOrder->customer->name }}</p>
    @endif

    <div class="stamp">FINALIZADO</div>

    <h2>Información</h2>
    <table>
        <tr><td class="label">Fecha</td><td class="mono">{{ $report->created_at->format('d/m/Y H:i') }}</td></tr>
        @if ($report->arrival_time)<tr><td class="label">Hora de llegada</td><td class="mono">{{ $report->arrival_time->format('H:i') }}</td></tr>@endif
        @if ($report->equipment_type)<tr><td class="label">Equipo</td><td>{{ App\Enums\EquipmentType::tryFrom($report->equipment_type)?->label() ?? $report->equipment_type }}</td></tr>@endif
        @if ($report->equipment)<tr><td class="label">Equipo registrado</td><td>{{ $report->equipment->name }}</td></tr>@endif
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

    <p class="footer">Generado por Bitácora Inteligente para Técnicos · {{ now()->format('d/m/Y H:i') }}</p>
</body>
</html>
