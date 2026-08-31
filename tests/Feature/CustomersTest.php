<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;

test('a technician can create a customer within their company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();

    $this->actingAs($user)->post(route('customers.store'), [
        'name' => 'Oficina 204',
        'phone' => '5512345678',
    ])->assertRedirect(route('customers.index'));

    $this->assertDatabaseHas('customers', [
        'name' => 'Oficina 204',
        'company_id' => $company->id,
    ]);
});

test('a user without a company cannot create a customer', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('customers.store'), [
        'name' => 'Sin empresa',
    ])->assertForbidden();
});

test('a technician only sees customers of their own company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();

    Customer::factory()->forCompany($companyA)->create(['name' => 'Cliente A']);
    Customer::factory()->forCompany($companyB)->create(['name' => 'Cliente B']);

    $ids = Customer::query()->forCompany($companyA)->pluck('id')->all();
    $all = Customer::query()->pluck('id')->all();

    expect($ids)->not->toContain(Customer::where('name', 'Cliente B')->value('id'))
        ->and($all)->toContain(Customer::where('name', 'Cliente A')->value('id'));
});

test('a technician cannot view a customer from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();
    $customerB = Customer::factory()->forCompany($companyB)->create();

    $this->actingAs($user)->get(route('customers.show', $customerB))->assertForbidden();
});

test('a technician cannot update a customer from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();
    $customerB = Customer::factory()->forCompany($companyB)->create();

    $this->actingAs($user)->put(route('customers.update', $customerB), [
        'name' => 'Hackeado',
    ])->assertForbidden();
});

test('name is required to create a customer', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();

    $this->actingAs($user)->post(route('customers.store'), [
        'name' => '',
    ])->assertSessionHasErrors('name');
});

test('a customer can be deleted (soft) by its company member', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $customer = Customer::factory()->forCompany($company)->create();

    $this->actingAs($user)->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));

    $this->assertSoftDeleted('customers', ['id' => $customer->id]);
});
