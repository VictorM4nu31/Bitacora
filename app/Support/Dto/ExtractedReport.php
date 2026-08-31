<?php

namespace App\Support\Dto;

/**
 * Structured data extracted from a transcript by an LLM.
 *
 * This is the stable contract between the AI layer and the domain. Providers
 * must return an instance of this class so the rest of the application never
 * depends on raw vendor JSON.
 */
class ExtractedReport
{
    public ?string $arrivalTime = null;

    public ?string $customerRef = null;

    public ?string $equipment = null;

    public ?string $equipmentType = null;

    public ?string $problem = null;

    public ?string $diagnosis = null;

    public ?string $workDone = null;

    public ?string $testsPerformed = null;

    public ?string $result = null;

    public ?float $totalCost = null;

    public ?string $currency = 'MXN';

    public ?float $confidence = null;

    /**
     * @var array<int, string>
     */
    public array $missing = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $dto = new self;

        $dto->arrivalTime = $data['arrival_time'] ?? null;
        $dto->customerRef = $data['customer_ref'] ?? null;
        $dto->equipment = $data['equipment'] ?? null;
        $dto->equipmentType = $data['equipment_type'] ?? null;
        $dto->problem = $data['problem'] ?? null;
        $dto->diagnosis = $data['diagnosis'] ?? null;
        $dto->workDone = $data['work_done'] ?? null;
        $dto->testsPerformed = $data['tests_performed'] ?? null;
        $dto->result = $data['result'] ?? null;
        $dto->totalCost = isset($data['total_cost']) ? (float) $data['total_cost'] : null;
        $dto->currency = $data['currency'] ?? 'MXN';
        $dto->confidence = isset($data['confidence']) ? (float) $data['confidence'] : null;
        $dto->missing = is_array($data['missing'] ?? null)
            ? array_values(array_filter(array_map('strval', $data['missing'])))
            : [];

        return $dto;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'arrival_time' => $this->arrivalTime,
            'customer_ref' => $this->customerRef,
            'equipment' => $this->equipment,
            'equipment_type' => $this->equipmentType,
            'problem' => $this->problem,
            'diagnosis' => $this->diagnosis,
            'work_done' => $this->workDone,
            'tests_performed' => $this->testsPerformed,
            'result' => $this->result,
            'total_cost' => $this->totalCost,
            'currency' => $this->currency,
            'confidence' => $this->confidence,
            'missing' => $this->missing,
        ];
    }

    /**
     * Whether the report contains the minimum useful information.
     */
    public function isEmpty(): bool
    {
        return $this->problem === null && $this->workDone === null && $this->diagnosis === null;
    }
}
