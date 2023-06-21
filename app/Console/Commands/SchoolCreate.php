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

        Organization::truncate();
        $count=0;
        $collection= collect([]);
        $error=0;
        $csvFile = fopen(public_path("/data/sipSchools.csv"), "r");
  
        $firstline = true;
       
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if (!$firstline) {
                if($data[0]!=null && $data[1]!=null && $data[2]!=null)
                {
                    
                    $organization=Organization::create([
                            "organization_type_id" => 1,
                            "name"=> $data[0],
                            "new_code"=> $data[1],
                            "region_id"=> $data[2],
                            "zone_id"=> $data[3],
                            "woreda_id"=> $data[4],
                            "sector_id"=> $data[5],
                            "ownership_id"=> $data[6],
                    ]);    
                    WareHouse::create([
                        "branch"=>1,
                        "organization_id"=>$organization->id,
                        "country_id"=>1,
                        "region_id"=>$organization->region_id,
                        "zone_id"=>$organization->zone_id,
                        "woreda_id"=>$organization->woreda_id,
                    ]);
                    Distribution::create([
                        "printer_id"=>1,
                        "moe_id"=>2,
                        "region_id"=>$organization->region_id,
                        "zone_id"=>$organization->zone_id,
                        "woreda_id"=>$organization->woreda_id,
                        "school_id"=>$organization->id,
                        "step"=>5
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
