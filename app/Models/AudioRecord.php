<?php

namespace App\Models;

use App\Enums\AudioRecordStatus;
use Database\Factories\AudioRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $service_order_id
 * @property int $recorder_id
 * @property string $disk
 * @property string $path
 * @property string|null $mime
 * @property int $size
 * @property int|null $duration_ms
 * @property AudioRecordStatus $status
 */
#[Fillable(['service_order_id', 'recorder_id', 'disk', 'path', 'mime', 'size', 'duration_ms', 'locale', 'status', 'transcript_text', 'transcription_provider', 'transcription_ms', 'transcription_error'])]
class AudioRecord extends Model
{
    /** @use HasFactory<AudioRecordFactory> */
    use HasFactory;

    /**
     * The service order the voice note belongs to.
     *
     * @return BelongsTo<ServiceOrder, $this>
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * The technician that recorded the voice note.
     *
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorder_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AudioRecordStatus::class,
            'duration_ms' => 'integer',
            'size' => 'integer',
        ];
    }
}
