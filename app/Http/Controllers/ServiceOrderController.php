<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentType;
use App\Enums\ServiceOrderStatus;
use App\Http\Requests\StoreServiceOrderRequest;
use App\Http\Requests\UpdateServiceOrderRequest;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\ReportEvent;
use App\Models\ServiceOrder;
use App\Models\ServiceReport;
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

        $report = $serviceOrder->report;

        $photos = $serviceOrder->photos()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($photo) => [
                'id' => $photo->id,
                'original_name' => $photo->original_name,
                'url' => route('service-photos.file', $photo),
            ])
            ->values();

        return Inertia::render('service_orders/show', [
            'order' => $serviceOrder,
            'statuses' => $this->statusOptions(),
            'audioUrl' => route('service-orders.audio', $serviceOrder),
            'audioRecords' => $audioRecords,
            'photoUploadUrl' => route('service-orders.photos', $serviceOrder),
            'photos' => $photos,
            'report' => $report ? [
                'id' => $report->id,
                'status' => $report->status->value,
                'arrival_time' => $report->arrival_time?->format('H:i'),
                'equipment_type' => $report->equipment_type,
                'problem' => $report->problem,
                'diagnosis' => $report->diagnosis,
                'work_done' => $report->work_done,
                'tests_performed' => $report->tests_performed,
                'result' => $report->result,
                'total_cost' => $report->total_cost,
            ] : null,
            'reportOptions' => [
                'types' => collect(EquipmentType::cases())
                    ->map(fn (EquipmentType $type) => [
                        'value' => $type->value,
                        'label' => $type->label(),
                    ])
                    ->all(),
                'updateUrl' => $report ? route('service-reports.update', $report) : null,
                'finalizeUrl' => $report ? route('service-reports.finalize', $report) : null,
                'pdfUrl' => $report ? route('service-reports.pdf', $report) : null,
                'shareUrl' => $report ? route('service-reports.share', $report) : null,
            ],
            'audit' => $this->auditPayload($report),
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
     * Audit trail and AI processing timing for the report.
     */
    private function auditPayload(?ServiceReport $report): ?array
    {
        if ($report === null) {
            return null;
        }

        $events = ReportEvent::query()
            ->where('service_report_id', $report->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ReportEvent $event) => [
                'id' => $event->id,
                'action' => $event->action,
                'user_name' => $event->user?->name,
                'new_status' => $event->new_status,
                'created_at' => $event->created_at->format('d/m/Y H:i'),
            ]);

        $ai = $report->audioRecord;

        return [
            'events' => $events,
            'ai' => $ai ? [
                'transcription_ms' => $ai->transcription_ms,
                'analysis_ms' => $ai->analysis_ms,
                'transcription_provider' => $ai->transcription_provider,
                'analysis_provider' => $ai->analysis_provider,
            ] : null,
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
