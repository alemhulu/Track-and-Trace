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
            ['name'=>'Amharic'],
            ['name'=>'English'],
            ['name'=>'Mathematics'],
            ['name'=>'Physics'],
            ['name'=>'Chemistry'],
            ['name'=>'Biology'],
            ['name'=>'History'],
            ['name'=>'Geography'],
            ['name'=>'Civics'],
            ['name'=>'PHE'],
            ['name'=>'Environmental Sience'],
            ['name'=>'Technical Drawing'],
            ['name'=>'Social Studies'],
            ['name'=>'Sign Language'],
            ['name'=>'ICT'],
            ['name'=>'General Business'],
        ];
        foreach($datas as $data){
            Subject::create($data);
        }
    }
}
