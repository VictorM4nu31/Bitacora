<?php

namespace App\Policies;

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
        return $user->company_id === $report->company_id;
    }
}
