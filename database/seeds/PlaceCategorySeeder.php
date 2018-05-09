<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlaceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('place_categories')->insert(['name' => '公共场合']);
        DB::table('place_categories')->insert(['name' => '商家']);
    }
}
