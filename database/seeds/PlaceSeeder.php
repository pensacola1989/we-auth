<?php

use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('places')->insert([
            'external_id' => MyHelper::newId(),
            'category_id' => 1,
            'name' => '上海来福士',
            'lat' => 31.26107,
            'lng' => 121.44451,
            'geo_hash' => MyHelper::convertGeoToHash('31.26107,121.44451')
        ]);
        DB::table('places')->insert([
            'external_id' => MyHelper::newId(),
            'category_id' => 1,
            'name' => '苏州凯德国际中心',
            'lat' => 41.26107,
            'lng' => 121.44451,
            'geo_hash' => MyHelper::convertGeoToHash('31.26107,121.44451')
        ]);
    }
}
