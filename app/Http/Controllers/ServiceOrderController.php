<?php

namespace App\Http\Controllers;

use App\Enums\ServiceOrderStatus;
use App\Http\Requests\StoreServiceOrderRequest;
use App\Http\Requests\UpdateServiceOrderRequest;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\ServiceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ServiceOrderController extends Controller
{
    /**
     * Display a listing of the service orders within the user's company.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', ServiceOrder::class);

        $orders = ServiceOrder::query()
            ->forCompany($request->user()->company_id)
            ->with(['customer:id,name', 'equipment:id,name', 'technician:id,name'])
            ->orderByDesc('updated_at')
            ->paginate(15);

        return Inertia::render('service_orders/index', [
            'orders' => $orders,
            'statuses' => $this->statusOptions(),
            ...$this->formOptions($request),
        ]);
    }

    /**
     * Store a newly created service order in storage.
     */
    public function store(StoreServiceOrderRequest $request): RedirectResponse
    {
        Gate::authorize('create', ServiceOrder::class);

        ServiceOrder::create($request->validated() + [
            'company_id' => $request->user()->company_id,
            'technician_id' => $request->user()->id,
            'status' => ServiceOrderStatus::Pending,
        ]);

        return to_route('service-orders.index');
    }

    /**
     * Display the specified service order.
     */
    public function show(ServiceOrder $serviceOrder): Response
    {
        Gate::authorize('view', $serviceOrder);

        $serviceOrder->load(['customer', 'equipment', 'technician']);
        $audioRecords = $serviceOrder
            ->audioRecords()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($audio) => [
                'id' => $audio->id,
                'status' => $audio->status->value,
                'duration_ms' => $audio->duration_ms,
                'statusUrl' => route('audio-records.status', $audio),
            ]);

        return Inertia::render('service_orders/show', [
            'order' => $serviceOrder,
            'statuses' => $this->statusOptions(),
            'audioUrl' => route('service-orders.audio', $serviceOrder),
            'audioRecords' => $audioRecords,
            ...$this->formOptions(request()),
        ]);
    }

    /**
     * Update the specified service order in storage.
     */
    public function update(UpdateServiceOrderRequest $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        Gate::authorize('update', $serviceOrder);

        $serviceOrder->update($this->payloadWithStatusTransition($request->validated(), $serviceOrder));

        return to_route('service-orders.show', $serviceOrder);
    }

    /**
     * Remove the specified service order from storage.
     */
    public function destroy(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        Gate::authorize('delete', $serviceOrder);

        $serviceOrder->delete();

        return to_route('service-orders.index');
    }

    /**
     * Shared options used by the service order forms.
     *
     * @return array{customers: Collection<int, Customer>, equipment: Collection<int, Equipment>}
     */
    private function formOptions(Request $request): array
    {
        $companyId = $request->user()->company_id;

        return [
            'customers' => Customer::query()
                ->forCompany($companyId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'equipment' => Equipment::query()
                ->forCompany($companyId)
                ->with('customer:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'customer_id']),
        ];
    }

    /**
     * Status options (value + human label) used by the UI.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return collect(ServiceOrderStatus::cases())
            ->map(fn (ServiceOrderStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->all();
    }

    /**
     * Add derived timestamps when the status transitions.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payloadWithStatusTransition(array $data, ServiceOrder $order): array
    {
        $status = isset($data['status']) ? ServiceOrderStatus::from($data['status']) : null;

        if ($status === ServiceOrderStatus::InProgress && $order->started_at === null) {
            $data['started_at'] = now();
        }

        if ($status === ServiceOrderStatus::Completed && $order->completed_at === null) {
            $data['completed_at'] = now();
        }

        return $data;
    }
}
