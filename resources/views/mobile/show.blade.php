<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Servicio') }} #{{ $order->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f3f4f6; margin: 0; padding: 16px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .meta { color: #6b7280; font-size: 14px; margin-bottom: 16px; }
        .card { background: #fff; border-radius: 14px; padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.06); }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .row:last-child { border-bottom: 0; }
        .row span { color: #6b7280; }
        a { display: inline-block; margin-top: 16px; color: #374151; font-size: 14px; text-decoration: none; }
    </style>
</head>
<body>
<h1>{{ $order->customer?->name ?? __('Sin cliente') }}</h1>
<p class="meta">{{ __('Orden de servicio') }} #{{ $order->id }}</p>

<div class="card">
    <div class="row"><span>{{ __('Equipo') }}</span><strong>{{ $order->equipment?->name ?? '—' }}</strong></div>
    <div class="row"><span>{{ __('Técnico') }}</span><strong>{{ $order->technician?->name ?? '—' }}</strong></div>
    <div class="row"><span>{{ __('Estado') }}</span><strong>{{ $order->status->label() }}</strong></div>
</div>

<a href="{{ route('mobile.index') }}">← {{ __('Volver') }}</a>
</body>
</html>
