<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

test('the roles seeder creates admin and technician roles with permissions', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Role::where('name', 'admin')->exists())->toBeTrue()
        ->and(Role::where('name', 'technician')->exists())->toBeTrue();
});

test('an admin user is granted all permissions', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->admin()->create();

    expect($admin->hasPermissionTo('delete equipment'))->toBeTrue()
        ->and($admin->hasPermissionTo('manage maintenance'))->toBeTrue();
});

test('a technician user has the customer/report permissions but not destructive ones', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $tech = User::factory()->create();
    $tech->assignRole('technician');

    expect($tech->hasPermissionTo('view customers'))->toBeTrue()
        ->and($tech->hasPermissionTo('view reports'))->toBeTrue()
        ->and($tech->hasPermissionTo('delete equipment'))->toBeFalse()
        ->and($tech->hasPermissionTo('manage maintenance'))->toBeFalse();
});

test('a user role can be checked via the hasRole helper', function () {
    $user = User::factory()->technician()->create();

    expect($user->hasRole('technician'))->toBeTrue();
});
