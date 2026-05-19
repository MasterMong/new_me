<?php

namespace Database\Seeders;

use App\Enums\ExpertReviewStatus;
use App\Enums\ModuleProgressStatus;
use App\Enums\TestAttemptStatus;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\ExpertReview;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class LearnerProgressSeeder extends Seeder
{
    public function run(): void
    {
        $course1 = Course::where('duration_hours', 6)->first();
        $course2 = Course::where('duration_hours', 8)->first();

        $modules1 = Module::where('course_id', $course1->id)->orderBy('sort_order')->get();
        $modules2 = Module::where('course_id', $course2->id)->orderBy('sort_order')->get();

        $c1Pre = Assessment::where('course_id', $course1->id)->where('type', 'pre_test')->first();
        $c1Post = Assessment::where('course_id', $course1->id)->where('type', 'post_test')->first();
        $c2Pre = Assessment::where('course_id', $course2->id)->where('type', 'pre_test')->first();
        $c2Post = Assessment::where('course_id', $course2->id)->where('type', 'post_test')->first();

        $learner1 = User::where('email', 'learner1@me-learning.go.th')->first();
        $learner2 = User::where('email', 'learner2@me-learning.go.th')->first();
        $learner3 = User::where('email', 'learner3@me-learning.go.th')->first();
        $learner4 = User::where('email', 'learner4@me-learning.go.th')->first();
        $learner5 = User::where('email', 'learner5@me-learning.go.th')->first();
        $expert1 = User::where('email', 'expert1@me-learning.go.th')->first();

        $this->seedScenarioA($learner1, $modules1, $c1Pre, $c1Post);
        $this->seedScenarioB($learner2, $modules1, $c1Pre);
        $this->seedScenarioC($learner3, $modules2, $c2Pre, $c2Post, $expert1);
        $this->seedScenarioD($learner4, $modules1, $c1Pre, $c1Post);
        $this->seedScenarioE($learner5, $modules1);
    }

    /** Scenario A — กมล: fully certified (all modules done, 2 attempts on post-test) */
    private function seedScenarioA(User $learner, Collection $modules, Assessment $preTest, Assessment $postTest): void
    {
        foreach ($modules as $i => $module) {
            ModuleProgress::firstOrCreate(
                ['user_id' => $learner->id, 'module_id' => $module->id],
                [
                    'status' => ModuleProgressStatus::Completed->value,
                    'started_at' => now()->subDays(55 - $i * 5),
                    'completed_at' => now()->subDays(52 - $i * 5),
                ]
            );
        }

        $pre = TestAttempt::firstOrCreate(
            ['user_id' => $learner->id, 'assessment_id' => $preTest->id, 'attempt_number' => 1],
            [
                'total_score' => 4.0,
                'max_score' => 5.0,
                'score_pct' => 80.0,
                'star_rating' => 3,
                'status' => TestAttemptStatus::Passed->value,
                'started_at' => now()->subDays(58),
                'submitted_at' => now()->subDays(58),
            ]
        );
        $this->seedAnswers($pre, 0.80);

        $post1 = TestAttempt::firstOrCreate(
            ['user_id' => $learner->id, 'assessment_id' => $postTest->id, 'attempt_number' => 1],
            [
                'total_score' => 2.0,
                'max_score' => 5.0,
                'score_pct' => 40.0,
                'star_rating' => 1,
                'status' => TestAttemptStatus::Failed->value,
                'started_at' => now()->subDays(20),
                'submitted_at' => now()->subDays(20),
            ]
        );
        $this->seedAnswers($post1, 0.40);

        $post2 = TestAttempt::firstOrCreate(
            ['user_id' => $learner->id, 'assessment_id' => $postTest->id, 'attempt_number' => 2],
            [
                'total_score' => 3.75,
                'max_score' => 5.0,
                'score_pct' => 75.0,
                'star_rating' => 2,
                'status' => TestAttemptStatus::Passed->value,
                'started_at' => now()->subDays(15),
                'submitted_at' => now()->subDays(15),
            ]
        );
        $this->seedAnswers($post2, 0.75);
    }

    /** Scenario B — สุดา: 3/5 modules done, pre-test passed, no post-test yet */
    private function seedScenarioB(User $learner, Collection $modules, Assessment $preTest): void
    {
        $statuses = [
            ModuleProgressStatus::Completed->value,
            ModuleProgressStatus::Completed->value,
            ModuleProgressStatus::Completed->value,
            ModuleProgressStatus::InProgress->value,
            ModuleProgressStatus::Locked->value,
        ];

        foreach ($modules as $i => $module) {
            $started = in_array($statuses[$i], [ModuleProgressStatus::Completed->value, ModuleProgressStatus::InProgress->value])
                ? now()->subDays(25 - $i * 3)
                : null;
            $completed = $statuses[$i] === ModuleProgressStatus::Completed->value
                ? now()->subDays(22 - $i * 3)
                : null;

            ModuleProgress::firstOrCreate(
                ['user_id' => $learner->id, 'module_id' => $module->id],
                ['status' => $statuses[$i], 'started_at' => $started, 'completed_at' => $completed]
            );
        }

        $pre = TestAttempt::firstOrCreate(
            ['user_id' => $learner->id, 'assessment_id' => $preTest->id, 'attempt_number' => 1],
            [
                'total_score' => 4.25,
                'max_score' => 5.0,
                'score_pct' => 85.0,
                'star_rating' => 3,
                'status' => TestAttemptStatus::Passed->value,
                'started_at' => now()->subDays(28),
                'submitted_at' => now()->subDays(28),
            ]
        );
        $this->seedAnswers($pre, 0.85);
    }

    /** Scenario C — พิมพ์ชนก: all modules done on course 2, post-test pending expert review */
    private function seedScenarioC(
        User $learner,
        Collection $modules,
        Assessment $preTest,
        Assessment $postTest,
        User $expert
    ): void {
        foreach ($modules as $i => $module) {
            ModuleProgress::firstOrCreate(
                ['user_id' => $learner->id, 'module_id' => $module->id],
                [
                    'status' => ModuleProgressStatus::Completed->value,
                    'started_at' => now()->subDays(18 - $i * 3),
                    'completed_at' => now()->subDays(15 - $i * 3),
                ]
            );
        }

        $pre = TestAttempt::firstOrCreate(
            ['user_id' => $learner->id, 'assessment_id' => $preTest->id, 'attempt_number' => 1],
            [
                'total_score' => 3.5,
                'max_score' => 5.0,
                'score_pct' => 70.0,
                'star_rating' => 2,
                'status' => TestAttemptStatus::Passed->value,
                'started_at' => now()->subDays(19),
                'submitted_at' => now()->subDays(19),
            ]
        );
        $this->seedAnswers($pre, 0.70);

        $post = TestAttempt::firstOrCreate(
            ['user_id' => $learner->id, 'assessment_id' => $postTest->id, 'attempt_number' => 1],
            [
                'total_score' => null,
                'max_score' => 6.0, // 4 MC (1pt each) + 1 essay (2pts)
                'score_pct' => null,
                'star_rating' => null,
                'status' => TestAttemptStatus::PendingReview->value,
                'started_at' => now()->subDays(5),
                'submitted_at' => now()->subDays(4),
            ]
        );
        $this->seedAnswers($post, 0.60);

        ExpertReview::firstOrCreate(
            ['attempt_id' => $post->id],
            [
                'expert_id' => $expert->id,
                'status' => ExpertReviewStatus::Pending->value,
                'score' => null,
                'feedback' => null,
                'reviewed_at' => null,
            ]
        );
    }

    /** Scenario D — อนุชา: module 1 done, failed post-test attempt */
    private function seedScenarioD(
        User $learner,
        Collection $modules,
        Assessment $preTest,
        Assessment $postTest
    ): void {
        foreach ($modules as $i => $module) {
            $isFirst = $i === 0;
            $status = $isFirst ? ModuleProgressStatus::Completed->value : ModuleProgressStatus::Locked->value;
            ModuleProgress::firstOrCreate(
                ['user_id' => $learner->id, 'module_id' => $module->id],
                [
                    'status' => $status,
                    'started_at' => $isFirst ? now()->subDays(14) : null,
                    'completed_at' => $isFirst ? now()->subDays(12) : null,
                ]
            );
        }

        $pre = TestAttempt::firstOrCreate(
            ['user_id' => $learner->id, 'assessment_id' => $preTest->id, 'attempt_number' => 1],
            [
                'total_score' => 3.6,
                'max_score' => 5.0,
                'score_pct' => 72.0,
                'star_rating' => 2,
                'status' => TestAttemptStatus::Passed->value,
                'started_at' => now()->subDays(14),
                'submitted_at' => now()->subDays(14),
            ]
        );
        $this->seedAnswers($pre, 0.72);

        $post = TestAttempt::firstOrCreate(
            ['user_id' => $learner->id, 'assessment_id' => $postTest->id, 'attempt_number' => 1],
            [
                'total_score' => 2.25,
                'max_score' => 5.0,
                'score_pct' => 45.0,
                'star_rating' => 1,
                'status' => TestAttemptStatus::Failed->value,
                'started_at' => now()->subDays(10),
                'submitted_at' => now()->subDays(10),
            ]
        );
        $this->seedAnswers($post, 0.45);
    }

    /** Scenario E — มณีรัตน์: just enrolled, all locked */
    private function seedScenarioE(User $learner, Collection $modules): void
    {
        foreach ($modules as $module) {
            ModuleProgress::firstOrCreate(
                ['user_id' => $learner->id, 'module_id' => $module->id],
                ['status' => ModuleProgressStatus::Locked->value, 'started_at' => null, 'completed_at' => null]
            );
        }
    }

    /** Create TestAnswer rows for all questions in the attempt's assessment. */
    private function seedAnswers(TestAttempt $attempt, float $passRate): void
    {
        $questions = Question::where('assessment_id', $attempt->assessment_id)
            ->orderBy('sort_order')
            ->get();

        $correctCount = (int) round($questions->count() * $passRate);

        foreach ($questions as $index => $question) {
            $isInCorrectSlot = $index < $correctCount;

            if ($question->question_type === 'multiple_choice') {
                $correctChoice = QuestionChoice::where('question_id', $question->id)->where('is_correct', true)->first();
                $wrongChoice = QuestionChoice::where('question_id', $question->id)->where('is_correct', false)->first();
                $selected = $isInCorrectSlot ? $correctChoice : $wrongChoice;

                TestAnswer::firstOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                    [
                        'selected_choice_id' => $selected?->id,
                        'essay_text' => null,
                        'score' => $isInCorrectSlot ? $question->points : 0.0,
                        'is_correct' => $isInCorrectSlot,
                    ]
                );
            } elseif ($question->question_type === 'short_answer') {
                $answerText = $isInCorrectSlot ? $question->correct_answer : 'คำตอบที่ผิด';

                TestAnswer::firstOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                    [
                        'selected_choice_id' => null,
                        'essay_text' => $answerText,
                        'score' => $isInCorrectSlot ? $question->points : 0.0,
                        'is_correct' => $isInCorrectSlot,
                    ]
                );
            } else {
                // Essay — score pending review or partial
                $score = $isInCorrectSlot ? $question->points : ($question->points * 0.5);

                TestAnswer::firstOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                    [
                        'selected_choice_id' => null,
                        'essay_text' => 'คำตอบตัวอย่าง: ผู้เรียนได้อธิบายแนวคิดหลักอย่างครบถ้วน โดยอ้างอิงทฤษฎีที่เกี่ยวข้องและยกตัวอย่างประกอบการอธิบายได้อย่างชัดเจน',
                        'score' => $attempt->status === TestAttemptStatus::PendingReview->value ? null : $score,
                        'is_correct' => null,
                    ]
                );
            }
        }
    }
}
