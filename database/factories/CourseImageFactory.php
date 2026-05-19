<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'image_url' => 'https://picsum.photos/seed/gallery_'.fake()->numberBetween(1, 5000).'/800/600',
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
