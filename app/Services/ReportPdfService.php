<?php

namespace App\Services;

use App\Models\ServiceReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Generates and stores the printable PDF for a service report.
 */
class ReportPdfService
{
    /**
     * Render the report as a PDF and store it on the private disk.
     *
     * @return string the stored path
     */
    public function generate(ServiceReport $report): string
    {
        $path = sprintf('pdfs/%s/%s/%s/%s.pdf', $report->company_id, now()->format('Y/m'), $report->id, Str::uuid());

        $html = view('reports.pdf', ['report' => $report])->render();

        Storage::disk('local')->put($path, Pdf::loadHTML($html)->output());

        $report->update(['pdf_path' => $path]);

        return $path;
    }
}
