<?php

use App\Models\User;

test('the spanish localization files exist', function () {
    expect(file_exists(lang_path('es.json')))->toBeTrue()
        ->and(is_dir(lang_path('es')))->toBeTrue()
        ->and(file_exists(lang_path('es/validation.php')))->toBeTrue()
        ->and(file_exists(lang_path('es/auth.php')))->toBeTrue();
});

test('the set locale middleware uses the authenticated user locale', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    expect(app()->getLocale())->toBe('en');
});

test('translation helper resolves spanish strings when locale is es', function () {
    app()->setLocale('es');

    expect(__('validation.required'))->not->toBe('The :attribute field is required.');
});

test('inertia shared props include the current locale', function () {
    app()->setLocale('es');

    $user = User::factory()->create(['locale' => 'es']);
    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $this->assertTrue(app()->getLocale() === 'es');
});

test('the locale endpoint updates the authenticated user locale', function () {
    $user = User::factory()->create(['locale' => 'es']);

    $this->actingAs($user)->patch(route('locale.update'), ['locale' => 'en'])->assertRedirect();

    expect($user->fresh()->locale)->toBe('en');
});

test('the locale endpoint rejects unsupported locales', function () {
    $user = User::factory()->create(['locale' => 'es']);

    $this->actingAs($user)->patch(route('locale.update'), ['locale' => 'fr'])->assertSessionHasErrors('locale');

    expect($user->fresh()->locale)->toBe('es');
});

test('the application brand is Bitácora', function () {
    expect(config('app.name'))->toBe('Bitácora');
});

test('dashboard strings resolve to spanish when locale is es', function () {
    app()->setLocale('es');

    expect(__('Today in the field'))->toBe('Hoy en campo')
        ->and(__('Where does the work continue?'))->toBe('¿Dónde sigue el trabajo?')
        ->and(__('Field routine'))->toBe('Rutina de campo')
        ->and(__('Voice note → draft → finalized PDF'))->toBe('Nota de voz → borrador → PDF finalizado');
});

test('dashboard strings fall back to the english source when locale is en', function () {
    app()->setLocale('en');

    expect(__('Today in the field'))->toBe('Today in the field')
        ->and(__('Where does the work continue?'))->toBe('Where does the work continue?');
});

test('every frontend translation key exists in both dictionaries', function () {
    $es = json_decode(file_get_contents(lang_path('es.json')), true);
    $en = json_decode(file_get_contents(lang_path('en.json')), true);

    $keys = [];

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('js')));
    foreach ($iterator as $file) {
        if (! $file->isFile() || ! in_array($file->getExtension(), ['ts', 'tsx'], true)) {
            continue;
        }

        preg_match_all("/\\bt\\(\\s*['\"]((?:[^'\"\\\\\\\\]|\\\\.)*)['\"]/", file_get_contents($file->getPathname()), $matches);

        foreach ($matches[1] as $key) {
            $keys[$key] = true;
        }
    }

    // Keys passed to t() dynamically (status maps, layout titles, breadcrumbs).
    $dynamic = [
        'Create an account',
        'Enter your details below to create your account',
        'Enter your email and password below to log in',
        'Enter your email to receive a password reset link',
        'Please enter your new password below',
        'This is a secure area of the application. Please confirm your password before continuing.',
        'Recovery code',
        'Please confirm access to your account by entering one of your emergency recovery codes.',
        'login using an authentication code',
        'Authentication code',
        'Enter the authentication code provided by your authenticator application.',
        'login using a recovery code',
        'Security',
        'Appearance',
        'Uploaded',
        'Processing',
        'Transcribed',
        'Error',
        'Dashboard',
        'Customers',
        'Equipment',
        'Services',
        'Profile',
        'Platform',
        'Settings',
    ];

    foreach ($dynamic as $key) {
        $keys[$key] = true;
    }

    $missingEs = array_values(array_filter(array_keys($keys), fn ($key) => ! array_key_exists($key, $es)));
    $missingEn = array_values(array_filter(array_keys($keys), fn ($key) => ! array_key_exists($key, $en)));

    expect($missingEs)->toBe([], 'missing in lang/es.json: '.implode(', ', $missingEs))
        ->and($missingEn)->toBe([], 'missing in lang/en.json: '.implode(', ', $missingEn));
});
