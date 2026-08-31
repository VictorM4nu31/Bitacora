<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentType;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the equipment within the user's company.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Equipment::class);

        $equipment = Equipment::query()
            ->forCompany($request->user()->company_id)
            ->with('customer:id,name')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return Inertia::render('equipment/index', [
            'equipment' => $equipment,
            ...$this->formOptions($request),
        ]);
    }

    /**
     * Store a newly created equipment in storage.
     */
    public function store(StoreEquipmentRequest $request): RedirectResponse
    {
        Gate::authorize('create', Equipment::class);

        Equipment::create($request->validated() + [
            'company_id' => $request->user()->company_id,
        ]);

        return to_route('equipment.index');
    }

    /**
     * Display the specified equipment.
     */
    public function show(Request $request, Equipment $equipment): Response
    {
        Gate::authorize('view', $equipment);

        $equipment->load('customer');

        $history = ServiceOrder::query()
            ->forCompany($request->user()->company_id)
            ->where('equipment_id', $equipment->id)
            ->with(['customer:id,name', 'report'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ServiceOrder $order) => [
                'id' => $order->id,
                'status' => $order->status->value,
                'statusLabel' => $order->status->label(),
                'customer_name' => $order->customer?->name,
                'report_status' => $order->report?->status->value,
                'date' => $order->created_at->format('d/m/Y H:i'),
            ]);

        $maintenance = MaintenanceSchedule::query()
            ->forCompany($request->user()->company_id)
            ->where('equipment_id', $equipment->id)
            ->orderBy('next_due_at')
            ->get()
            ->map(fn (MaintenanceSchedule $schedule) => [
                'id' => $schedule->id,
                'interval_days' => $schedule->interval_days,
                'next_due_at' => $schedule->next_due_at?->format('d/m/Y'),
                'enabled' => $schedule->enabled,
            ]);

        return Inertia::render('equipment/show', [
            'equipment' => $equipment,
            'history' => $history,
            'maintenance' => $maintenance,
            'maintenanceUrl' => route('equipment.maintenance', $equipment),
            'completeMaintenanceUrl' => route('maintenance-schedules.complete', ['maintenance_schedule' => '__ID__']),
            ...$this->formOptions($request),
        ]);
    }

    /**
     * Shared options used by the equipment form (store/update).
     *
     * @return array{customers: Collection<int, Customer>, types: array<int, array{value: string, label: string}>}
     */
    private function formOptions(Request $request): array
    {
        return [
            'customers' => Customer::query()
                ->forCompany($request->user()->company_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'types' => collect(EquipmentType::cases())
                ->map(fn (EquipmentType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ])
                ->all(),
        ];
    }

    /**
     * Update the specified equipment in storage.
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        Gate::authorize('update', $equipment);

        $equipment->update($request->validated());

        return to_route('equipment.show', $equipment);
    }

    /**
     * Remove the specified equipment from storage.
     */
    public function destroy(Request $request, Equipment $equipment): RedirectResponse
    {
        Gate::authorize('delete', $equipment);

        $equipment->delete();

        return to_route('equipment.index');
    }
}
