<?php

namespace App\Console\Commands;

use App\Models\Distribution;
use App\Models\Organization;
use App\Models\WareHouse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SchoolCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'school:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Schema::disableForeignKeyConstraints();
        // DB::table('countries')->truncate();
        // Schema::enableForeignKeyConstraints();


        $count=0;
        $collection= collect([]);
        $error=0;
        $csvFile = fopen(public_path("/data/sipSchools.csv"), "r");
  
        $firstline = true;
       
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if (!$firstline) {
                if($data[0]!=null && $data[1]!=null && $data[2]!=null)
                {
                    $name = trim(collect(explode(' ', $data[0]))->map(function ($segment) {
                        return mb_substr($segment, 0, 1);
                    })->join(' '));
            
                    $imageName = "https://ui-avatars.com/api/?name=".urlencode($name)."&color=7F9CF5&background=EBF4FF";
                    $organization=Organization::create([
                            "organization_type_id" => 5,
                            "name"=> $data[0],
                            "new_code"=> $data[1],
                            "region_id"=> $data[2],
                            "zone_id"=> $data[3],
                            "woreda_id"=> $data[4],
                            "sector_id"=> $data[5],
                            "ownership_id"=> $data[6],
                            "logo"=>$imageName,
                            "status"=> 1,
                    ]);    
                    WareHouse::create([
                        "branch"=>1,
                        "organization_id"=>$organization->id,
                        "country_id"=>1,
                        "region_id"=>$organization->region_id,
                        "zone_id"=>$organization->zone_id,
                        "woreda_id"=>$organization->woreda_id,
                    ]);
                    $this->line('#' . $count . '->  ' . ' ------------------------------------------------');
                        $count++;
                    }
                else{
                    $collection->push($data);
                    $this->line('XXXXXXXXXX--------  Not Registered: Region'.$data[0].' and Name  '.$data[1]. ' ----------XXXXXXXXXXXX');
                    $error++;
                }
               
            }
            $firstline = false;
        }
   
        fclose($csvFile);
        dd($collection->all());
    }
}
