<?php

namespace Database\Factories;

use App\Enums\AudioRecordStatus;
use App\Models\AudioRecord;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AudioRecord>
 */
class AudioRecordFactory extends Factory
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
            'recorder_id' => User::factory(),
            'disk' => 'local',
            'path' => 'voice/'.fake()->uuid().'.webm',
            'mime' => 'audio/webm',
            'size' => fake()->numberBetween(1000, 500000),
            'duration_ms' => fake()->numberBetween(5000, 60000),
            'locale' => 'es',
            'status' => AudioRecordStatus::Uploaded,
        ];
    }

    /**
     * Indicate that the audio record belongs to the given service order.
     */
    public function forServiceOrder(ServiceOrder|int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'service_order_id' => $order instanceof ServiceOrder ? $order->getKey() : $order,
        ]);
    }

    /**
     * Indicate the audio record status.
     */
    public function status(AudioRecordStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
