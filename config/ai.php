<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Speech-to-Text
    |--------------------------------------------------------------------------
    |
    | The transcription driver is resolved by a service provider. Use "fake"
    | for development/tests (returns a canned transcript) and "local" to call a
    | local Whisper binary (whisper.cpp / faster-whisper).
    |
    */

    'transcription' => [
        'driver' => env('STT_DRIVER', 'fake'),

        'local' => [
            'binary' => env('STT_LOCAL_BINARY', 'whisper'),
            'model' => env('STT_LOCAL_MODEL', 'base'),
            'language' => env('STT_LOCAL_LANGUAGE', 'es'),
            'timeout' => env('STT_LOCAL_TIMEOUT', 180),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | LLM Extraction
    |--------------------------------------------------------------------------
    |
    | Extracts structured report data from a transcript. Use "fake" for
    | development/tests and "openai" for OpenAI / DeepSeek / any OpenAI-
    | compatible endpoint.
    |
    */

    'extraction' => [
        'driver' => env('LLM_DRIVER', 'fake'),

        'openai' => [
            'base_url' => env('LLM_BASE_URL', 'https://api.openai.com/v1'),
            'model' => env('LLM_MODEL', 'gpt-4o-mini'),
            'key' => env('LLM_API_KEY'),
            'timeout' => env('LLM_TIMEOUT', 60),
        ],
    ],

];
