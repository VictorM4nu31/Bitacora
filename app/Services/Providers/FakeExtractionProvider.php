<?php

namespace App\Services\Providers;

use App\Support\Dto\ExtractedReport;

/**
 * Deterministic extraction provider for development, tests and visual
 * validation (no external model required).
 */
class FakeExtractionProvider implements ExtractionProvider
{
    /**
     * Return a canned structured report based on the fake transcript.
     */
    public function extract(string $transcript, ?string $locale = null): ExtractedReport
    {
        return ExtractedReport::fromArray([
            'arrival_time' => '10:20',
            'customer_ref' => 'Oficina 204',
            'equipment' => 'aire acondicionado',
            'equipment_type' => 'air_conditioning',
            'problem' => 'no enfriaba',
            'diagnosis' => 'capacitor dañado',
            'work_done' => 'cambio de capacitor de 35 μF',
            'tests_performed' => '10 minutos de pruebas',
            'result' => 'funcionando correctamente',
            'total_cost' => 850,
            'currency' => 'MXN',
            'confidence' => 0.87,
            'missing' => ['cliente', 'equipo'],
        ]);
    }
}
