<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Notifications\MaintenanceDue;
use Illuminate\Support\Facades\Notification;

test('an admin can schedule a recurring maintenance for their equipment', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->admin()->create();
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

test('an admin cannot schedule maintenance for equipment from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->admin()->create();
    $equipmentB = Equipment::factory()->forCompany($companyB)->create();

    $this->actingAs($user)->post(route('equipment.maintenance', $equipmentB), [
        'interval_days' => 30,
    ])->assertForbidden();
});

test('completing a maintenance advances the next due date', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->admin()->create();
    $equipment = Equipment::factory()->forCompany($company)->create();
    $schedule = MaintenanceSchedule::factory()->forCompany($company)->forEquipment($equipment)->create();

    $this->actingAs($user)->post(route('maintenance-schedules.complete', $schedule))
        ->assertRedirect();

    $schedule->refresh();
    expect($schedule->last_run_at)->not->toBeNull()
        ->and($schedule->next_due_at->isAfter(now()))->toBeTrue();
});

test('the maintenance check command notifies technicians of due schedules', function () {
    Notification::fake();

    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $equipment = Equipment::factory()->forCompany($company)->create();
    $schedule = MaintenanceSchedule::factory()->forCompany($company)->forEquipment($equipment)->due()->create();

    $this->artisan('maintenance:check')->assertSuccessful();

    Notification::assertSentTo($user, MaintenanceDue::class);
});

test('maintenance reminders are deduplicated on the same day', function () {
    Notification::fake();

    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $equipment = Equipment::factory()->forCompany($company)->create();
    MaintenanceSchedule::factory()->forCompany($company)->forEquipment($equipment)->due()->create();

    $this->artisan('maintenance:check')->assertSuccessful();
    $this->artisan('maintenance:check')->assertSuccessful();

    Notification::assertSentToTimes($user, MaintenanceDue::class, 1);
});

test('the maintenance check command notifies technicians only', function () {
    Notification::fake();

    $company = Company::factory()->create();
    $technician = User::factory()->forCompany($company)->create();
    $member = User::factory()->create(['company_id' => $company->id]);
    $equipment = Equipment::factory()->forCompany($company)->create();
    MaintenanceSchedule::factory()->forCompany($company)->forEquipment($equipment)->due()->create();

    $this->artisan('maintenance:check')->assertSuccessful();

    Notification::assertSentTo($technician, MaintenanceDue::class);
    Notification::assertNotSentTo($member, MaintenanceDue::class);
});

test('schedule requires a valid interval', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->admin()->create();
    $equipment = Equipment::factory()->forCompany($company)->create();

    $this->actingAs($user)->post(route('equipment.maintenance', $equipment), [
        'interval_days' => 0,
    ])->assertSessionHasErrors('interval_days');
});

test('schedule rejects intervals beyond the supported maximum', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->admin()->create();
    $equipment = Equipment::factory()->forCompany($company)->create();

    $this->actingAs($user)->post(route('equipment.maintenance', $equipment), [
        'interval_days' => 3651,
    ])->assertSessionHasErrors('interval_days');
});
