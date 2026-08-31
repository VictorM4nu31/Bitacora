<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization is enforced in the controller via the report policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'arrival_time' => ['nullable', 'date_format:H:i'],
            'equipment_type' => ['nullable', 'string', 'max:100'],
            'problem' => ['nullable', 'string', 'max:5000'],
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'work_done' => ['nullable', 'string', 'max:5000'],
            'tests_performed' => ['nullable', 'string', 'max:5000'],
            'result' => ['nullable', 'string', 'max:5000'],
            'total_cost' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['nullable', 'string', 'max:3'],
        ];
    }

    /**
     * Data used by the report service.
     *
     * @return array<string, mixed>
     */
    public function validatedData(): array
    {
        return $this->validated();
    }
}
