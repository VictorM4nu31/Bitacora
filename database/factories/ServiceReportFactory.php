<?php

namespace Database\Factories;

use App\Enums\EquipmentType;
use App\Enums\ReportStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\ServiceReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceReport>
 */
class ServiceReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'customer_id' => Customer::factory(),
            'technician_id' => User::factory(),
            'equipment_id' => null,
            'audio_record_id' => null,
            'arrival_time' => '10:20',
            'equipment_type' => EquipmentType::AirConditioning->value,
            'problem' => 'no enfriaba',
            'diagnosis' => 'capacitor dañado',
            'work_done' => 'cambio de capacitor de 35 μF',
            'tests_performed' => '10 minutos de pruebas',
            'result' => 'funcionando correctamente',
            'total_cost' => 850,
            'currency' => 'MXN',
            'status' => ReportStatus::Draft,
            'llm_confidence' => 0.87,
        ];
    }

    /**
     * Indicate that the report belongs to the given service order.
     */
    public function forServiceOrder(ServiceOrder $order): static
    {
        return $this->state(fn (array $attributes) => [
            'service_order_id' => $order->id,
            'company_id' => $order->company_id,
            'customer_id' => $order->customer_id,
            'equipment_id' => $order->equipment_id,
            'technician_id' => $order->technician_id,
        ]);
    }

    /**
     * Indicate the report status.
     */
    public function status(ReportStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
