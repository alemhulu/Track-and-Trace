<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subjects=Subject::all();
        $grades=[9,10,11,12];
        $i=0;
        foreach ($subjects as $key => $subject) {
            foreach ($grades as $key => $grade) {
                $data[$i] = 
                    [
                        'grade_id'=>$grade,
                        'subject_id' =>$subject->id
                    ];  
                   
                    GradeSubject::insert($data[$i]);
                    $i++;
            }
            
        }
       
        
    }
}
