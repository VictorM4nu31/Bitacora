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

];
