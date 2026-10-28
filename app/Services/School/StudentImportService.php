<?php

namespace App\Services\School;

use App\Models\Grade;
use App\Models\Organization;
use App\Models\School\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Loads a school's student register from CSV with the columns
 * student_no, name, sex, grade, section. Nothing is saved unless every row is valid;
 * existing students (same number, school and year) are updated.
 */
class StudentImportService
{
    public const COLUMNS = ['student_no', 'name', 'sex', 'grade', 'section'];

    public function __construct(private SchoolBookService $books)
    {
    }

    /**
     * @return array{created: int, updated: int, errors: array<int, string>} errors keyed by CSV line number
     */
    public function import(User $actor, Organization $school, int $year, string $path): array
    {
        $this->books->authorize($actor, $school);

        $handle = fopen($path, 'r');
        $header = array_map(fn ($column) => Str::snake(trim((string) $column)), fgetcsv($handle) ?: []);
        $header[0] = ltrim($header[0] ?? '', "\u{FEFF}");

        $missing = array_diff(self::COLUMNS, $header);
        if ($missing) {
            fclose($handle);

            return ['created' => 0, 'updated' => 0, 'errors' => [1 => 'Missing columns: ' . implode(', ', $missing) . '.']];
        }

        $grades = Grade::query()->pluck('id', 'name');
        $rows = [];
        $errors = [];
        $seen = [];
        $line = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $row = array_combine($header, array_pad(array_slice($values, 0, count($header)), count($header), ''));
            $number = trim($row['student_no']);
            $sex = strtoupper(substr(trim($row['sex']), 0, 1));
            $grade = trim(Str::after(Str::lower(trim($row['grade'])), 'grade'));

            $problem = match (true) {
                $number === '' => 'student number is empty',
                isset($seen[$number]) => "student number {$number} repeats line {$seen[$number]}",
                trim($row['name']) === '' => 'name is empty',
                ! in_array($sex, ['M', 'F'], true) => 'sex must be M or F',
                ! isset($grades[$grade]) => "unknown grade \"{$row['grade']}\"",
                default => null,
            };

            if ($problem) {
                $errors[$line] = ucfirst($problem) . '.';
                continue;
            }

            $seen[$number] = $line;
            $rows[] = [
                'student_no' => $number,
                'name' => trim($row['name']),
                'sex' => $sex,
                'grade_id' => $grades[$grade],
                'section' => strtoupper(trim($row['section'])) ?: null,
            ];
        }
        fclose($handle);

        if ($errors || empty($rows)) {
            return ['created' => 0, 'updated' => 0, 'errors' => $errors ?: [1 => 'The file has no students.']];
        }

        return DB::transaction(function () use ($school, $year, $rows): array {
            $existing = Student::query()
                ->where('organization_id', $school->id)
                ->where('academic_year', $year)
                ->whereIn('student_no', array_column($rows, 'student_no'))
                ->pluck('student_no')
                ->flip();

            Student::query()->upsert(
                array_map(fn ($row) => $row + [
                    'organization_id' => $school->id,
                    'academic_year' => $year,
                    'status' => Student::STATUS_ACTIVE,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $rows),
                ['organization_id', 'academic_year', 'student_no'],
                ['name', 'sex', 'grade_id', 'section', 'updated_at']
            );

            return ['created' => count($rows) - $existing->count(), 'updated' => $existing->count(), 'errors' => []];
        });
    }
}
