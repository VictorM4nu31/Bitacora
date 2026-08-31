<?php

namespace App\Models;

use App\Concerns\BelongsToCompany;
use App\Enums\EquipmentType;
use Database\Factories\EquipmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $company_id
 * @property int $customer_id
 * @property string $name
 * @property string|null $brand
 * @property string|null $model
 * @property string|null $serial_number
 * @property EquipmentType $type
 * @property string|null $notes
 */
#[Fillable(['company_id', 'customer_id', 'name', 'brand', 'model', 'serial_number', 'type', 'notes'])]
class Equipment extends Model
{
    /** @use HasFactory<EquipmentFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

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
            'type' => EquipmentType::class,
        ];
    }
}
