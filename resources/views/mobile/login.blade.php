<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Iniciar sesión') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f3f4f6; margin: 0; padding: 24px; }
        .card { max-width: 340px; margin: 8vh auto 0; background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        h1 { font-size: 18px; margin: 0 0 16px; }
        label { display: block; font-size: 13px; color: #6b7280; margin: 12px 0 4px; }
        input { width: 100%; height: 42px; border: 1px solid #d1d5db; border-radius: 10px; padding: 0 12px; font-size: 15px; }
        button { width: 100%; height: 44px; margin-top: 20px; background: #111827; color: #fff; border: 0; border-radius: 10px; font-size: 15px; font-weight: 600; }
        p.error { color: #dc2626; font-size: 13px; }
    </style>
</head>
<body>
<div class="card">
    <h1>{{ __('Bitácora') }}</h1>
    @if ($errors->any())
        <p class="error">{{ __('Credenciales incorrectas.') }}</p>
    @endif
    <form method="POST" action="{{ route('mobile.login.submit') }}">
        @csrf
        <label for="email">{{ __('Correo electrónico') }}</label>
        <input id="email" type="email" name="email" required autofocus>
        <label for="password">{{ __('Contraseña') }}</label>
        <input id="password" type="password" name="password" required>
        <button type="submit">{{ __('Iniciar sesión') }}</button>
    </form>
</div>
</body>
</html>
