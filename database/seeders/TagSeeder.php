<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=0; $i<3; $i++)
        Tag::create([
            'name' =>\Illuminate\Support\Str::random(3,10)
        ]);

    }
}
