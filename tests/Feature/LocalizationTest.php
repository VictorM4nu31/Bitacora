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
