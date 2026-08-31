<?php

namespace Database\Factories;

use App\Enums\EquipmentType;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
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
            'name' => fake()->word(),
            'brand' => fake()->company(),
            'model' => fake()->bothify('M-##'),
            'serial_number' => fake()->unique()->bothify('SN-########'),
            'type' => EquipmentType::Other,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the equipment belongs to the given company.
     */
    public function forCompany(Company|int $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company instanceof Company ? $company->getKey() : $company,
        ]);
    }

    /**
     * Indicate that the equipment belongs to the given customer.
     */
    public function forCustomer(Customer|int $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer instanceof Customer ? $customer->getKey() : $customer,
        ]);
    }

    /**
     * Indicate that the equipment is of the given type.
     */
    public function type(EquipmentType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}
