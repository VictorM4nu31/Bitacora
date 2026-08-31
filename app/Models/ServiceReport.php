<?php

namespace App\Models;

use App\Concerns\BelongsToCompany;
use App\Enums\ReportStatus;
use Database\Factories\ServiceReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $service_order_id
 * @property int $company_id
 * @property int $customer_id
 * @property int|null $equipment_id
 * @property int $technician_id
 * @property int|null $audio_record_id
 * @property ReportStatus $status
 */
#[Fillable(['service_order_id', 'company_id', 'customer_id', 'equipment_id', 'technician_id', 'audio_record_id', 'arrival_time', 'equipment_type', 'problem', 'diagnosis', 'work_done', 'tests_performed', 'result', 'total_cost', 'currency', 'status', 'llm_raw_json', 'llm_confidence'])]
class ServiceReport extends Model
{
    /** @use HasFactory<ServiceReportFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * The service order the report belongs to.
     *
     * @return BelongsTo<ServiceOrder, $this>
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * The customer the report was generated for.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The equipment the report refers to.
     *
     * @return BelongsTo<Equipment, $this>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * The technician that authored the report.
     *
     * @return BelongsTo<User, $this>
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
            'arrival_time' => 'datetime:H:i',
            'total_cost' => 'decimal:2',
            'llm_confidence' => 'decimal:2',
            'llm_raw_json' => 'array',
        ];
    }
}
