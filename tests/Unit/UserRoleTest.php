<?php

use App\Enums\UserRole;

test('UserRole has exactly the admin and technician cases', function () {
    expect(UserRole::cases())->toHaveCount(2);
    expect(UserRole::Admin->value)->toBe('admin');
    expect(UserRole::Technician->value)->toBe('technician');
});

test('UserRole label returns human readable values', function () {
    expect(UserRole::Admin->label())->toBe('Administrador');
    expect(UserRole::Technician->label())->toBe('Técnico');
});

test('isAdmin only returns true for the admin role', function () {
    expect(UserRole::Admin->isAdmin())->toBeTrue();
    expect(UserRole::Technician->isAdmin())->toBeFalse();
});
