<?php

use App\Enums\ServiceOrderStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\ServiceOrder;
use App\Models\User;

test('a technician can create a service order within their company', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->forCompany($company)->create();
    $user = User::factory()->forCompany($company)->create();

    $this->actingAs($user)->post(route('service-orders.store'), [
        'customer_id' => $customer->id,
    ])->assertRedirect(route('service-orders.index'));

    $this->assertDatabaseHas('service_orders', [
        'customer_id' => $customer->id,
        'company_id' => $company->id,
        'technician_id' => $user->id,
        'status' => ServiceOrderStatus::Pending->value,
    ]);
});

test('a user without a company cannot create a service order', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('service-orders.store'), [
        'customer_id' => 1,
    ])->assertForbidden();
});

test('a technician cannot create a service order for a customer from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $customerB = Customer::factory()->forCompany($companyB)->create();
    $user = User::factory()->forCompany($companyA)->create();

    $this->actingAs($user)->post(route('service-orders.store'), [
        'customer_id' => $customerB->id,
    ])->assertSessionHasErrors('customer_id');
});

test('a service order cannot use equipment owned by another customer', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $customer = Customer::factory()->forCompany($company)->create();
    $otherCustomer = Customer::factory()->forCompany($company)->create();
    $equipment = Equipment::factory()->forCompany($company)->forCustomer($otherCustomer)->create();

    $this->actingAs($user)->post(route('service-orders.store'), [
        'customer_id' => $customer->id,
        'equipment_id' => $equipment->id,
    ])->assertSessionHasErrors('equipment_id');
});

test('service orders can be searched and paginated by related records', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $customer = Customer::factory()->forCompany($company)->create([
        'name' => 'Clinica Central',
    ]);

    ServiceOrder::factory()->count(16)->forCompany($company)->create([
        'customer_id' => $customer->id,
        'technician_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('service-orders.index', [
        'search' => 'Clinica Central',
    ]));

    $response->assertInertia(fn ($page) => $page
        ->where('filters.search', 'Clinica Central')
        ->has('orders.data', 15)
        ->where('orders.next_page_url', fn (?string $url) => $url !== null
            && str_contains(urldecode($url), 'search=Clinica Central')
        )
    );
});

test('a technician only sees service orders of their own company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();

    ServiceOrder::factory()->forCompany($companyA)->create(['customer_id' => Customer::factory()->forCompany($companyA)]);
    ServiceOrder::factory()->forCompany($companyB)->create(['customer_id' => Customer::factory()->forCompany($companyB)]);

    $ids = ServiceOrder::query()->forCompany($companyA)->pluck('id')->all();

    expect($ids)->toHaveCount(1);
});

test('a technician cannot view or update a service order from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();
    $orderB = ServiceOrder::factory()->forCompany($companyB)->create([
        'customer_id' => Customer::factory()->forCompany($companyB),
    ]);

    $this->actingAs($user)->get(route('service-orders.show', $orderB))->assertForbidden();
    $this->actingAs($user)->put(route('service-orders.update', $orderB), [
        'status' => ServiceOrderStatus::Completed->value,
    ])->assertForbidden();
});

test('starting a service sets the started timestamp', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $this->actingAs($user)->put(route('service-orders.update', $order), [
        'status' => ServiceOrderStatus::InProgress->value,
    ])->assertRedirect();

    expect($order->fresh()->status)->toBe(ServiceOrderStatus::InProgress)
        ->and($order->fresh()->started_at)->not->toBeNull();
});

test('completing a service sets the completed timestamp', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->status(ServiceOrderStatus::InProgress)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $this->actingAs($user)->put(route('service-orders.update', $order), [
        'status' => ServiceOrderStatus::Completed->value,
    ])->assertRedirect();

    expect($order->fresh()->status)->toBe(ServiceOrderStatus::Completed)
        ->and($order->fresh()->completed_at)->not->toBeNull();
});

test('a service order can be deleted by an admin of its company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->admin()->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $this->actingAs($user)->delete(route('service-orders.destroy', $order))
        ->assertRedirect(route('service-orders.index'));

    $this->assertDatabaseMissing('service_orders', ['id' => $order->id]);
});
