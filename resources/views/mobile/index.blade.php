<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Servicios') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f3f4f6; margin: 0; padding: 16px; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        h1 { font-size: 20px; margin: 0; }
        .user { font-size: 13px; color: #6b7280; }
        .list { display: grid; gap: 12px; }
        .item { background: #fff; border-radius: 14px; padding: 14px; box-shadow: 0 1px 2px rgba(0,0,0,.06); }
        .item h2 { font-size: 15px; margin: 0 0 2px; }
        .item p { margin: 0; color: #6b7280; font-size: 13px; }
        .badge { display: inline-block; margin-top: 8px; padding: 3px 10px; border-radius: 999px; font-size: 12px; background: #e5e7eb; }
        .logout { color: #6b7280; font-size: 13px; text-decoration: none; }
    </style>
</head>
<body>
<header>
    <h1>{{ __('Servicios') }}</h1>
    <span class="user">{{ $user?->name }}</span>
</header>

<div class="list">
    @foreach ($orders as $order)
        <a class="item" href="{{ route('mobile.show', $order['id']) }}" style="text-decoration:none;color:inherit">
            <h2>{{ $order['customer'] ?? __('Sin cliente') }}</h2>
            <p>{{ $order['equipment'] ?? __('Sin equipo') }}</p>
            <span class="badge">{{ $order['status'] }}</span>
        </a>
    @endforeach
</div>

<p style="margin-top:20px;text-align:center">
    <a class="logout" href="{{ route('mobile.logout') }}">{{ __('Cerrar sesión') }}</a>
</p>
</body>
</html>
