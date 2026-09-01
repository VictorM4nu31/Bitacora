<?php

use App\Services\Providers\OpenAiCompatibleExtractionProvider;
use App\Support\Dto\ExtractedReport;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

function openAiProvider(array $config = []): OpenAiCompatibleExtractionProvider
{
    return new OpenAiCompatibleExtractionProvider(array_merge([
        'base_url' => 'https://api.test/v1',
        'model' => 'gpt-test',
        'key' => 'secret',
        'timeout' => 30,
    ], $config));
}

test('the provider parses a valid JSON response wrapped in markdown fences', function () {
    Http::fake([
        '*' => Http::response([
            'choices' => [
                ['message' => ['content' => <<<'JSON'
                    ```json
                    {"work_done": "cambio de capacitor", "total_cost": 850, "missing": []}
                    ```
                    JSON],
                ],
            ],
        ], 200),
    ]);

    $report = openAiProvider()->extract('No enfriaba');

    expect($report)->toBeInstanceOf(ExtractedReport::class)
        ->and($report->workDone)->toBe('cambio de capacitor')
        ->and((float) $report->totalCost)->toBe(850.0);
});

test('the provider extracts the JSON object from surrounding text', function () {
    Http::fake([
        '*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Aquí tienes: {"problem": "ruido"} Gracias.']],
            ],
        ], 200),
    ]);

    $report = openAiProvider()->extract('texto');

    expect($report->problem)->toBe('ruido');
});

test('the provider throws a RuntimeException on invalid JSON', function () {
    Http::fake([
        '*' => Http::response([
            'choices' => [['message' => ['content' => 'esto no es json']]],
        ], 200),
    ]);

    expect(fn () => openAiProvider()->extract('texto'))
        ->toThrow(RuntimeException::class, 'invalid JSON');
});

test('the provider throws a generic message on a failed response', function () {
    Http::fake([
        '*' => Http::response('boom', 500),
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('status 500');

    openAiProvider()->extract('texto');
});

test('the provider sends the transcript as delimited data in the prompt', function () {
    Http::fake([
        '*' => Http::response([
            'choices' => [['message' => ['content' => '{"work_done": "x"}']]],
        ], 200),
    ]);

    openAiProvider()->extract('cambio de capacitor');

    Http::assertSent(function (Request $request) {
        $payload = $request->data();
        $prompt = $payload['messages'][1]['content'];

        return str_contains($prompt, '<<<TRANSCRIPT')
            && str_contains($prompt, '<</TRANSCRIPT')
            && str_contains($prompt, 'cambio de capacitor');
    });
});
