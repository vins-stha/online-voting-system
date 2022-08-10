<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        User::truncate();

        $faker = \Faker\Factory::create();

        $password = Hash::make("laravel");

        User::create([
            'name' => "laravel",
            'email'=>"email@email.com",
            "password"=> $password
        ]);

        for($i=0; $i<3; $i++)
        {
            User::create([
                'name' => $faker->name,
                'email' => $faker->email,
                'password'=> $password,
                'questions_count' =>rand(10,100),
                'up_votes_received' =>rand(10,100)
            ]);
        }
    }
}
