<?php

use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('posts')->insert([
           'external_id'=>MyHelper::newId(),
            'user_id'=>1,
            'place_id'=>1,
            'title'=>'oh Fuck',
            'content'=>'thisi is a fucking content',
            'lat' => 41.26107,
            'lng' => 121.44451
        ]);
        DB::table('posts')->insert([
            'external_id'=>MyHelper::newId(),
            'user_id'=>2,
            'place_id'=>2,
            'title'=>'oh Fuck',
            'content'=>'thisi is a fucking content',
            'lat' => 41.26107,
            'lng' => 121.44451
        ]);
        DB::table('posts')->insert([
            'external_id'=>MyHelper::newId(),
            'user_id'=>3,
            'place_id'=>1,
            'title'=>'oh Fuck',
            'content'=>'thisi is a fucking content',
            'lat' => 41.26107,
            'lng' => 121.44451
        ]);
        DB::table('posts')->insert([
            'external_id'=>MyHelper::newId(),
            'user_id'=>3,
            'place_id'=>2,
            'title'=>'oh Fuck',
            'content'=>'thisi is a fucking content',
            'lat' => 41.26107,
            'lng' => 121.44451
        ]);
        DB::table('posts')->insert([
            'external_id'=>MyHelper::newId(),
            'user_id'=>5,
            'place_id'=>1,
            'title'=>'oh Fuck',
            'content'=>'thisi is a fucking content',
            'lat' => 41.26107,
            'lng' => 121.44451
        ]);
        DB::table('posts')->insert([
            'external_id'=>MyHelper::newId(),
            'user_id'=>6,
            'place_id'=>1,
            'title'=>'oh Fuck',
            'content'=>'thisi is a fucking content',
            'lat' => 41.26107,
            'lng' => 121.44451
        ]);
    }
}
