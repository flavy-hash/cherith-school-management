<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::firstOrCreate(
            ['name' => 'Mathematics'],
            ['code' => 'MATH'],
        );

        $teacher = User::updateOrCreate(
            ['email' => 'teacher1@cherithschool.ac.tz'],
            [
                'name' => 'Teacher 1',
                'password' => Hash::make('password'),
            ],
        );

        TeacherSubject::updateOrCreate(
            [
                'user_id' => $teacher->id,
                'subject_id' => $subject->id,
                'standard_id' => null,
            ],
            ['subject_id' => $subject->id],
        );
    }
}
