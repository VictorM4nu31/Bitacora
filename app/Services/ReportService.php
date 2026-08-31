<?php

namespace App\Services;

use App\Enums\ReportStatus;
use App\Models\AudioRecord;
use App\Models\ReportEvent;
use App\Models\ServiceReport;
use App\Models\User;

/**
 * Orchestrates the lifecycle of a service report.
 *
 * The human always has the final say: the AI only fills a draft, and the
 * record is finalized after an explicit confirmation. Every transition is
 * recorded into report_events for full traceability.
 */
class ReportService
{
    /**
     * Create (or refresh) a draft report for a service order from an analyzed
     * voice note. Never overwrites an already finalized report.
     */
    public function createDraftFromAudio(AudioRecord $audio): ServiceReport
    {
        $order = $audio->serviceOrder;
        $extracted = $audio->extracted_data ?? [];

        $report = ServiceReport::firstOrNew(['service_order_id' => $order->id]);

        if ($report->status === ReportStatus::Finalized) {
            return $report;
        }

        $wasCreated = ! $report->exists;

        $report->fill([
            'company_id' => $order->company_id,
            'customer_id' => $order->customer_id,
            'equipment_id' => $order->equipment_id,
            'technician_id' => $order->technician_id,
            'audio_record_id' => $audio->id,
            'arrival_time' => $extracted['arrival_time'] ?? null,
            'equipment_type' => $extracted['equipment_type'] ?? null,
            'problem' => $extracted['problem'] ?? null,
            'diagnosis' => $extracted['diagnosis'] ?? null,
            'work_done' => $extracted['work_done'] ?? null,
            'tests_performed' => $extracted['tests_performed'] ?? null,
            'result' => $extracted['result'] ?? null,
            'total_cost' => $extracted['total_cost'] ?? null,
            'currency' => $extracted['currency'] ?? 'MXN',
            'llm_raw_json' => $extracted,
            'llm_confidence' => $extracted['confidence'] ?? null,
            'status' => ReportStatus::Draft,
        ]);

        $report->save();

        $this->record($report, $wasCreated ? 'created' : 'refreshed', ReportStatus::Draft, null, [
            'audio_record_id' => $audio->id,
            'transcription_ms' => $audio->transcription_ms,
            'analysis_ms' => $audio->analysis_ms,
            'analysis_provider' => $audio->analysis_provider,
        ]);

        return $report;
    }

    /**
     * Update a report from technician corrections (keeps the draft status).
     *
     * @param  array<string, mixed>  $data
     */
    public function update(ServiceReport $report, array $data, ?User $user = null): ServiceReport
    {
        $report->fill($data);
        $report->save();

        $this->record($report, 'updated', ReportStatus::Draft, $user, ['fields' => array_keys($data)]);

        return $report;
    }

    /**
     * Mark a report as finalized after human confirmation.
     */
    public function finalize(ServiceReport $report, ?User $user = null): ServiceReport
    {
        $order = $report->serviceOrder;
        $completed = false;

        if ($order && $order->status->value === 'pending') {
            $order->update(['status' => 'completed', 'completed_at' => now()]);
            $completed = true;
        }

        $report->update(['status' => ReportStatus::Finalized]);
        $this->record($report, 'finalized', ReportStatus::Finalized, $user, ['order_completed' => $completed]);

        return $report;
    }

    /**
     * Persist an audit event for a report lifecycle transition.
     *
     * @param  array<string, mixed>  $metadata
     */
    private function record(ServiceReport $report, string $action, ReportStatus $status, ?User $user = null, array $metadata = []): void
    {
        ReportEvent::create([
            'company_id' => $report->company_id,
            'service_order_id' => $report->service_order_id,
            'service_report_id' => $report->id,
            'user_id' => $user?->id,
            'action' => $action,
            'old_status' => $report->getOriginal('status')?->value,
            'new_status' => $status->value,
            'metadata' => $metadata,
        ]);
    }
}
