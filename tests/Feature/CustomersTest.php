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

test('customers can be searched by name, email or phone', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();

    Customer::factory()->forCompany($company)->create([
        'name' => 'Oficina Norte',
        'email' => 'norte@example.test',
        'phone' => '5550001111',
    ]);
    Customer::factory()->forCompany($company)->create([
        'name' => 'Taller Sur',
        'email' => 'sur@example.test',
        'phone' => '5550002222',
    ]);

    $response = $this->actingAs($user)->get(route('customers.index', [
        'search' => 'norte@example.test',
    ]));

    $response->assertInertia(fn ($page) => $page
        ->where('filters.search', 'norte@example.test')
        ->has('customers.data', 1)
        ->where('customers.data.0.name', 'Oficina Norte')
    );
});

test('customer pagination preserves the search filter', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();

    Customer::factory()->count(16)->forCompany($company)->create([
        'name' => 'Cliente recurrente',
    ]);

    $response = $this->actingAs($user)->get(route('customers.index', [
        'search' => 'recurrente',
    ]));

    $response->assertInertia(fn ($page) => $page
        ->where('filters.search', 'recurrente')
        ->has('customers.data', 15)
        ->where('customers.next_page_url', fn (?string $url) => $url !== null
            && str_contains($url, 'search=recurrente')
        )
    );
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

test('a customer can be deleted (soft) by an admin of its company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->admin()->create();
    $customer = Customer::factory()->forCompany($company)->create();

    $this->actingAs($user)->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));

    $this->assertSoftDeleted('customers', ['id' => $customer->id]);
});
