<?php

namespace App\Models;

use App\Concerns\BelongsToCompany;
use Database\Factories\MaintenanceScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $equipment_id
 * @property int $customer_id
 * @property int $interval_days
 * @property bool $enabled
 */
#[Fillable(['company_id', 'equipment_id', 'customer_id', 'interval_days', 'last_run_at', 'last_notified_at', 'next_due_at', 'enabled'])]
class MaintenanceSchedule extends Model
{
    /** @use HasFactory<MaintenanceScheduleFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * The equipment to maintain.
     *
     * @return BelongsTo<Equipment, $this>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * The customer that owns the equipment.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_run_at' => 'datetime',
            'last_notified_at' => 'datetime',
            'next_due_at' => 'datetime',
            'enabled' => 'boolean',
            'interval_days' => 'integer',
        ];
    }

    /**
     * Move the schedule to its next due date after a completed maintenance.
     */
    public function advance(): void
    {
        $this->update([
            'last_run_at' => now(),
            'next_due_at' => now()->addDays($this->interval_days),
        ]);
    }
}
