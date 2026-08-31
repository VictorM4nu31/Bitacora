<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ReportEvent;
use App\Models\ServiceOrder;
use App\Models\ServiceReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportEvent>
 */
class ReportEventFactory extends Factory
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
            'service_order_id' => ServiceOrder::factory(),
            'service_report_id' => null,
            'user_id' => null,
            'action' => 'updated',
            'old_status' => null,
            'new_status' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate the action.
     */
    public function action(string $action): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => $action,
        ]);
    }

    /**
     * Indicate the user that performed the action.
     */
    public function by(User|int $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user instanceof User ? $user->getKey() : $user,
        ]);
    }

    /**
     * Indicate the report the event is tied to.
     */
    public function forReport(ServiceReport $report): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $report->company_id,
            'service_order_id' => $report->service_order_id,
            'service_report_id' => $report->id,
        ]);
    }
}
