<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaintenanceScheduleController extends Controller
{
    /**
     * Schedule a recurring maintenance for a piece of equipment.
     */
    public function store(Request $request, Equipment $equipment): RedirectResponse
    {
        Gate::authorize('view', $equipment);

        $request->validate([
            'interval_days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        MaintenanceSchedule::create([
            'company_id' => $equipment->company_id,
            'equipment_id' => $equipment->id,
            'customer_id' => $equipment->customer_id,
            'interval_days' => $request->integer('interval_days'),
            'next_due_at' => now()->addDays($request->integer('interval_days')),
            'enabled' => true,
        ]);

        return to_route('equipment.show', $equipment);
    }

    /**
     * Mark a maintenance as completed and advance the next due date.
     */
    public function complete(MaintenanceSchedule $maintenanceSchedule): RedirectResponse
    {
        Gate::authorize('view', $maintenanceSchedule->equipment);

        $maintenanceSchedule->advance();

        return to_route('equipment.show', $maintenanceSchedule->equipment);
    }
}
