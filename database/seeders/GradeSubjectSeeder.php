<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subjects = Subject::query()->pluck('id');
        $grades = Grade::query()->pluck('id');
        $rows = [];

        foreach ($grades as $gradeId) {
            foreach ($subjects as $subjectId) {
                $rows[] = [
                    'grade_id' => $gradeId,
                    'subject_id' => $subjectId,
                ];
            }
        }

        DB::table('grade_subjects')->delete();
        if (! empty($rows)) {
            DB::table('grade_subjects')->insert($rows);
        }
    }
}
