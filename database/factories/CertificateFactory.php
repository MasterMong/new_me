<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
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
            'course_id' => Course::factory(),
            'certificate_number' => 'CERT-'.$this->faker->unique()->bothify('??####'),
            'full_name_on_cert' => $this->faker->name,
            'final_score_pct' => $this->faker->numberBetween(80, 100),
            'issued_date' => now(),
            'pdf_url' => $this->faker->url,
        ];
    }
}
