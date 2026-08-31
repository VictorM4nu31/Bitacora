<?php

use App\Enums\AudioRecordStatus;
use App\Jobs\AnalyzeTranscriptJob;
use App\Models\AudioRecord;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\Providers\ExtractionProvider;
use App\Services\Providers\FakeExtractionProvider;
use App\Support\Dto\ExtractedReport;

test('the extraction provider resolves to the fake driver from config', function () {
    config()->set('ai.extraction.driver', 'fake');

    $provider = app(ExtractionProvider::class);

    expect($provider)->toBeInstanceOf(FakeExtractionProvider::class)
        ->and($provider->extract('No enfriaba'))->toBeInstanceOf(ExtractedReport::class);
});

test('analyzing a transcript stores the extracted data on the audio', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $audio = AudioRecord::factory()->forServiceOrder($order)->status(AudioRecordStatus::Transcribed)->create([
        'transcript_text' => 'Llegue a las diez veinte... capacitor danado... pago 850.',
    ]);

    (new AnalyzeTranscriptJob($audio))->handle(app(ExtractionProvider::class));

    $audio->refresh();

    expect($audio->extracted_data['work_done'])->toBe('cambio de capacitor de 35 μF')
        ->and((float) $audio->extracted_data['total_cost'])->toBe(850.0)
        ->and($audio->analysis_provider)->toBe('fake');
});

test('analyzing a transcript skips when there is no transcript', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $audio = AudioRecord::factory()->forServiceOrder($order)->status(AudioRecordStatus::Uploaded)->create([
        'transcript_text' => null,
    ]);

    (new AnalyzeTranscriptJob($audio))->handle(app(ExtractionProvider::class));

    expect($audio->fresh()->extracted_data)->toBeNull();
});

test('a failing extraction provider stores the analysis error and rethrows', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $audio = AudioRecord::factory()->forServiceOrder($order)->status(AudioRecordStatus::Transcribed)->create([
        'transcript_text' => 'texto',
    ]);

    $failing = new class implements ExtractionProvider
    {
        public function extract(string $transcript, ?string $locale = null): ExtractedReport
        {
            throw new RuntimeException('LLM no disponible');
        }
    };

    try {
        (new AnalyzeTranscriptJob($audio))->handle($failing);
        $this->fail('Expected RuntimeException');
    } catch (Throwable $e) {
        expect($e->getMessage())->toBe('LLM no disponible');
    }

    expect($audio->fresh()->analysis_error)->toContain('no disponible');
});

test('the extracted data is exposed via the status endpoint', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $audio = AudioRecord::factory()->forServiceOrder($order)->status(AudioRecordStatus::Transcribed)->create([
        'transcript_text' => 'texto',
        'extracted_data' => ['work_done' => 'cambio', 'missing' => []],
    ]);

    $this->actingAs($user)->getJson(route('audio-records.status', $audio))
        ->assertOk()
        ->assertJsonPath('extracted.work_done', 'cambio');
});
