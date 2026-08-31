<?php

namespace App\Models;

use Database\Factories\ServicePhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $service_order_id
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string|null $mime
 * @property int $size
 * @property string|null $caption
 * @property int $sort_order
 */
#[Fillable(['service_order_id', 'disk', 'path', 'original_name', 'mime', 'size', 'caption', 'sort_order'])]
class ServicePhoto extends Model
{
    /** @use HasFactory<ServicePhotoFactory> */
    use HasFactory;

    /**
     * The service order the photo belongs to.
     *
     * @return BelongsTo<ServiceOrder, $this>
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }
}
