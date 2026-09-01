<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\ServiceOrder;
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

test('a technician cannot delete customers, equipment or service orders', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();

    $customer = Customer::factory()->forCompany($company)->create();
    $equipment = Equipment::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'customer_id' => $customer->id,
        'equipment_id' => $equipment->id,
        'technician_id' => $user->id,
    ]);

    $this->actingAs($user)->delete(route('customers.destroy', $customer))->assertForbidden();
    $this->actingAs($user)->delete(route('equipment.destroy', $equipment))->assertForbidden();
    $this->actingAs($user)->delete(route('service-orders.destroy', $order))->assertForbidden();
});

test('an admin can delete customers, equipment and service orders', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->admin()->create();

    $customer = Customer::factory()->forCompany($company)->create();
    $equipment = Equipment::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'customer_id' => $customer->id,
        'equipment_id' => $equipment->id,
        'technician_id' => $user->id,
    ]);

    $this->actingAs($user)->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));
    $this->actingAs($user)->delete(route('equipment.destroy', $equipment))
        ->assertRedirect(route('equipment.index'));
    $this->actingAs($user)->delete(route('service-orders.destroy', $order))
        ->assertRedirect(route('service-orders.index'));
});

test('a technician cannot manage maintenance; only admins can', function () {
    $company = Company::factory()->create();
    $technician = User::factory()->forCompany($company)->create();
    $admin = User::factory()->forCompany($company)->admin()->create();
    $equipment = Equipment::factory()->forCompany($company)->create();

    $this->actingAs($technician)->post(route('equipment.maintenance', $equipment), [
        'interval_days' => 30,
    ])->assertForbidden();

    $this->actingAs($admin)->post(route('equipment.maintenance', $equipment), [
        'interval_days' => 30,
    ])->assertRedirect(route('equipment.show', $equipment));
});
