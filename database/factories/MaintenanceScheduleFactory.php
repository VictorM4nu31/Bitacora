<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceSchedule>
 */
class MaintenanceScheduleFactory extends Factory
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
            'equipment_id' => Equipment::factory(),
            'customer_id' => Customer::factory(),
            'interval_days' => fake()->numberBetween(30, 180),
            'last_run_at' => null,
            'next_due_at' => now()->addDays(30),
            'enabled' => true,
        ];
    }

    /**
     * Indicate that the schedule belongs to the given company.
     */
    public function forCompany(Company|int $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company instanceof Company ? $company->getKey() : $company,
        ]);
    }

    /**
     * Indicate that the schedule belongs to the given equipment.
     */
    public function forEquipment(Equipment|int $equipment): static
    {
        return $this->state(fn (array $attributes) => [
            'equipment_id' => $equipment instanceof Equipment ? $equipment->getKey() : $equipment,
        ]);
    }

    /**
     * Indicate that the schedule is due now.
     */
    public function due(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_due_at' => now()->subHour(),
            'enabled' => true,
        ]);
    }
}
