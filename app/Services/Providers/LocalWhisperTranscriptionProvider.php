<?php

namespace App\Services\Providers;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Transcribes audio using a local Whisper-compatible binary
 * (whisper.cpp / faster-whisper) configured via config.
 */
class LocalWhisperTranscriptionProvider implements TranscriptionProvider
{
    /**
     * @param  array{binary: string, model: string, language: string, timeout: int}  $config
     */
    public function __construct(private array $config) {}

    /**
     * Run the Whisper binary against the given audio file.
     */
    public function transcribe(string $path, ?string $language = null): string
    {
        $binary = $this->config['binary'];
        $model = $this->config['model'];
        $lang = $language ?? $this->config['language'];
        $timeout = $this->config['timeout'];

        $command = [$binary, '--model', $model, '--language', $lang, '--output-format', 'txt', $path];

        $process = new Process($command, null, null, null, $timeout);

        $process->run();

        if (! $process->isSuccessful()) {
            $error = trim((string) $process->getErrorOutput()) ?: 'unknown error';

            throw new RuntimeException('Local Whisper failed: '.$error);
        }

        return trim($process->getOutput());
    }
}
