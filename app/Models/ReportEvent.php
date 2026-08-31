<?php

namespace App\Models;

use Database\Factories\ReportEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $service_order_id
 * @property int|null $service_report_id
 * @property int|null $user_id
 * @property string $action
 */
#[Fillable(['company_id', 'service_order_id', 'service_report_id', 'user_id', 'action', 'old_status', 'new_status', 'metadata'])]
class ReportEvent extends Model
{
    /** @use HasFactory<ReportEventFactory> */
    use HasFactory;

    /**
     * The user that performed the action (null for automatic AI actions).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The report the event belongs to.
     *
     * @return BelongsTo<ServiceReport, $this>
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(ServiceReport::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
