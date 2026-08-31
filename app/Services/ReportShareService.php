<?php

namespace App\Services;

use App\Models\ServiceReport;
use Illuminate\Support\Facades\URL;

/**
 * Builds expiring links so a customer can view a report without logging in.
 */
class ReportShareService
{
    /**
     * An expiring, signed URL that opens the public customer view.
     */
    public function shareUrl(ServiceReport $report, int $minutes = 1440): string
    {
        return URL::signedRoute('reports.shared', ['service_report' => $report], now()->addMinutes($minutes));
    }
}
