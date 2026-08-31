<?php

namespace App\Jobs;

use App\Enums\AudioRecordStatus;
use App\Models\AudioRecord;
use App\Services\Providers\TranscriptionProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TranscribeAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $tries = 3;

    /**
     * @var array<int, int>
     */
    public $backoff = [10, 30, 60];

    /**
     * @var int
     */
    public $timeout = 180;

    public function __construct(public AudioRecord $audio) {}

    /**
     * Transcribe the voice note and store the result.
     */
    public function handle(TranscriptionProvider $provider): void
    {
        $this->audio->update(['status' => AudioRecordStatus::Processing]);

        $path = Storage::disk($this->audio->disk)->path($this->audio->path);
        $startedAt = microtime(true);

        try {
            $transcript = $provider->transcribe($path, $this->audio->locale);
        } catch (Throwable $e) {
            $this->audio->update([
                'status' => AudioRecordStatus::Failed,
                'transcription_error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $this->audio->update([
            'status' => AudioRecordStatus::Transcribed,
            'transcript_text' => $transcript,
            'transcription_provider' => config('ai.transcription.driver'),
            'transcription_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);
    }

    /**
     * Mark the voice note as failed once attempts are exhausted.
     */
    public function failed(Throwable $e): void
    {
        $this->audio->update([
            'status' => AudioRecordStatus::Failed,
            'transcription_error' => $e->getMessage(),
        ]);
    }
}
