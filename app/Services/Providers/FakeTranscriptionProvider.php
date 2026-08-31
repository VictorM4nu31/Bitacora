<?php

namespace App\Services\Providers;

/**
 * Deterministic transcription provider used for development, tests and the
 * visual validation (no external service or binary required).
 */
class FakeTranscriptionProvider implements TranscriptionProvider
{
    /**
     * Return a canned transcript.
     */
    public function transcribe(string $path, ?string $language = null): string
    {
        return <<<'TRANSCRIPT'
        Llegue a las diez veinte a la oficina 204 porque el aire acondicionado no estaba enfriando. Revise el equipo y encontre el capacitor danado. Lo cambie por uno de 35 microfaradios, hice pruebas durante diez minutos y quedo funcionando correctamente. El cliente pago 850 pesos.
        TRANSCRIPT;
    }
}
