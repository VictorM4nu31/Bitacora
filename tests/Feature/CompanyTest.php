<?php

use App\Enums\UserRole;
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

test('a user defaults to the technician role', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe(UserRole::Technician);
});

test('a user can be created as an admin', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBe(UserRole::Admin);
    expect($user->isAdmin())->toBeTrue();
});

test('a company exposes its slug and timezone', function () {
    $company = Company::factory()->create(['slug' => 'demo', 'timezone' => 'UTC']);

    expect($company->slug)->toBe('demo');
    expect($company->timezone)->toBe('UTC');
});
