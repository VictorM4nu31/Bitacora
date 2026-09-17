<?php

namespace App\Policies;

use App\Enums\ReportStatus;
use App\Models\ServiceReport;
use App\Models\User;

class ServiceReportPolicy
{
    /**
     * Determine whether the user can view the service report.
     */
    public function view(User $user, ServiceReport $report): bool
    {
        return $user->company_id === $report->company_id;
    }

    /**
     * Determine whether the user can update the service report.
     */
    public function update(User $user, ServiceReport $report): bool
    {
        return $user->company_id === $report->company_id
            && $report->status === ReportStatus::Draft
            && ($user->isAdmin() || $user->hasPermissionTo('update reports'));
    }

    /**
     * Determine whether the user can finalize the service report.
     */
    public function finalize(User $user, ServiceReport $report): bool
    {
        return $user->company_id === $report->company_id
            && $report->status === ReportStatus::Draft
            && ($user->isAdmin() || $user->hasPermissionTo('finalize reports'));
    }

    /**
     * Determine whether the user can generate a PDF of the report.
     */
    public function generatePdf(User $user, ServiceReport $report): bool
    {
        return $user->company_id === $report->company_id
            && $report->status === ReportStatus::Finalized
            && ($user->isAdmin() || $user->hasPermissionTo('generate pdf'));
    }

    /**
     * Determine whether the user can share the report with the customer.
     */
    public function share(User $user, ServiceReport $report): bool
    {
        return $user->company_id === $report->company_id
            && $report->status === ReportStatus::Finalized
            && ($user->isAdmin() || $user->hasPermissionTo('share reports'));
    }
}
