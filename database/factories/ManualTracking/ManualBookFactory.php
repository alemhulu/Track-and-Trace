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
        $catalog = [
            ['subject' => 'Mathematics', 'series' => 'Competency-Based Student Textbook'],
            ['subject' => 'English', 'series' => 'Communicative English Student Textbook'],
            ['subject' => 'Biology', 'series' => 'Applied Science Student Textbook'],
            ['subject' => 'Chemistry', 'series' => 'Applied Science Student Textbook'],
            ['subject' => 'Physics', 'series' => 'Applied Science Student Textbook'],
            ['subject' => 'ICT', 'series' => 'Digital Literacy Student Textbook'],
            ['subject' => 'Geography', 'series' => 'Social Studies Student Textbook'],
            ['subject' => 'History', 'series' => 'Social Studies Student Textbook'],
            ['subject' => 'Civics', 'series' => 'Citizenship Education Student Textbook'],
        ];
        $selected = $this->faker->randomElement($catalog);
        $subjectName = $selected['subject'];
        $gradeNumber = $this->faker->numberBetween(5, 12);
        $gradeName = "Grade {$gradeNumber}";

        $packages = $this->faker->numberBetween(6, 50);
        $hasRemainder = $this->faker->boolean(15);
        $remainder = $hasRemainder ? $this->faker->numberBetween(1, 39) : 0;
        $totalCopies = ($packages * 40) + $remainder;

        return [
            'code' => 'MB-' . strtoupper(Str::random(3)) . '-' . $this->faker->unique()->numberBetween(100, 9999),
            'title' => "{$subjectName} {$selected['series']} ({$gradeName})",
            'grade_name' => $gradeName,
            'subject_name' => $subjectName,
            'isbn' => $this->faker->unique()->numerify('978##########'),
            'edition' => $this->faker->numberBetween(1, 5),
            'total_copies' => $totalCopies,
            'notes' => $this->faker->sentence(8),
        ];
    }
}
