<?php

namespace App\Services\Providers;

use App\Support\Dto\ExtractedReport;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * LLM extraction provider compatible with the OpenAI chat completions API shape.
 *
 * Works with OpenAI, DeepSeek and any self-hosted OpenAI-compatible server
 * (e.g. Ollama, vLLM) by only changing base_url / key / model.
 */
class OpenAiCompatibleExtractionProvider implements ExtractionProvider
{
    /**
     * @param  array{base_url: string, model: string, key: ?string, timeout: int}  $config
     */
    public function __construct(private array $config) {}

    /**
     * Ask the model for a JSON-only structured extraction.
     */
    public function extract(string $transcript, ?string $locale = null): ExtractedReport
    {
        $prompt = $this->buildPrompt($transcript, $locale);

        $response = Http::timeout($this->config['timeout'])
            ->withToken($this->config['key'] ?? '')
            ->post(rtrim($this->config['base_url'], '/').'/chat/completions', [
                'model' => $this->config['model'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You extract structured data from service reports. Only respond with a valid JSON object.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('LLM extraction failed: '.$response->body());
        }

        $content = $response->json('choices.0.message.content') ?? '';

        return ExtractedReport::fromArray($this->decodeJson($content));
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $content): array
    {
        $content = trim($content);

        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $content = $matches[0];
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('LLM returned invalid JSON.');
        }

        return $decoded;
    }

    private function buildPrompt(string $transcript, ?string $locale): string
    {
        $lang = $locale ?? 'es';

        return <<<PROMPT
        Transcript (in {$lang}):
        \"\"\"
        {$transcript}
        \"\"\"

        Return only this JSON structure and nothing else:
        {
          "arrival_time": "HH:MM or null",
          "customer_ref": "string or null",
          "equipment": "string or null",
          "equipment_type": "air_conditioning | refrigeration | electrical | other | null",
          "problem": "string or null",
          "diagnosis": "string or null",
          "work_done": "string or null",
          "tests_performed": "string or null",
          "result": "string or null",
          "total_cost": "number or null",
          "currency": "MXN",
          "confidence": "0.0-1.0",
          "missing": ["field names not present in the transcript"]
        }

        If a value is not in the transcript, set it to null and add its key to "missing".
        Never invent information not mentioned in the transcript.
        PROMPT;
    }
}
