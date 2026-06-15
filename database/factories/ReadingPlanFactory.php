<?php

namespace Database\Factories;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReadingPlan>
 */
class ReadingPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'book_id'      => Book::factory(),
            'due_date'     => now()->addDays(7),
            'status'       => ReadingPlanStatus::Reading,
            'completed_at' => null,
        ];
    }

    public function reading(): static
    {
        return $this->state(['status' => ReadingPlanStatus::Reading, 'completed_at' => null]);
    }

    public function completed(): static
    {
        return $this->state(['status' => ReadingPlanStatus::Completed, 'completed_at' => now()]);
    }

    public function expired(): static
    {
        return $this->state([
            'status'   => ReadingPlanStatus::Expired,
            'due_date' => now()->subDays(3),
        ]);
    }

    public function overdue(): static
    {
        return $this->state([
            'status'   => ReadingPlanStatus::Reading,
            'due_date' => now()->subDays(1),
        ]);
    }
}
