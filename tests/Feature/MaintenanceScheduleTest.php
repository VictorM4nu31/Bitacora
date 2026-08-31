<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Notifications\MaintenanceDue;
use Illuminate\Support\Facades\Notification;

test('a technician can schedule a recurring maintenance for their equipment', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $customer = Customer::factory()->forCompany($company)->create();
    $equipment = Equipment::factory()->forCompany($company)->forCustomer($customer)->create();

    $this->actingAs($user)->post(route('equipment.maintenance', $equipment), [
        'interval_days' => 90,
    ])->assertRedirect(route('equipment.show', $equipment));

    $this->assertDatabaseHas('maintenance_schedules', [
        'equipment_id' => $equipment->id,
        'interval_days' => 90,
        'enabled' => true,
    ]);
});

test('a technician cannot schedule maintenance for equipment from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();
    $equipmentB = Equipment::factory()->forCompany($companyB)->create();

    $this->actingAs($user)->post(route('equipment.maintenance', $equipmentB), [
        'interval_days' => 30,
    ])->assertForbidden();
});

test('completing a maintenance advances the next due date', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $equipment = Equipment::factory()->forCompany($company)->create();
    $schedule = MaintenanceSchedule::factory()->forCompany($company)->forEquipment($equipment)->create();

    $this->actingAs($user)->post(route('maintenance-schedules.complete', $schedule))
        ->assertRedirect();

    $schedule->refresh();
    expect($schedule->last_run_at)->not->toBeNull()
        ->and($schedule->next_due_at->isAfter(now()))->toBeTrue();
});

test('the maintenance check command notifies company users of due schedules', function () {
    Notification::fake();

    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $equipment = Equipment::factory()->forCompany($company)->create();
    $schedule = MaintenanceSchedule::factory()->forCompany($company)->forEquipment($equipment)->due()->create();

    $this->artisan('maintenance:check')->assertSuccessful();

    Notification::assertSentTo($user, MaintenanceDue::class);
});

test('schedule requires a valid interval', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $equipment = Equipment::factory()->forCompany($company)->create();

    $this->actingAs($user)->post(route('equipment.maintenance', $equipment), [
        'interval_days' => 0,
    ])->assertSessionHasErrors('interval_days');
});
