<?php

namespace App\Services\Providers;

/**
 * Contract for speech-to-text providers.
 *
 * The domain never depends on a concrete vendor; swapping OpenAI, Groq,
 * whisper.cpp or a local service only changes the registered implementation.
 */
interface TranscriptionProvider
{
    /**
     * Transcribe the audio file located at the given filesystem path.
     */
    public function transcribe(string $path, ?string $language = null): string;
}
