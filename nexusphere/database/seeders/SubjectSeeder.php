<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = ([
            ['subject_name' => 'AI&テクノロジー科'],
            ['subject_name' => 'デジタルテクノロジー科'],
            ['subject_name' => 'クリエイティブデザイン科'],
        ]);

        foreach($subjects as $subject){
            Subject::create($subject);
        }
    }
}
