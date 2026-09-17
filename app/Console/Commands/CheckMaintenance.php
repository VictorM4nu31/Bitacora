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
            ->where(function ($query) {
                $query->whereNull('last_notified_at')
                    ->orWhereDate('last_notified_at', '<', now()->toDateString());
            })
            ->with(['equipment', 'customer'])
            ->get();

        foreach ($due->groupBy('company_id') as $companyId => $schedules) {
            User::query()
                ->where('company_id', $companyId)
                ->role('technician')
                ->get()
                ->each(function (User $user) use ($schedules) {
                    foreach ($schedules as $schedule) {
                        $user->notify(new MaintenanceDue($schedule));
                    }
                });
        }

        if ($due->isNotEmpty()) {
            MaintenanceSchedule::whereIn('id', $due->pluck('id'))
                ->update(['last_notified_at' => now()]);
        }

        $this->info("Checked maintenance: {$due->count()} schedule(s) due.");

        return self::SUCCESS;
    }
}
