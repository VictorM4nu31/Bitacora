<?php

use App\Enums\EquipmentType;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\ServiceOrder;
use App\Models\User;

test('a technician can create equipment within their company', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->forCompany($company)->create();
    $user = User::factory()->forCompany($company)->create();

    $this->actingAs($user)->post(route('equipment.store'), [
        'name' => 'Minisplit 1.5 ton',
        'customer_id' => $customer->id,
        'type' => EquipmentType::AirConditioning->value,
    ])->assertRedirect(route('equipment.index'));

    $this->assertDatabaseHas('equipment', [
        'name' => 'Minisplit 1.5 ton',
        'company_id' => $company->id,
        'customer_id' => $customer->id,
    ]);
});

test('a user without a company cannot create equipment', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('equipment.store'), [
        'name' => 'Equipo',
        'customer_id' => 1,
        'type' => EquipmentType::Other->value,
    ])->assertForbidden();
});

test('a technician cannot create equipment for a customer from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $customerB = Customer::factory()->forCompany($companyB)->create();
    $user = User::factory()->forCompany($companyA)->create();

    $this->actingAs($user)->post(route('equipment.store'), [
        'name' => 'Intruso',
        'customer_id' => $customerB->id,
        'type' => EquipmentType::Other->value,
    ])->assertSessionHasErrors('customer_id');
});

test('a technician only sees equipment of their own company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();

    Equipment::factory()->forCompany($companyA)->create(['name' => 'Equipo A']);
    Equipment::factory()->forCompany($companyB)->create(['name' => 'Equipo B']);

    $ids = Equipment::query()->forCompany($companyA)->pluck('id')->all();
    $all = Equipment::query()->pluck('id')->all();

    expect($ids)->not->toContain(Equipment::where('name', 'Equipo B')->value('id'))
        ->and($all)->toContain(Equipment::where('name', 'Equipo A')->value('id'));
});

test('a technician cannot view or update equipment from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();
    $equipmentB = Equipment::factory()->forCompany($companyB)->create();

    $this->actingAs($user)->get(route('equipment.show', $equipmentB))->assertForbidden();
    $this->actingAs($user)->put(route('equipment.update', $equipmentB), [
        'name' => 'Hackeado',
    ])->assertForbidden();
});

test('name and customer and type are required to create equipment', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();

    $this->actingAs($user)->post(route('equipment.store'), [
        'name' => '',
        'customer_id' => '',
        'type' => '',
    ])->assertSessionHasErrors(['name', 'customer_id', 'type']);
});

test('a customer has many equipment through the relation', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->forCompany($company)->create();

    Equipment::factory()->forCompany($company)->forCustomer($customer)->count(2)->create();

    expect($customer->equipment()->count())->toBe(2);
});

test('equipment can be deleted (soft) by an admin of its company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->admin()->create();
    $equipment = Equipment::factory()->forCompany($company)->create();

    $this->actingAs($user)->delete(route('equipment.destroy', $equipment))
        ->assertRedirect(route('equipment.index'));

    $this->assertSoftDeleted('equipment', ['id' => $equipment->id]);
});

test('the equipment show lists only its service orders within the company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $customer = Customer::factory()->forCompany($company)->create();
    $equipment = Equipment::factory()->forCompany($company)->forCustomer($customer)->create();

    ServiceOrder::factory()->forCompany($company)->create([
        'equipment_id' => $equipment->id,
        'customer_id' => $customer->id,
        'technician_id' => $user->id,
    ]);

    $this->actingAs($user)->get(route('equipment.show', $equipment))->assertOk();

    expect(ServiceOrder::where('equipment_id', $equipment->id)->count())->toBe(1);
});

test('a technician cannot view the history of equipment from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();
    $equipmentB = Equipment::factory()->forCompany($companyB)->create();

    $this->actingAs($user)->get(route('equipment.show', $equipmentB))->assertForbidden();
});
