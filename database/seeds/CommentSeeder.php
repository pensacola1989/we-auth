<?php

use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('comments')->insert([
            'post_id'=>1,
            'from_uid'=>1,
            'to_uid'=>2,
            'content'=>'fuck you damn it'
        ]);
        DB::table('comments')->insert([
            'post_id'=>1,
            'from_uid'=>1,
            'to_uid'=>2,
            'content'=>'fuck you damn it'
        ]);
        DB::table('comments')->insert([
            'post_id'=>1,
            'from_uid'=>1,
            'to_uid'=>3,
            'content'=>'fuck you damn it'
        ]);
        DB::table('comments')->insert([
            'post_id'=>2,
            'from_uid'=>3,
            'to_uid'=>2,
            'content'=>'fuck you damn it'
        ]);
        DB::table('comments')->insert([
            'post_id'=>2,
            'from_uid'=>3,
            'to_uid'=>2,
            'content'=>'fuck you damn it'
        ]);
        DB::table('comments')->insert([
            'post_id'=>3,
            'from_uid'=>3,
            'to_uid'=>3,
            'content'=>'fuck you damn it'
        ]);
    }
}
