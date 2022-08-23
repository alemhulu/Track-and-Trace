<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
            ['name'=>'Amharic','code'=>'C-01'],
            ['name'=>'English','code'=>'C-02'],
            ['name'=>'Mathematics','code'=>'C-03'],
            ['name'=>'Physics','code'=>'C-04'],
            ['name'=>'Chemistry','code'=>'C-05'],
            ['name'=>'Biology','code'=>'C-06'],
            ['name'=>'History','code'=>'C-07'],
            ['name'=>'Geography','code'=>'C-08'],
            ['name'=>'Civics','code'=>'C-09'],
            ['name'=>'PHE','code'=>'C-10'],
            ['name'=>'Environmental Sience','code'=>'C-11'],
            ['name'=>'Technical Drawing','code'=>'C-12'],
            ['name'=>'Social Studies','code'=>'C-13'],
            ['name'=>'Sign Language','code'=>'C-14'],
            ['name'=>'ICT','code'=>'C-15'],
            ['name'=>'General Business','code'=>'C-16'],
        ];
        foreach($datas as $data){
            Subject::create($data);
        }
    }
}
