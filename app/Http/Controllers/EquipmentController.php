<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentType;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Customer;
use App\Models\Equipment;
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
    public function show(Equipment $equipment): Response
    {
        Gate::authorize('view', $equipment);

        $equipment->load('customer');

        return Inertia::render('equipment/show', [
            'equipment' => $equipment,
            ...$this->formOptions(request()),
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
