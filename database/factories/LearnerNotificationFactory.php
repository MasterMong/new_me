<?php

namespace Database\Factories;

use App\Enums\NotificationType;
use App\Models\LearnerNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearnerNotification>
 */
class LearnerNotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(NotificationType::cases()),
            'title' => $this->faker->sentence,
            'message' => $this->faker->paragraph,
            'is_read' => false,
            'sent_email' => false,
        ];
    }
}
