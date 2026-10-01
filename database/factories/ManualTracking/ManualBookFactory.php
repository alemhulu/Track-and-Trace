<?php

namespace Database\Factories\ManualTracking;

use App\Models\ManualTracking\ManualBook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ManualBookFactory extends Factory
{
    protected $model = ManualBook::class;

    public function definition(): array
    {
        $gradeName = $this->faker->randomElement([
            'Grade 5',
            'Grade 6',
            'Grade 7',
            'Grade 8',
            'Grade 9',
            'Grade 10',
            'Grade 11',
            'Grade 12',
        ]);

        $subjectName = $this->faker->randomElement([
            'Mathematics',
            'English',
            'Biology',
            'Chemistry',
            'Physics',
            'ICT',
            'Geography',
        ]);

        return [
            'code' => 'MB-' . strtoupper(Str::random(3)) . '-' . $this->faker->unique()->numberBetween(100, 9999),
            'title' => $subjectName . ' ' . $gradeName,
            'grade_name' => $gradeName,
            'subject_name' => $subjectName,
            'isbn' => $this->faker->unique()->numerify('978##########'),
            'edition' => $this->faker->numberBetween(1, 5),
            'total_copies' => $this->faker->numberBetween(200, 2500),
            'notes' => $this->faker->sentence(8),
        ];
    }
}
