<?php

namespace App\Services;

use App\Enums\ReportStatus;
use App\Models\AudioRecord;
use App\Models\ServiceReport;

/**
 * Orchestrates the lifecycle of a service report.
 *
 * The human always has the final say: the AI only fills a draft, and the
 * record is finalized after an explicit confirmation.
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

        return $report;
    }

    /**
     * Update a report from technician corrections (keeps the draft status).
     */
    public function update(ServiceReport $report, array $data): ServiceReport
    {
        $report->fill($data);
        $report->save();

        return $report;
    }

    /**
     * Mark a report as finalized after human confirmation.
     */
    public function finalize(ServiceReport $report): ServiceReport
    {
        $report->update(['status' => ReportStatus::Finalized]);

        $order = $report->serviceOrder;
        if ($order && $order->status->value === 'pending') {
            $order->update(['status' => 'completed', 'completed_at' => now()]);
        }

        return $report;
    }
}
