<?php

namespace App\Livewire\Admin;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'totalUsers' => User::count(),
            'totalCourses' => Course::count(),
            'totalEnrollments' => Enrollment::count(),
            'totalCertificates' => Certificate::count(),
            'recentCourses' => Course::with('creator')->latest()->limit(5)->get(),
        ])->layout('layouts.app', ['title' => 'แดชบอร์ดผู้ดูแล']);
    }
}
