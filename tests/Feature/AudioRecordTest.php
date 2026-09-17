<?php

use App\Enums\AudioRecordStatus;
use App\Models\AudioRecord;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

test('a technician can upload a voice note to their service order', function () {
    Storage::fake('local');
    Queue::fake();

    $company = Company::factory()->create();
    $customer = Customer::factory()->forCompany($company)->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => $customer->id,
    ]);

    $file = UploadedFile::fake()->create('nota.webm', 100, 'audio/webm');

    $response = $this->actingAs($user)->postJson(route('service-orders.audio', $order), [
        'audio' => $file,
        'duration_ms' => 15000,
    ])->assertOk();

    $response->assertJsonStructure(['id', 'status', 'statusUrl']);

    $this->assertDatabaseHas('audio_records', [
        'service_order_id' => $order->id,
        'recorder_id' => $user->id,
        'status' => AudioRecordStatus::Uploaded->value,
    ]);

    Storage::disk('local')->assertExists(AudioRecord::first()->path);
});

test('a user cannot upload audio to a service order from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();
    $orderB = ServiceOrder::factory()->forCompany($companyB)->create();

    $file = UploadedFile::fake()->create('nota.webm', 100, 'audio/webm');

    $this->actingAs($user)->postJson(route('service-orders.audio', $orderB), [
        'audio' => $file,
    ])->assertForbidden();
});

test('a user with view-only access cannot upload audio', function () {
    Storage::fake('local');
    Queue::fake();

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole(Role::findOrCreate('viewer')->syncPermissions(['view services']));
    $order = ServiceOrder::factory()->forCompany($company)->create();

    $this->actingAs($user)->postJson(route('service-orders.audio', $order), [
        'audio' => UploadedFile::fake()->create('nota.webm', 100, 'audio/webm'),
    ])->assertForbidden();
});

test('an audio upload requires a valid audio file', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
    ]);

    $this->actingAs($user)->postJson(route('service-orders.audio', $order), [
        'audio' => UploadedFile::fake()->create('foto.png', 100, 'image/png'),
    ])->assertJsonValidationErrors('audio');
});

test('a user can query the status of a voice note they can access', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
    ]);
    $audio = AudioRecord::factory()->forServiceOrder($order)->status(AudioRecordStatus::Uploaded)->create();

    $this->actingAs($user)->getJson(route('audio-records.status', $audio))
        ->assertOk()
        ->assertJson(['status' => AudioRecordStatus::Uploaded->value]);
});

test('a user cannot query the status of a voice note from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();
    $orderB = ServiceOrder::factory()->forCompany($companyB)->create();
    $audioB = AudioRecord::factory()->forServiceOrder($orderB)->create();

    $this->actingAs($user)->getJson(route('audio-records.status', $audioB))->assertForbidden();
});
