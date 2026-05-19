<?php

namespace Database\Seeders;

use App\Models\LearnerGroup;
use App\Models\User;
use App\Models\UserGroupMembership;
use Illuminate\Database\Seeder;

class UserGroupMembershipSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@me-learning.go.th')->first();
        $groups = LearnerGroup::pluck('id', 'name');

        $assignments = [
            'learner1@me-learning.go.th' => 'ผู้เรียน สตผ.',
            'learner2@me-learning.go.th' => 'ผู้เรียนทั่วไป',
            'learner3@me-learning.go.th' => 'ศึกษานิเทศก์',
            'learner4@me-learning.go.th' => 'ผู้เรียนทั่วไป',
            'learner5@me-learning.go.th' => 'ผู้บริหาร สพท.',
        ];

        foreach ($assignments as $email => $groupName) {
            $user = User::where('email', $email)->first();
            UserGroupMembership::firstOrCreate(
                ['user_id' => $user->id, 'group_id' => $groups[$groupName]],
                ['assigned_at' => now()->subDays(30), 'assigned_by' => $admin->id]
            );
        }
    }
}
