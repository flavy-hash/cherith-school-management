<?php

namespace Database\Seeders;

use App\Models\Standard;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TeacherSeeder::class);

        // Create standards STD 1 to STD 7
        $standards = [
            ['name' => 'STD 1', 'term_one_fee' => 15000, 'term_two_fee' => 15000],
            ['name' => 'STD 2', 'term_one_fee' => 16000, 'term_two_fee' => 16000],
            ['name' => 'STD 3', 'term_one_fee' => 17000, 'term_two_fee' => 17000],
            ['name' => 'STD 4', 'term_one_fee' => 18000, 'term_two_fee' => 18000],
            ['name' => 'STD 5', 'term_one_fee' => 19000, 'term_two_fee' => 19000],
            ['name' => 'STD 6', 'term_one_fee' => 20000, 'term_two_fee' => 20000],
            ['name' => 'STD 7', 'term_one_fee' => 21000, 'term_two_fee' => 21000],
        ];

        foreach ($standards as $standard) {
            Standard::firstOrCreate(
                ['name' => $standard['name']],
                $standard,
            );
        }

        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@cherithschool.ac.tz'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ],
        );
    }
}
