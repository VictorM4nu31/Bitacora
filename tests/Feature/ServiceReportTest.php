<?php

use App\Enums\AudioRecordStatus;
use App\Enums\ReportStatus;
use App\Models\AudioRecord;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ReportEvent;
use App\Models\ServiceOrder;
use App\Models\ServiceReport;
use App\Models\User;
use App\Services\ReportPdfService;
use App\Services\ReportService;
use App\Services\ReportShareService;
use Illuminate\Support\Facades\Storage;

test('an analyzed voice note creates a draft report for the service order', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $audio = AudioRecord::factory()->forServiceOrder($order)->status(AudioRecordStatus::Transcribed)->create([
        'extracted_data' => [
            'arrival_time' => '10:20',
            'work_done' => 'cambio de capacitor',
            'total_cost' => 850,
            'confidence' => 0.9,
        ],
    ]);

    app(ReportService::class)->createDraftFromAudio($audio);

    $report = ServiceReport::where('service_order_id', $order->id)->first();

    expect($report)->not->toBeNull()
        ->and($report->status)->toBe(ReportStatus::Draft)
        ->and($report->work_done)->toBe('cambio de capacitor')
        ->and((float) $report->total_cost)->toBe(850.0);
});

test('finalizing a report sets the status and does not overwrite it on reanalysis', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);

    $report = ServiceReport::factory()->forServiceOrder($order)->status(ReportStatus::Draft)->create();

    app(ReportService::class)->finalize($report);

    expect($report->fresh()->status)->toBe(ReportStatus::Finalized);

    // Re-analysis must not overwrite a finalized report.
    $audio = AudioRecord::factory()->forServiceOrder($order)->status(AudioRecordStatus::Transcribed)->create([
        'extracted_data' => ['work_done' => 'otro trabajo'],
    ]);
    app(ReportService::class)->createDraftFromAudio($audio);

    expect($report->fresh()->work_done)->toBe('cambio de capacitor de 35 μF');
});

test('a technician can update their draft report', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);
    $report = ServiceReport::factory()->forServiceOrder($order)->status(ReportStatus::Draft)->create();

    $this->actingAs($user)->put(route('service-reports.update', $report), [
        'problem' => 'no enfriaba (corregido)',
        'total_cost' => 950,
    ])->assertRedirect(route('service-orders.show', $order));

    expect($report->fresh()->problem)->toBe('no enfriaba (corregido)')
        ->and((float) $report->fresh()->total_cost)->toBe(950.0);
});

test('a technician cannot update a report from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->forCompany($companyA)->create();
    $orderB = ServiceOrder::factory()->forCompany($companyB)->create([
        'customer_id' => Customer::factory()->forCompany($companyB),
    ]);
    $reportB = ServiceReport::factory()->forServiceOrder($orderB)->create();

    $this->actingAs($user)->put(route('service-reports.update', $reportB), [
        'problem' => 'hack',
    ])->assertForbidden();
});

test('finalizing a report marks the service order as completed', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);
    $report = ServiceReport::factory()->forServiceOrder($order)->status(ReportStatus::Draft)->create();

    app(ReportService::class)->finalize($report);

    expect($order->fresh()->status->value)->toBe('completed')
        ->and($order->fresh()->completed_at)->not->toBeNull();
});

test('the pdf service stores a generated pdf on the private disk', function () {
    Storage::fake('local');

    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);
    $report = ServiceReport::factory()->forServiceOrder($order)->status(ReportStatus::Finalized)->create();

    $path = app(ReportPdfService::class)->generate($report);

    Storage::disk('local')->assertExists($path);
    expect($report->fresh()->pdf_path)->toBe($path);
});

test('the pdf endpoint returns a downloadable pdf', function () {
    Storage::fake('local');

    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);
    $report = ServiceReport::factory()->forServiceOrder($order)->status(ReportStatus::Finalized)->create();

    $this->actingAs($user)->get(route('service-reports.pdf', $report))->assertOk();
});

test('the share service returns a signed url', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);
    $report = ServiceReport::factory()->forServiceOrder($order)->status(ReportStatus::Finalized)->create();

    $url = app(ReportShareService::class)->shareUrl($report, 60);

    expect($url)->toContain('/shared/'.$report->id)
        ->and($url)->toContain('signature')
        ->and($url)->toContain('expires');
});

test('the shared customer view requires a valid signature', function () {
    $company = Company::factory()->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'customer_id' => Customer::factory()->forCompany($company),
    ]);
    $report = ServiceReport::factory()->forServiceOrder($order)->status(ReportStatus::Finalized)->create();
    $report->load('serviceOrder');

    // Without a valid signature -> 403.
    $this->get('/shared/'.$report->id)->assertForbidden();

    // With a valid signature -> 200.
    $url = app(ReportShareService::class)->shareUrl($report, 60);
    $this->get($url)->assertOk();
});

test('creating a draft from audio records an audit event', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);
    $audio = AudioRecord::factory()->forServiceOrder($order)->status(AudioRecordStatus::Transcribed)->create([
        'extracted_data' => ['work_done' => 'x'],
        'transcription_ms' => 120,
        'analysis_ms' => 40,
    ]);

    app(ReportService::class)->createDraftFromAudio($audio);

    $report = $order->fresh()->report;

    expect(ReportEvent::where('service_report_id', $report->id)->count())->toBe(1)
        ->and(ReportEvent::where('service_report_id', $report->id)->value('action'))->toBe('created')
        ->and(ReportEvent::where('service_report_id', $report->id)->value('user_id'))->toBeNull();
});

test('finalizing a report records a finalized audit event by the user', function () {
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company)->create();
    $order = ServiceOrder::factory()->forCompany($company)->create([
        'technician_id' => $user->id,
        'customer_id' => Customer::factory()->forCompany($company),
    ]);
    $report = ServiceReport::factory()->forServiceOrder($order)->status(ReportStatus::Draft)->create();

    app(ReportService::class)->finalize($report, $user);

    $event = ReportEvent::where('service_report_id', $report->id)->latest()->first();

    expect($event->action)->toBe('finalized')
        ->and($event->user_id)->toBe($user->id)
        ->and($event->new_status)->toBe('finalized');
});
