<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Notifications\MaintenanceDue;
use Illuminate\Console\Command;

class CheckMaintenance extends Command
{
    /**
     * @var string
     */
    protected $signature = 'maintenance:check';

    /**
     * @var string
     */
    protected $description = 'Notify technicians about maintenance schedules that are due';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $due = MaintenanceSchedule::query()
            ->where('enabled', true)
            ->where('next_due_at', '<=', now())
            ->with(['equipment', 'customer'])
            ->get();

        foreach ($due->groupBy('company_id') as $companyId => $schedules) {
            User::query()
                ->where('company_id', $companyId)
                ->get()
                ->each(function (User $user) use ($schedules) {
                    foreach ($schedules as $schedule) {
                        $user->notify(new MaintenanceDue($schedule));
                    }
                });
        }

        $this->info("Checked maintenance: {$due->count()} schedule(s) due.");

        return self::SUCCESS;
    }
}
