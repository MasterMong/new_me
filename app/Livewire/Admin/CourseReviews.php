<?php

namespace App\Livewire\Admin;

use App\Models\CourseReview as ReviewModel;
use Livewire\Component;
use Livewire\WithPagination;

class CourseReviews extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $ratingFilter = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRatingFilter(): void
    {
        $this->resetPage();
    }

    public function deleteReview(int $reviewId): void
    {
        ReviewModel::findOrFail($reviewId)->delete();
        $this->dispatch('toast', message: 'ลบรีวิวเรียบร้อยแล้ว', type: 'success');
    }

    public function render()
    {
        $reviews = ReviewModel::query()
            ->with(['user', 'course'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                })->orWhereHas('course', function ($q) {
                    $q->where('title', 'like', "%{$this->search}%");
                });
            })
            ->when($this->ratingFilter, fn ($q) => $q->where('rating', $this->ratingFilter))
            ->latest()
            ->paginate(15);

        $averageRating = ReviewModel::avg('rating') ?: 0;
        $totalReviews = ReviewModel::count();

        return view('livewire.admin.course-reviews', [
            'reviews' => $reviews,
            'averageRating' => round($averageRating, 1),
            'totalReviews' => $totalReviews,
        ])->layout('layouts.app', ['title' => 'รีวิวจากผู้เรียน']);
    }
}
