<?php

namespace App\Http\Requests;

use App\Enums\EquipmentType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::enum(EquipmentType::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
