<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $this->call(PlaceCategorySeeder::class);
         $this->call(PlaceSeeder::class);
         $this->call(UserSeeder::class);
         $this->call(LoginSeeder::class);
         $this->call(PostSeeder::class);
         $this->call(CommentSeeder::class);
    }
}
