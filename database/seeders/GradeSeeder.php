<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Grade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
            ['name'=>'Grade 1','code'=>'G-01','subject_id'=>'1'],
            ['name'=>'Grade 1','code'=>'G-01','subject_id'=>'2'],
            ['name'=>'Grade 1','code'=>'G-01','subject_id'=>'3'],
            ['name'=>'Grade 1','code'=>'G-01','subject_id'=>'4'],

            ['name'=>'Grade 2','code'=>'G-02','subject_id'=>'1'],
            ['name'=>'Grade 2','code'=>'G-02','subject_id'=>'2'],
            ['name'=>'Grade 2','code'=>'G-02','subject_id'=>'3'],
            ['name'=>'Grade 2','code'=>'G-02','subject_id'=>'4'],

            ['name'=>'Grade 3','code'=>'G-03','subject_id'=>'1'],
            ['name'=>'Grade 3','code'=>'G-03','subject_id'=>'2'],
            ['name'=>'Grade 3','code'=>'G-03','subject_id'=>'3'],
            ['name'=>'Grade 3','code'=>'G-03','subject_id'=>'4'],

            ['name'=>'Grade 4','code'=>'G-04','subject_id'=>'1'],
            ['name'=>'Grade 4','code'=>'G-04','subject_id'=>'2'],
            ['name'=>'Grade 4','code'=>'G-04','subject_id'=>'3'],
            ['name'=>'Grade 4','code'=>'G-04','subject_id'=>'4'],

            ['name'=>'Grade 5','code'=>'G-05','subject_id'=>'1'],
            ['name'=>'Grade 5','code'=>'G-05','subject_id'=>'2'],
            ['name'=>'Grade 5','code'=>'G-05','subject_id'=>'3'],
            ['name'=>'Grade 5','code'=>'G-05','subject_id'=>'4'],

            ['name'=>'Grade 6','code'=>'G-06','subject_id'=>'1'],
            ['name'=>'Grade 6','code'=>'G-06','subject_id'=>'2'],
            ['name'=>'Grade 6','code'=>'G-06','subject_id'=>'3'],
            ['name'=>'Grade 6','code'=>'G-06','subject_id'=>'4'],

            ['name'=>'Grade 7','code'=>'G-07','subject_id'=>'1'],
            ['name'=>'Grade 7','code'=>'G-07','subject_id'=>'2'],
            ['name'=>'Grade 7','code'=>'G-07','subject_id'=>'3'],
            ['name'=>'Grade 7','code'=>'G-07','subject_id'=>'4'],

            ['name'=>'Grade 8','code'=>'G-08','subject_id'=>'1'],
            ['name'=>'Grade 8','code'=>'G-08','subject_id'=>'2'],
            ['name'=>'Grade 8','code'=>'G-08','subject_id'=>'3'],
            ['name'=>'Grade 8','code'=>'G-08','subject_id'=>'4'],

            ['name'=>'Grade 9','code'=>'G-09','subject_id'=>'1'],
            ['name'=>'Grade 9','code'=>'G-09','subject_id'=>'2'],
            ['name'=>'Grade 9','code'=>'G-09','subject_id'=>'3'],
            ['name'=>'Grade 9','code'=>'G-09','subject_id'=>'4'],

            ['name'=>'Grade 10','code'=>'G-10','subject_id'=>'1'],
            ['name'=>'Grade 10','code'=>'G-10','subject_id'=>'2'],
            ['name'=>'Grade 10','code'=>'G-10','subject_id'=>'3'],
            ['name'=>'Grade 10','code'=>'G-10','subject_id'=>'4'],

            ['name'=>'Grade 11','code'=>'G-11','subject_id'=>'1'],
            ['name'=>'Grade 11','code'=>'G-11','subject_id'=>'2'],
            ['name'=>'Grade 11','code'=>'G-11','subject_id'=>'3'],
            ['name'=>'Grade 11','code'=>'G-11','subject_id'=>'4'],

            ['name'=>'Grade 12','code'=>'G-12','subject_id'=>'1'],
            ['name'=>'Grade 12','code'=>'G-12','subject_id'=>'2'],
            ['name'=>'Grade 12','code'=>'G-12','subject_id'=>'3'],
            ['name'=>'Grade 12','code'=>'G-12','subject_id'=>'4'],

        ];
        foreach($datas as $data){
            Grade::create($data);
        }
    }
}
