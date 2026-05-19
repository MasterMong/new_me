<?php

use App\Livewire\Admin\Courses\Create as AdminCoursesCreate;
use App\Livewire\Admin\Courses\Modules as AdminCourseModules;
use App\Livewire\Admin\Courses\Edit as AdminCoursesEdit;
use App\Livewire\Admin\Courses\Index as AdminCoursesIndex;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Groups\Index as AdminGroupsIndex;
use App\Livewire\Admin\Groups\Members as AdminGroupMembers;
use App\Livewire\Admin\Reports\Index as AdminReportsIndex;
use App\Livewire\Admin\Users\Index as AdminUsersIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::get('/courses', AdminCoursesIndex::class)->name('courses.index');
    Route::get('/courses/create', AdminCoursesCreate::class)->name('courses.create');
    Route::get('/courses/{course}/edit', AdminCoursesEdit::class)->name('courses.edit');
    Route::get('/courses/{course}/modules', AdminCourseModules::class)->name('courses.modules');
    Route::get('/users', AdminUsersIndex::class)->name('users.index');
    Route::get('/groups', AdminGroupsIndex::class)->name('groups.index');
    Route::get('/groups/{group}/members', AdminGroupMembers::class)->name('groups.members');
    Route::get('/reports', AdminReportsIndex::class)->name('reports.index');
});

require __DIR__.'/settings.php';
