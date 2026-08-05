<?php

namespace App\Livewire\Learner;

use App\Models\ContentView;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CoursePlayer extends Component
{
    public Course $course;

    public Module $module;

    public ?ModuleContent $activeContent = null;

    public ?Enrollment $enrollment = null;

    public function mount(Course $course, Module $module, ?ModuleContent $content = null)
    {
        $this->course = $course;
        $this->module = $module;

        $this->enrollment = Enrollment::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->first();

        if (! $this->enrollment) {
            return redirect()->route('courses.show', $course);
        }

        // Default to first content if none selected
        if (! $content) {
            $this->activeContent = $this->module->contents()->visibleTo(Auth::user())->orderBy('sort_order')->first();
        } else {
            abort_unless($content->isVisibleTo(Auth::user()), 403);
            $this->activeContent = $content;
        }

        if (! $this->activeContent) {
            return redirect()->route('learn.courses.show', $this->course);
        }
    }

    public function selectContent(ModuleContent $content)
    {
        if ($content->isVisibleTo(Auth::user()) && $this->isContentAccessible($content)) {
            $this->activeContent = $content;
        }
    }

    public function updateProgress($durationSec, $lastPositionSec, $isCompleted = false)
    {
        if (! $this->activeContent) {
            return;
        }

        ContentView::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'content_id' => $this->activeContent->id,
            ],
            [
                'watch_duration_sec' => $durationSec,
                'last_position_sec' => $lastPositionSec,
                'is_completed' => $isCompleted,
                'viewed_at' => now(),
            ]
        );

        if ($isCompleted) {
            $this->dispatch('contentCompleted');
        }
    }

    public function isContentAccessible(ModuleContent $content): bool
    {
        if (! $this->module->is_sequential) {
            return true;
        }

        // Get all contents visible to the user that come before this one
        $previousContents = $this->module->contents()
            ->visibleTo(Auth::user())
            ->where('sort_order', '<', $content->sort_order)
            ->get();

        foreach ($previousContents as $prev) {
            $view = ContentView::where('user_id', Auth::id())
                ->where('content_id', $prev->id)
                ->first();

            if (! $view || ! $view->is_completed) {
                return false;
            }
        }

        return true;
    }

    public function render()
    {
        $contents = $this->module->contents()
            ->visibleTo(Auth::user())
            ->orderBy('sort_order')
            ->get()
            ->map(function ($content) {
                $view = $content->views->where('user_id', Auth::id())->first();
                $content->is_completed = $view?->is_completed ?? false;
                $content->is_accessible = $this->isContentAccessible($content);

                return $content;
            });

        return view('livewire.learner.course-player', [
            'contents' => $contents,
        ])->title($this->activeContent->title.' - '.$this->course->title);
    }

    protected function extractYoutubeId($url): ?string
    {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);

        return $match[1] ?? null;
    }
}
