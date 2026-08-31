<?php

use App\Models\Company;
use App\Models\User;

test('a company has many users', function () {
    $company = Company::factory()->create();

    User::factory()->forCompany($company)->count(3)->create();

    expect($company->users()->count())->toBe(3);
});

test('a user belongs to a company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();

    expect($user->company->id)->toBe($company->id);
});

test('a user assigned the technician role has that role', function () {
    $user = User::factory()->technician()->create();

    expect($user->hasRole('technician'))->toBeTrue()
        ->and($user->isAdmin())->toBeFalse();
});

test('a user assigned the admin role is an admin', function () {
    $user = User::factory()->admin()->create();

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->isAdmin())->toBeTrue();
});

test('a company exposes its slug and timezone', function () {
    $company = Company::factory()->create(['slug' => 'demo', 'timezone' => 'UTC']);

    expect($company->slug)->toBe('demo');
    expect($company->timezone)->toBe('UTC');
});
