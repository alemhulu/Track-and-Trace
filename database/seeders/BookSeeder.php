<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            ['grade' => '9', 'subject' => 'Biology', 'isbn' => '978000100901', 'edition' => 1, 'volume' => 1],
            ['grade' => '9', 'subject' => 'Mathematics', 'isbn' => '978000100902', 'edition' => 1, 'volume' => 1],
            ['grade' => '10', 'subject' => 'Biology', 'isbn' => '978000101001', 'edition' => 2, 'volume' => 1],
            ['grade' => '10', 'subject' => 'Chemistry', 'isbn' => '978000101002', 'edition' => 2, 'volume' => 1],
            ['grade' => '10', 'subject' => 'Physics', 'isbn' => '978000101003', 'edition' => 2, 'volume' => 1],
            ['grade' => '11', 'subject' => 'Mathematics', 'isbn' => '978000101101', 'edition' => 1, 'volume' => 2],
            ['grade' => '11', 'subject' => 'English', 'isbn' => '978000101102', 'edition' => 1, 'volume' => 2],
            ['grade' => '12', 'subject' => 'ICT', 'isbn' => '978000101201', 'edition' => 1, 'volume' => 2],
        ];

        foreach ($books as $book) {
            $grade = Grade::query()->where('name', $book['grade'])->first();
            $subject = Subject::query()->where('name', $book['subject'])->first();

            if (! $grade || ! $subject) {
                continue;
            }

            Book::updateOrCreate(
                ['isbn' => $book['isbn']],
                [
                    'grade_id' => $grade->id,
                    'subject_id' => $subject->id,
                    'volume' => $book['volume'],
                    'edition' => $book['edition'],
                    'book_type' => false,
                    'print_type' => 'Offset',
                    'paper_size' => 'A4',
                    'file_location' => '/books/' . $book['isbn'] . '.pdf',
                    'front_cover_location' => '/book-covers/' . $book['isbn'] . '-front.jpg',
                    'back_cover_location' => '/book-covers/' . $book['isbn'] . '-back.jpg',
                ]
            );
        }
    }
}
