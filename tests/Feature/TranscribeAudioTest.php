<?php

use App\Enums\AudioRecordStatus;
use App\Jobs\TranscribeAudioJob;
use App\Models\AudioRecord;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\Providers\FakeTranscriptionProvider;
use App\Services\Providers\TranscriptionProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Throwable;

test('the transcription provider resolves to the fake driver from config', function () {
    config()->set('ai.transcription.driver', 'fake');

    $provider = app(TranscriptionProvider::class);

    expect($provider)->toBeInstanceOf(FakeTranscriptionProvider::class)
        ->and($provider->transcribe('/tmp/not-a-file.webm'))->toContain('capacitor');
});

test('transcribing a voice note sets the transcript and marks it transcribed', function () {
    Storage::fake('local');

    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $audio = AudioRecord::factory()->forServiceOrder($order)->status(AudioRecordStatus::Uploaded)->create();
    Storage::disk('local')->put($audio->path, 'fake-audio-bytes');

    (new TranscribeAudioJob($audio))->handle(app(TranscriptionProvider::class));

    $audio->refresh();

    expect($audio->status)->toBe(AudioRecordStatus::Transcribed)
        ->and($audio->transcript_text)->toContain('capacitor')
        ->and($audio->transcription_ms)->not->toBeNull();
});

test('a failing provider marks the voice note as failed', function () {
    Storage::fake('local');

    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $audio = AudioRecord::factory()->forServiceOrder($order)->status(AudioRecordStatus::Uploaded)->create();
    Storage::disk('local')->put($audio->path, 'fake-audio-bytes');

    $failing = new class implements TranscriptionProvider
    {
        public function transcribe(string $path, ?string $language = null): string
        {
            throw new RuntimeException('Servicio de transcripción no disponible');
        }
    };

    try {
        (new TranscribeAudioJob($audio))->handle($failing);
        $this->fail('Expected a RuntimeException to be thrown.');
    } catch (Throwable $e) {
        expect($e->getMessage())->toBe('Servicio de transcripción no disponible');
    }

    $audio->refresh();
    expect($audio->status)->toBe(AudioRecordStatus::Failed)
        ->and($audio->transcription_error)->toContain('no disponible');
});

test('the failed callback marks the voice note as failed when attempts are exhausted', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $audio = AudioRecord::factory()->forServiceOrder($order)->status(AudioRecordStatus::Transcribed)->create();

    $job = new TranscribeAudioJob($audio);
    $job->failed(new RuntimeException('se agotaron intentos'));

    $audio->refresh();
    expect($audio->status)->toBe(AudioRecordStatus::Failed)
        ->and($audio->transcription_error)->toContain('agotaron');
});

test('uploading a voice note dispatches the transcription job', function () {
    Queue::fake();
    Storage::fake('local');

    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $this->actingAs($user)->postJson(route('service-orders.audio', $order), [
        'audio' => UploadedFile::fake()->create('nota.webm', 100, 'audio/webm'),
        'duration_ms' => 1000,
    ])->assertOk();

    Queue::assertPushed(TranscribeAudioJob::class);
});
