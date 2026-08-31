<?php

namespace App\Jobs;

use App\Models\AudioRecord;
use App\Services\Providers\ExtractionProvider;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AnalyzeTranscriptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $tries = 2;

    /**
     * @var array<int, int>
     */
    public $backoff = [20, 40];

    /**
     * @var int
     */
    public $timeout = 120;

    public function __construct(public AudioRecord $audio) {}

    /**
     * Extract structured data from the transcript and store it on the audio.
     */
    public function handle(ExtractionProvider $provider): void
    {
        if ($this->audio->transcript_text === null) {
            return;
        }

        $startedAt = microtime(true);

        try {
            $report = $provider->extract($this->audio->transcript_text, $this->audio->locale);
        } catch (Throwable $e) {
            $this->audio->update(['analysis_error' => $e->getMessage()]);

            throw $e;
        }

        $this->audio->update([
            'extracted_data' => $report->toArray(),
            'analysis_provider' => config('ai.extraction.driver'),
            'analysis_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);

        app(ReportService::class)->createDraftFromAudio($this->audio);
    }

    /**
     * Keep the error trace when attempts are exhausted.
     */
    public function failed(Throwable $e): void
    {
        $this->audio->update(['analysis_error' => $e->getMessage()]);
    }
}
