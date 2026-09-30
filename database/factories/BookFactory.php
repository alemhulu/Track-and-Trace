<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition()
    {
        $gradeId = Grade::query()->inRandomOrder()->value('id')
            ?? Grade::query()->firstOrCreate(['name' => (string) $this->faker->numberBetween(1, 12)])->id;

        $subjectId = Subject::query()->inRandomOrder()->value('id')
            ?? Subject::query()->firstOrCreate(['name' => 'Factory Subject'])->id;

        return [
            'grade_id' => $gradeId,
            'subject_id' => $subjectId,
            'isbn' => $this->faker->unique()->numerify('978##########'),
            'volume' => $this->faker->numberBetween(1, 3),
            'edition' => $this->faker->numberBetween(1, 5),
            'book_type' => $this->faker->boolean(),
            'print_type' => 'Offset',
            'paper_size' => 'A4',
            'file_location' => '/books/' . $this->faker->numerify('####') . '.pdf',
            'front_cover_location' => '/book-covers/' . $this->faker->numerify('####') . '-front.jpg',
            'back_cover_location' => '/book-covers/' . $this->faker->numerify('####') . '-back.jpg',
        ];
    }
}
