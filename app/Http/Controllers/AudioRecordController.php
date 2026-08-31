<?php

namespace App\Http\Controllers;

use App\Enums\AudioRecordStatus;
use App\Http\Requests\StoreAudioRequest;
use App\Models\AudioRecord;
use App\Models\ServiceOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class AudioRecordController extends Controller
{
    /**
     * Store an uploaded voice note for a service order.
     */
    public function store(StoreAudioRequest $request, ServiceOrder $serviceOrder): JsonResponse
    {
        Gate::authorize('view', $serviceOrder);

        $file = $request->file('audio');
        $extension = $file->clientExtension();

        $path = $file->storeAs(
            sprintf('voice/%s/%s/%s', $serviceOrder->company_id, now()->format('Y/m'), Str::uuid()),
            sprintf('%s.%s', Str::uuid(), $extension),
            'local',
        );

        $audio = AudioRecord::create([
            'service_order_id' => $serviceOrder->id,
            'recorder_id' => $request->user()->id,
            'disk' => 'local',
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'duration_ms' => $request->integer('duration_ms') ?: null,
            'locale' => $request->user()->locale ?? 'es',
            'status' => AudioRecordStatus::Uploaded,
        ]);

        return response()->json([
            'id' => $audio->id,
            'status' => $audio->status->value,
            'statusUrl' => route('audio-records.status', $audio),
        ]);
    }

    /**
     * Return the current processing status of a voice note.
     */
    public function status(AudioRecord $audioRecord): JsonResponse
    {
        $serviceOrder = $audioRecord->serviceOrder;

        Gate::authorize('view', $serviceOrder);

        return response()->json([
            'id' => $audioRecord->id,
            'status' => $audioRecord->status->value,
            'statusLabel' => $audioRecord->status->label(),
            'transcript' => $audioRecord->transcript_text,
        ]);
    }
}
