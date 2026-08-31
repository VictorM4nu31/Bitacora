<?php

namespace Database\Factories;

use App\Enums\ServiceOrderStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceOrder>
 */
class ServiceOrderFactory extends Factory
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
            'equipment_id' => null,
            'technician_id' => User::factory(),
            'status' => ServiceOrderStatus::Pending,
            'scheduled_at' => now()->addDay(),
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the service order belongs to the given company.
     */
    public function forCompany(Company|int $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company instanceof Company ? $company->getKey() : $company,
        ]);
    }

    /**
     * Indicate that the service order is for the given customer.
     */
    public function forCustomer(Customer|int $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer instanceof Customer ? $customer->getKey() : $customer,
        ]);
    }

    /**
     * Indicate that the service order is handled by the given technician.
     */
    public function forTechnician(User|int $user): static
    {
        return $this->state(fn (array $attributes) => [
            'technician_id' => $user instanceof User ? $user->getKey() : $user,
        ]);
    }

    /**
     * Indicate the service order status.
     */
    public function status(ServiceOrderStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
