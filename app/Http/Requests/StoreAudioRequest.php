<?php

namespace App\Http\Requests;

use App\Models\ServiceOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAudioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $routeOrder = $this->route('service_order');
        $id = $routeOrder instanceof ServiceOrder ? $routeOrder->getKey() : $routeOrder;
        $order = ServiceOrder::query()->whereKey($id)->first();

        return $order !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $order->company_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'audio' => ['required', 'file', 'max:30000', 'extensions:webm,ogg,mp4,m4a,wav,mp3'],
            'duration_ms' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
