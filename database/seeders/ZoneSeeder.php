<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Zone;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Zone::truncate();

        $zone = [
            ['name' => 'varachha', 'estatus' => '1'],
            ['name' => 'katargam', 'estatus' => '1'],
            ['name' => 'kamrej', 'estatus' => '1'],
            ['name' => 'udhna', 'estatus' => '1'],
        ];

        foreach ($zone as $key => $value) {
            Zone::create($value);
        }
        
    }
}
