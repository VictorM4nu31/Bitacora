<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

test('a technician can upload a photo to their service order', function () {
    Storage::fake('local');

    $company = Company::factory()->create();
    $customer = Customer::factory()->forCompany($company)->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => $customer->id,
    ]);

    $file = UploadedFile::fake()->image('evidencia.jpg', 800, 600);

    $this->actingAs($user)->postJson(route('service-orders.photos', $order), [
        'photo' => $file,
        'caption' => 'Antes del cambio',
    ])->assertOk()->assertJsonStructure(['id', 'url', 'caption']);

    $this->assertDatabaseHas('service_photos', [
        'service_order_id' => $order->id,
        'original_name' => 'evidencia.jpg',
        'caption' => 'Antes del cambio',
    ]);

    Storage::disk('local')->assertExists(ServicePhoto::first()->path);
});

test('a user with view-only access cannot upload or delete photos', function () {
    Storage::fake('local');

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole(Role::findOrCreate('viewer')->syncPermissions(['view services']));
    $order = ServiceOrder::factory()->forCompany($company)->create();
    $photo = ServicePhoto::factory()->forServiceOrder($order)->create();

    $this->actingAs($user)->postJson(route('service-orders.photos', $order), [
        'photo' => UploadedFile::fake()->image('x.jpg'),
    ])->assertForbidden();

    $this->actingAs($user)->deleteJson(route('service-photos.destroy', $photo))
        ->assertForbidden();
});

test('a technician can delete a photo from their service order', function () {
    Storage::fake('local');

    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
    ]);
    $photo = ServicePhoto::factory()->forServiceOrder($order)->create();
    Storage::disk('local')->put($photo->path, 'fake-image-bytes');

    $this->actingAs($user)->deleteJson(route('service-photos.destroy', $photo))
        ->assertOk()
        ->assertJson(['deleted' => true]);

    $this->assertDatabaseMissing('service_photos', ['id' => $photo->id]);
    Storage::disk('local')->assertMissing($photo->path);
});

test('a user cannot upload a photo to a service order from another company', function () {
    Storage::fake('local');

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();
    $orderB = ServiceOrder::factory()->forCompany($companyB)->create();

    $this->actingAs($user)->postJson(route('service-orders.photos', $orderB), [
        'photo' => UploadedFile::fake()->image('x.jpg'),
    ])->assertForbidden();
});

test('an upload requires a valid image', function () {
    Storage::fake('local');

    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
    ]);

    $this->actingAs($user)->postJson(route('service-orders.photos', $order), [
        'photo' => UploadedFile::fake()->create('nota.webm', 100, 'audio/webm'),
    ])->assertJsonValidationErrors('photo');
});

test('a technician can serve the private photo file of their service order', function () {
    Storage::fake('local');

    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
    ]);
    $photo = ServicePhoto::factory()->forServiceOrder($order)->create();
    Storage::disk('local')->put($photo->path, 'fake-image-bytes');

    $this->actingAs($user)->get(route('service-photos.file', $photo))->assertOk();
});

test('a user cannot serve a photo from another company', function () {
    Storage::fake('local');

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();
    $orderB = ServiceOrder::factory()->forCompany($companyB)->create();
    $photoB = ServicePhoto::factory()->forServiceOrder($orderB)->create();

    $this->actingAs($user)->get(route('service-photos.file', $photoB))->assertForbidden();
});
