<?php

namespace Database\Factories;

use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicePhoto>
 */
class ServicePhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_order_id' => ServiceOrder::factory(),
            'disk' => 'local',
            'path' => 'photos/'.fake()->uuid().'.jpg',
            'original_name' => fake()->word().'.jpg',
            'mime' => 'image/jpeg',
            'size' => fake()->numberBetween(1000, 1000000),
            'caption' => null,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the photo belongs to the given service order.
     */
    public function forServiceOrder(ServiceOrder|int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'service_order_id' => $order instanceof ServiceOrder ? $order->getKey() : $order,
        ]);
    }
}
