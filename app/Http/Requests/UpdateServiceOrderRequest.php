<?php

namespace App\Http\Requests;

use App\Enums\ServiceOrderStatus;
use App\Models\ServiceOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $routeOrder = $this->route('service_order');
        $id = $routeOrder instanceof ServiceOrder ? $routeOrder->getKey() : $routeOrder;
        $order = ServiceOrder::query()->whereKey($id)->first();

        return $order !== null && $this->user()?->company_id === $order->company_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;
        $order = $this->route('service_order');
        $customerId = $this->integer('customer_id') ?: ($order instanceof ServiceOrder ? $order->customer_id : null);

        return [
            'status' => ['sometimes', 'string', Rule::enum(ServiceOrderStatus::class)],
            'scheduled_at' => ['nullable', 'date'],
            'customer_id' => ['sometimes', 'integer', Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'equipment_id' => ['sometimes', 'nullable', 'integer', Rule::exists('equipment', 'id')
                ->where('company_id', $companyId)
                ->where('customer_id', $customerId)],
        ];
    }
}
