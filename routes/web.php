<?php

use App\Enums\UserRole;
use App\Livewire\Admin\CertificatesManager;
use App\Livewire\Admin\CourseReviews;
use App\Livewire\Admin\Courses\Assessments;
use App\Livewire\Admin\Courses\Create as AdminCoursesCreate;
use App\Livewire\Admin\Courses\Edit as AdminCoursesEdit;
use App\Livewire\Admin\Courses\Index as AdminCoursesIndex;
use App\Livewire\Admin\Courses\Modules as AdminCourseModules;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Groups\Index as AdminGroupsIndex;
use App\Livewire\Admin\Groups\Members as AdminGroupMembers;
use App\Livewire\Admin\Reporting\CourseProgress;
use App\Livewire\Admin\Reporting\UserProgress;
use App\Livewire\Admin\Reports\Index as AdminReportsIndex;
use App\Livewire\Admin\Users\Index as AdminUsersIndex;
use App\Livewire\Expert\IndividualReport;
use App\Livewire\Expert\Reports;
use App\Livewire\Expert\ReviewSubmission;
use App\Livewire\Expert\SubmissionsList;
use App\Livewire\Learner\AssessmentPlayer;
use App\Livewire\Learner\Certificates;
use App\Livewire\Learner\CoursePath;
use App\Livewire\Learner\CoursePlayer;
use App\Livewire\Learner\CourseReview;
use App\Livewire\Learner\Dashboard;
use App\Livewire\Learner\MyProgress;
use App\Livewire\Learner\Notifications;
use App\Livewire\Learner\Results;
use App\Livewire\Learner\Settings;
use App\Livewire\Public\Courses\Index as PublicCoursesIndex;
use App\Livewire\Public\Courses\Show as PublicCoursesShow;
use App\Livewire\Public\Directory as PublicDirectory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Public pages
Route::get('/courses', PublicCoursesIndex::class)->name('courses.index');
Route::get('/courses/{course}', PublicCoursesShow::class)->name('courses.show');
Route::get('/directory', PublicDirectory::class)->name('directory');
Route::view('/contact', 'contact')->name('contact');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (auth()->user()->role === UserRole::Admin) {
            return redirect()->route('admin.dashboard');
        }

        if (auth()->user()->role === UserRole::Expert) {
            return redirect()->route('expert.dashboard');
        }

        return redirect()->route('learn.dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'verified', 'learner'])->prefix('learn')->name('learn.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/courses/{course}', CoursePath::class)->name('courses.show');
    Route::get('/courses/{course}/modules/{module}', CoursePlayer::class)->name('courses.play');
    Route::get('/assessments/{assessment}', AssessmentPlayer::class)->name('assessments.show');
    Route::get('/progress', MyProgress::class)->name('progress');
    Route::get('/certificates', Certificates::class)->name('certificates.index');
    Route::get('/notifications', Notifications::class)->name('notifications.index');
    Route::get('/settings', Settings::class)->name('settings.index');
    Route::get('/results', Results::class)->name('results.index');
    Route::get('/courses/{course}/review', CourseReview::class)->name('courses.review');
});

Route::middleware(['auth', 'verified', 'expert'])->prefix('expert')->name('expert.')->group(function () {
    Route::get('/', App\Livewire\Expert\Dashboard::class)->name('dashboard');
    Route::get('/modules/{module}/submissions', SubmissionsList::class)->name('submissions.index');
    Route::get('/submissions/{attempt}/review', ReviewSubmission::class)->name('submissions.review');
    Route::get('/reports', Reports::class)->name('reports.index');
    Route::get('/reports/{user}', IndividualReport::class)->name('reports.show');
    Route::get('/settings', App\Livewire\Expert\Settings::class)->name('settings');

    // Reporting (Shared with Admin)
    Route::prefix('reporting')->name('reporting.')->group(function () {
        Route::get('/course-progress', CourseProgress::class)->name('course-progress');
        Route::get('/user-progress', UserProgress::class)->name('user-progress');
    });
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::get('/courses', AdminCoursesIndex::class)->name('courses.index');
    Route::get('/courses/create', AdminCoursesCreate::class)->name('courses.create');
    Route::get('/courses/{course}/edit', AdminCoursesEdit::class)->name('courses.edit');
    Route::get('/courses/{course}/modules', AdminCourseModules::class)->name('courses.modules');
    Route::get('/courses/{course}/assessments', Assessments::class)->name('courses.assessments');
    Route::get('/users', AdminUsersIndex::class)->name('users.index');
    Route::get('/groups', AdminGroupsIndex::class)->name('groups.index');
    Route::get('/groups/{group}/members', AdminGroupMembers::class)->name('groups.members');
    Route::get('/reviews', CourseReviews::class)->name('reviews.index');
    Route::get('/certificates', CertificatesManager::class)->name('certificates.index');

    // Reporting
    Route::prefix('reporting')->name('reporting.')->group(function () {
        Route::get('/course-progress', CourseProgress::class)->name('course-progress');
        Route::get('/user-progress', UserProgress::class)->name('user-progress');
    });
    Route::get('/reports', AdminReportsIndex::class)->name('reports.index');
});

require __DIR__.'/settings.php';

if (app()->isLocal()) {
    Route::post('/quick-login', function (Request $request) {
        $user = User::where('email', $request->input('email'))->firstOrFail();
        Auth::login($user);

        return redirect()->intended('/dashboard');
    })->name('quick-login');
}
