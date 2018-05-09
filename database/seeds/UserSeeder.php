<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Daniel.Wu',
            'external_id' => MyHelper::newId(),
            'nick_name' => '',
            'gender' => 1
        ]);

        DB::table('users')->insert([
            'name' => 'Lewis.Gao',
            'external_id' => MyHelper::newId(),
            'nick_name' => '',
            'gender' => 1
        ]);

        DB::table('users')->insert([
            'name' => 'Meme.Pang',
            'external_id' => MyHelper::newId(),
            'nick_name' => '',
            'gender' => 1
        ]);
        DB::table('users')->insert([
            'name' => 'Rain.Wu',
            'external_id' => MyHelper::newId(),
            'nick_name' => '',
            'gender' => 1
        ]);
        DB::table('users')->insert([
            'name' => 'Wesley.Wang',
            'external_id' => MyHelper::newId(),
            'nick_name' => '',
            'gender' => 1
        ]);
        DB::table('users')->insert([
            'name' => 'Guolu.Jiang',
            'external_id' => MyHelper::newId(),
            'nick_name' => '',
            'gender' => 1
        ]);
    }
}
