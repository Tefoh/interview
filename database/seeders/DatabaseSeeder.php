<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

         $user = \App\Models\User::factory()->create([
             'name' => 'user',
             'email' => 'user@example.com',
         ]);
         \App\Models\User::factory()->admin()->create([
             'name' => 'admin',
             'email' => 'admin@example.com',
         ]);

         Article::factory()->count(10)->create([
             'author_id' => $user->id
         ]);

         Article::factory()->count(20)->create();
    }
}
