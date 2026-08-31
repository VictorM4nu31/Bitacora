<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinalizeReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\ServiceReport;
use App\Services\ReportPdfService;
use App\Services\ReportService;
use App\Services\ReportShareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceReportController extends Controller
{
    /**
     * Update a report draft from technician corrections.
     */
    public function update(UpdateReportRequest $request, ServiceReport $serviceReport, ReportService $reportService): RedirectResponse
    {
        Gate::authorize('update', $serviceReport);

        $reportService->update($serviceReport, $request->validated());

        return to_route('service-orders.show', $serviceReport->serviceOrder);
    }

    /**
     * Finalize a report after explicit confirmation.
     */
    public function finalize(FinalizeReportRequest $request, ServiceReport $serviceReport, ReportService $reportService): RedirectResponse
    {
        Gate::authorize('update', $serviceReport);

        $reportService->finalize($serviceReport);

        return to_route('service-orders.show', $serviceReport->serviceOrder);
    }

    /**
     * Download the report as a PDF (generating it on demand if needed).
     */
    public function pdf(ServiceReport $serviceReport, ReportPdfService $pdfService): StreamedResponse
    {
        Gate::authorize('view', $serviceReport);

        $path = $serviceReport->pdf_path ?? $pdfService->generate($serviceReport);

        return Storage::disk('local')->download($path, sprintf('reporte-%s.pdf', $serviceReport->id));
    }

    /**
     * Return an expiring customer-facing link for the report.
     */
    public function share(ServiceReport $serviceReport, ReportShareService $shareService): JsonResponse
    {
        Gate::authorize('view', $serviceReport);

        return response()->json([
            'url' => $shareService->shareUrl($serviceReport),
        ]);
    }

    /**
     * Public (signed) customer view of a finalized report.
     */
    public function shared(ServiceReport $serviceReport): Response
    {
        abort_unless($serviceReport->status->value === 'finalized', 404);

        return response()
            ->view('reports.shared', ['report' => $serviceReport])
            ->header('X-Robots-Tag', 'noindex');
    }
}
