<?php

namespace Database\Seeders;

use App\Enums\UserPrefix;
use App\Enums\UserRole;
use App\Models\Affiliation;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $position = Position::first();
        $affiliation = Affiliation::first();

        User::create([
            'prefix' => UserPrefix::Nai->value,
            'first_name' => 'สมชาย',
            'last_name' => 'รักษาไทย',
            'email' => 'admin@me-learning.go.th',
            'password' => Hash::make('password'),
            'position_id' => $position?->id,
            'affiliation_id' => $affiliation?->id,
            'role' => UserRole::Admin->value,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'prefix' => UserPrefix::NangSao->value,
            'first_name' => 'วิภา',
            'last_name' => 'ชำนาญกิจ',
            'email' => 'expert1@me-learning.go.th',
            'password' => Hash::make('password'),
            'position_id' => $position?->id,
            'affiliation_id' => $affiliation?->id,
            'role' => UserRole::Expert->value,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'prefix' => UserPrefix::Nai->value,
            'first_name' => 'ประสิทธิ์',
            'last_name' => 'มงคลทรัพย์',
            'email' => 'expert2@me-learning.go.th',
            'password' => Hash::make('password'),
            'position_id' => $position?->id,
            'affiliation_id' => $affiliation?->id,
            'role' => UserRole::Expert->value,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $learners = [
            ['prefix' => 'นาย', 'first_name' => 'กมล', 'last_name' => 'ศรีสุข', 'email' => 'learner1@me-learning.go.th'],
            ['prefix' => 'นาง', 'first_name' => 'สุดา', 'last_name' => 'พงษ์ไพร', 'email' => 'learner2@me-learning.go.th'],
            ['prefix' => 'นางสาว', 'first_name' => 'พิมพ์ชนก', 'last_name' => 'ทองดี', 'email' => 'learner3@me-learning.go.th'],
            ['prefix' => 'นาย', 'first_name' => 'อนุชา', 'last_name' => 'สว่างรัตน์', 'email' => 'learner4@me-learning.go.th'],
            ['prefix' => 'นาง', 'first_name' => 'มณีรัตน์', 'last_name' => 'ใจดี', 'email' => 'learner5@me-learning.go.th'],
        ];

        foreach ($learners as $learner) {
            User::create([
                'prefix' => $learner['prefix'],
                'first_name' => $learner['first_name'],
                'last_name' => $learner['last_name'],
                'email' => $learner['email'],
                'password' => Hash::make('password'),
                'position_id' => $position?->id,
                'affiliation_id' => $affiliation?->id,
                'role' => UserRole::Learner->value,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
