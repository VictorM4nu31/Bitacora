<?php

namespace App\Models;

use App\Concerns\BelongsToCompany;
use App\Enums\ServiceOrderStatus;
use Database\Factories\ServiceOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $company_id
 * @property int $customer_id
 * @property int|null $equipment_id
 * @property int $technician_id
 * @property ServiceOrderStatus $status
 */
#[Fillable(['company_id', 'customer_id', 'equipment_id', 'technician_id', 'status', 'scheduled_at', 'started_at', 'completed_at'])]
class ServiceOrder extends Model
{
    /** @use HasFactory<ServiceOrderFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * The customer the service was performed for.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The equipment being serviced.
     *
     * @return BelongsTo<Equipment, $this>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * The technician that performed the service.
     *
     * @return BelongsTo<User, $this>
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Voice notes recorded for the service order.
     *
     * @return HasMany<AudioRecord, $this>
     */
    public function audioRecords(): HasMany
    {
        return $this->hasMany(AudioRecord::class);
    }

    /**
     * The report generated for the service order.
     *
     * @return HasOne<ServiceReport, $this>
     */
    public function report(): HasOne
    {
        return $this->hasOne(ServiceReport::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ServiceOrderStatus::class,
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
