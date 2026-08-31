<?php

namespace App\Services\Providers;

use App\Support\Dto\ExtractedReport;

/**
 * Contract for LLM providers that extract structured data from a transcript.
 *
 * Swapping OpenAI, Groq, a local model or DeepSeek only changes the registered
 * implementation; the domain consumes the returned DTO.
 */
interface ExtractionProvider
{
    /**
     * Extract structured information from a transcript.
     */
    public function extract(string $transcript, ?string $locale = null): ExtractedReport;
}
