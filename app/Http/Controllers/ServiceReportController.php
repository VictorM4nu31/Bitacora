<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinalizeReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\ServiceReport;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

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
}
