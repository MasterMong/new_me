# Expert Review Workflow Specification

> **Version:** 1.0
> **Last Updated:** 2026-02-23
> **Related Docs:** ME-Learning-Database-Design-v2.md (Section 5.3), ME-Learning-PRD.md (Section 3.6)

---

## Overview

The expert review workflow manages the process where experts (ผู้เชี่ยวชาญ) review and grade learner submissions (essays and file uploads) for specific modules that require human evaluation.

---

## Business Requirements

### Expert Role Definition

**Who are Experts?**
- Users with `role = 'expert'` in the `users` table
- Typically 5-10 experts in the system
- All experts can review ALL modules requiring expert review
- No per-module assignment needed for MVP

**Expert Responsibilities:**
1. Review submissions from learners
2. Provide scores and feedback
3. Approve (ผ่าน) or request revision (รอแก้ไข)
4. Respond within 3 business days (SLA)

### Modules Requiring Expert Review

**Selection Criteria:**
- Modules marked with `requires_expert_review = TRUE`
- Typically 3 out of 9 modules in a course
- These modules contain:
  - Essay questions (`question_type = 'essay'`)
  - Short Answer questions (`question_type = 'short_answer'`)
  - File upload assignments (`question_type = 'file_upload'`)
  - Mixed assessments (`grading_mode = 'manual'` or `'mixed'`)

### Review States

**Submission Status Flow:**
```
1. pending_review (รอตรวจ)
   ↓
2. passed (ผ่าน) OR revision_needed (รอแก้ไข)
   ↓
3. If revision_needed → Learner resubmits → Back to pending_review
```

**Expert Decision Criteria:**
- **Passed (ผ่าน)**: Submission meets all requirements, quality is acceptable
- **Revision Needed (รอแก้ไข)**: Submission needs improvement, specific feedback provided

---

## Database Schema

### `expert_reviews` Table

```sql
CREATE TABLE expert_reviews (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  attempt_id BIGINT NOT NULL,
  expert_id BIGINT NOT NULL,
  status ENUM('pending','passed','revision_needed') DEFAULT 'pending',
  score DECIMAL(5,2) NULL,
  feedback TEXT NULL,
  reviewed_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (attempt_id) REFERENCES test_attempts(id) ON DELETE CASCADE,
  FOREIGN KEY (expert_id) REFERENCES users(id),
  UNIQUE KEY unique_attempt (attempt_id), -- One review per attempt
  INDEX idx_expert_status (expert_id, status),
  INDEX idx_status_created (status, created_at)
);
```

### `notifications` Table Updates

```sql
-- Notification types for expert review
INSERT INTO notifications (type) VALUES
('assignment_submitted'),
('review_completed'),
('revision_needed');
```

---

## Review Workflow

### Step 1: Learner Submits Assignment

**Endpoint:** `PUT /api/assessments/:id/attempts`

**Request:**
```json
{
  "answers": [
    {
      "question_id": 123,
      "essay_text": "คำตอบแบบบรรยาย...",
      "uploaded_file_url": null
    },
    {
      "question_id": 124,
      "essay_text": null,
      "uploaded_file_url": "https://storage.example.com/assignments/learner1-module3.pdf"
    }
  ]
}
```

**Backend Logic:**
```php
<?php

class AssessmentSubmissionController extends Controller
{
    public function submit(Request $request, Assessment $assessment)
    {
        $user = auth()->user();

        // Validate max attempts
        $attemptCount = TestAttempt::where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->count();

        if ($attemptCount >= $assessment->max_attempts) {
            return response()->json([
                'error' => 'Maximum attempts reached'
            ], 403);
        }

        // Create attempt
        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => $attemptCount + 1,
            'star_rating' => match($attemptCount + 1) {
                1 => 3,
                2 => 2,
                3 => 1
            },
            'status' => 'submitted',
            'submitted_at' => now()
        ]);

        // Save answers
        foreach ($request->answers as $answer) {
            TestAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $answer['question_id'],
                'essay_text' => $answer['essay_text'] ?? null,
                'uploaded_file_url' => $answer['uploaded_file_url'] ?? null
            ]);
        }

        // Check if manual grading required
        if ($assessment->requiresExpertReview()) {
            // Create expert review record
            ExpertReview::create([
                'attempt_id' => $attempt->id,
                'status' => 'pending_review'
            ]);

            // Notify all experts
            $this->notifyExperts($assessment, $attempt);
        } else {
            // Auto-grade
            $this->autoGrade($attempt);
        }

        return response()->json([
            'message' => 'Submission successful',
            'attempt_id' => $attempt->id,
            'requires_review' => $assessment->requiresExpertReview()
        ], 201);
    }

    protected function notifyExperts(Assessment $assessment, TestAttempt $attempt)
    {
        // Get all experts
        $experts = User::where('role', 'expert')->get();

        foreach ($experts as $expert) {
            Notification::create([
                'user_id' => $expert->id,
                'type' => 'assignment_submitted',
                'title' => 'มีใบงานใหม่รอตรวจ',
                'message' => "นักเรียน {$attempt->user->full_name} ส่งใบงาน {$assessment->title}",
                'reference_id' => $attempt->id,
                'is_read' => false
            ]);

            // Send email
            Mail::to($expert->email)->send(
                new AssignmentSubmitted($assessment, $attempt)
            );
        }
    }
}
```

---

### Step 2: Expert Views Dashboard

**Endpoint:** `GET /api/expert/dashboard`

**Response:**
```json
{
  "summary": {
    "pending_reviews": 12,
    "completed_this_month": 45,
    "completed_all_time": 120
  },
  "modules_under_review": [
    {
      "id": 3,
      "title": "Module 3: การสร้างเครื่องมือประเมินผล",
      "pending_count": 5,
      "completed_this_week": 8
    },
    {
      "id": 5,
      "title": "Module 5: การวิเคราะห์ข้อมูล",
      "pending_count": 7,
      "completed_this_week": 12
    }
  ]
}
```

**Frontend Dashboard:**
```vue
<template>
  <div class="expert-dashboard">
    <h1>แดชบอร์ดผู้เชี่ยวชาญ</h1>

    <!-- Summary Cards -->
    <div class="summary-cards">
      <div class="card pending">
        <div class="number">{{ summary.pending_reviews }}</div>
        <div class="label">ใบงานรอตรวจ</div>
      </div>
      <div class="card completed-month">
        <div class="number">{{ summary.completed_this_month }}</div>
        <div class="label">ตรวจแล้วเดือนนี้</div>
      </div>
      <div class="card completed-all">
        <div class="number">{{ summary.completed_all_time }}</div>
        <div class="label">ตรวจแล้วทั้งหมด</div>
      </div>
    </div>

    <!-- Modules Under Review -->
    <h2>Modules ที่รับผิดชอบ</h2>
    <div class="modules-list">
      <div
        v-for="module in modules"
        :key="module.id"
        class="module-card"
        @click="goToSubmissions(module.id)"
      >
        <h3>{{ module.title }}</h3>
        <div class="stats">
          <span class="pending">รอตรวจ: {{ module.pending_count }}</span>
          <span class="completed">ตรวจแล้วสัปดาห์นี้: {{ module.completed_this_week }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';
import { useRouter } from 'vue-router';

const router = useRouter();
const summary = ref({});
const modules = ref([]);

onMounted(async () => {
  const response = await api.get('/expert/dashboard');
  summary.value = response.data.summary;
  modules.value = response.data.modules_under_review;
});

const goToSubmissions = (moduleId) => {
  router.push(`/expert/modules/${moduleId}/submissions`);
};
</script>
```

---

### Step 3: Expert Views Submission List

**Endpoint:** `GET /api/expert/modules/:id/submissions`

**Query Parameters:**
- `status`: `pending`, `passed`, `revision_needed` (optional)
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20)

**Response:**
```json
{
  "data": [
    {
      "attempt_id": 456,
      "learner": {
        "id": 123,
        "full_name": "นายสมชาย รักเรียน",
        "email": "somchai@example.com",
        "position": "ครูประเมินผล",
        "affiliation": "สพฐ. เขต 1"
      },
      "assessment": {
        "id": 7,
        "title": "Module 3: การสร้างเครื่องมือประเมินผล"
      },
      "submitted_at": "2026-02-23T10:30:00Z",
      "status": "pending_review",
      "days_waiting": 2
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 12,
    "per_page": 20
  }
}
```

**Backend Implementation:**
```php
<?php

class ExpertSubmissionController extends Controller
{
    public function index(Request $request, Module $module)
    {
        $expert = auth()->user();

        // Verify this module requires expert review
        if (!$module->requires_expert_review) {
            return response()->json([
                'error' => 'This module does not require expert review'
            ], 400);
        }

        // Build query
        $query = TestAttempt::with(['user', 'assessment'])
            ->whereHas('assessment', function ($q) use ($module) {
                $q->where('module_id', $module->id);
            })
            ->where('status', 'submitted')
            ->whereHas('expertReview', function ($q) {
                if ($request->has('status')) {
                    $q->where('status', $request->status);
                }
            })
            ->orderBy('submitted_at', 'asc');

        // Paginate
        $submissions = $query->paginate($request->per_page ?? 20);

        // Format response
        return response()->json([
            'data' => $submissions->map(function ($attempt) {
                return [
                    'attempt_id' => $attempt->id,
                    'learner' => [
                        'id' => $attempt->user->id,
                        'full_name' => $attempt->user->full_name,
                        'email' => $attempt->user->email,
                        'position' => $attempt->user->position->name ?? null,
                        'affiliation' => $attempt->user->affiliation->name ?? null
                    ],
                    'assessment' => [
                        'id' => $attempt->assessment->id,
                        'title' => $attempt->assessment->title
                    ],
                    'submitted_at' => $attempt->submitted_at,
                    'status' => $attempt->expertReview->status,
                    'days_waiting' => now()->diffInDays($attempt->submitted_at)
                ];
            })
        ]);
    }
}
```

---

### Step 4: Expert Reviews Submission

**Endpoint:** `GET /api/expert/submissions/:id/review`

**Response:**
```json
{
  "submission": {
        "attempt_id": 456,
        "learner": {
          "full_name": "นายสมชาย รักเรียน",
          "email": "somchai@example.com",
          "position": "ครูประเมินผล",
          "experience": "2-5y",
          "affiliation": "สพฐ. เขต 1"
        },
        "assessment": {
          "id": 7,
          "title": "Module 3: การสร้างเครื่องมือประเมินผล",
          "max_score": 100,
          "passing_score": 60
        },
        "attempt_details": {
          "attempt_number": 1,
          "stars_at_stake": 3,
          "submitted_at": "2026-02-23T10:30:00Z"
        },
        "answers": [
          {
            "question_id": 121,
            "question_type": "short_answer",
            "question_text": "KPI คืออะไรสั้นๆ",
            "answer": {
              "essay_text": "ดัชนีชี้วัดความสำเร็จ"
            }
          },
          {
            "question_id": 123,
            "question_type": "essay",
            "question_text": "อธิบายขั้นตอนการสร้างแบบประเมิน...",
            "answer": {
              "essay_text": "ขั้นตอนการสร้างแบบประเมิน มีดังนี้...",
              "word_count": 350
            }
          },
      {
        "question_id": 124,
        "question_type": "file_upload",
        "question_text": "อัปโหลดแบบประเมินที่สร้าง",
        "answer": {
          "uploaded_file_url": "https://storage.example.com/assignments/learner1-module3.pdf",
          "file_name": "assignment.pdf",
          "file_size": 2048576 // bytes
        }
      }
    ],
    "previous_reviews": [
      {
        "attempt_number": 1,
        "expert_name": "ผศ.ดร. วิชัย ใจดี",
        "status": "revision_needed",
        "score": 45,
        "feedback": "แบบประเมินมีความครบถ้วน แต่ข้อสอบยังไม่ชัดเจน...",
        "reviewed_at": "2026-02-20T14:00:00Z"
      }
    ]
  }
}
```

**Frontend Review Interface:**
```vue
<template>
  <div class="submission-review">
    <div class="header">
      <h1>ตรวจใบงาน #{{ submission.attempt_id }}</h1>
      <button @click="goBack" class="back-btn">← กลับ</button>
    </div>

    <!-- Learner Info -->
    <div class="learner-info card">
      <h2>ข้อมูลผู้เรียน</h2>
      <table>
        <tr>
          <th>ชื่อ-นามสกุล:</th>
          <td>{{ submission.learner.full_name }}</td>
        </tr>
        <tr>
          <th>อีเมล:</th>
          <td>{{ submission.learner.email }}</td>
        </tr>
        <tr>
          <th>ตำแหน่ง:</th>
          <td>{{ submission.learner.position }}</td>
        </tr>
        <tr>
          <th>สังกัด:</th>
          <td>{{ submission.learner.affiliation }}</td>
        </tr>
        <tr>
          <th>ประสบการณ์:</th>
          <td>{{ submission.learner.experience }}</td>
        </tr>
      </table>
    </div>

    <!-- Assessment Info -->
    <div class="assessment-info card">
      <h2>ข้อมูลแบบทดสอบ</h2>
      <p><strong>ชื่อ:</strong> {{ submission.assessment.title }}</p>
      <p><strong>คะแนนเต็ม:</strong> {{ submission.assessment.max_score }}</p>
      <p><strong>เกณฑ์ผ่าน:</strong> {{ submission.assessment.passing_score }}%</p>
      <p><strong>ครั้งที่:</strong> {{ submission.attempt_details.attempt_number }}/3</p>
      <p><strong>ดาวที่ได้:</strong>
        {{ '⭐'.repeat(submission.attempt_details.stars_at_stake) }}
        {{ '☆'.repeat(3 - submission.attempt_details.stars_at_stake) }}
      </p>
    </div>

    <!-- Answers -->
    <div class="answers card">
      <h2>คำตอบ</h2>

      <!-- Essay Answer -->
      <div
        v-for="answer in essayAnswers"
        :key="answer.question_id"
        class="answer-item essay"
      >
        <h3>{{ answer.question_text }}</h3>
        <p class="word-count">คำทั้งหมด: {{ answer.word_count }} คำ</p>
        <div class="essay-text">{{ answer.answer.essay_text }}</div>
      </div>

      <!-- File Upload Answer -->
      <div
        v-for="answer in fileAnswers"
        :key="answer.question_id"
        class="answer-item file"
      >
        <h3>{{ answer.question_text }}</h3>
        <a
          :href="answer.answer.uploaded_file_url"
          target="_blank"
          class="file-link"
          download
        >
          📄 {{ answer.answer.file_name }}
          ({{ formatFileSize(answer.answer.file_size) }})
        </a>
        <button @click="previewFile(answer.answer.uploaded_file_url)">
          👁️ ดูตัวอย่าง
        </button>
      </div>
    </div>

    <!-- Previous Reviews -->
    <div v-if="submission.previous_reviews.length > 0" class="previous-reviews card">
      <h2>การตรวจครั้งก่อนหน้านี้</h2>
      <div
        v-for="review in submission.previous_reviews"
        :key="review.attempt_number"
        class="past-review"
      >
        <p><strong>ครั้งที่:</strong> {{ review.attempt_number }}</p>
        <p><strong>ผู้ตรวจ:</strong> {{ review.expert_name }}</p>
        <p><strong>สถานะ:</strong>
          <span :class="review.status">
            {{ review.status === 'passed' ? 'ผ่าน' : 'รอแก้ไข' }}
          </span>
        </p>
        <p><strong>คะแนน:</strong> {{ review.score }}</p>
        <p><strong>ข้อเสนอแนะ:</strong></p>
        <p>{{ review.feedback }}</p>
      </div>
    </div>

    <!-- Review Form -->
    <div class="review-form card">
      <h2>ให้คะแนนและข้อเสนอแนะ</h2>

      <form @submit.prevent="submitReview">
        <!-- Score -->
        <div class="form-group">
          <label for="score">คะแนน (0-{{ submission.assessment.max_score }}):</label>
          <input
            id="score"
            v-model.number="review.score"
            type="number"
            :min="0"
            :max="submission.assessment.max_score"
            step="0.5"
            required
          />
        </div>

        <!-- Status -->
        <div class="form-group">
          <label for="status">สถานะ:</label>
          <select id="status" v-model="review.status" required>
            <option value="passed">✅ ผ่าน</option>
            <option value="revision_needed">🔄 รอแก้ไข</option>
          </select>
        </div>

        <!-- Feedback -->
        <div class="form-group">
          <label for="feedback">ข้อเสนอแนะ:</label>
          <textarea
            id="feedback"
            v-model="review.feedback"
            rows="10"
            placeholder="กรอกข้อเสนอแนะสำหรับผู้เรียน..."
            required
          ></textarea>
          <p class="char-count">{{ review.feedback.length }} ตัวอักษร</p>
        </div>

        <!-- Submit Buttons -->
        <div class="form-actions">
          <button type="submit" class="btn-submit">
            💾 บันทึกผลการตรวจ
          </button>
          <button type="button" @click="goBack" class="btn-cancel">
            ยกเลิก
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();

const submission = ref({});
const review = ref({
  score: 0,
  status: 'passed',
  feedback: ''
});

const essayAnswers = computed(() =>
  submission.value.answers?.filter(a => a.question_type === 'essay') ?? []
);

const fileAnswers = computed(() =>
  submission.value.answers?.filter(a => a.question_type === 'file_upload') ?? []
);

onMounted(async () => {
  try {
    const response = await api.get(`/expert/submissions/${route.params.id}/review`);
    submission.value = response.data.submission;

    // Pre-fill with previous score if exists
    if (submission.value.previous_reviews.length > 0) {
      const lastReview = submission.value.previous_reviews[0];
      review.value.score = lastReview.score;
    }
  } catch (error) {
    alert('ไม่สามารถโหลดข้อมูลได้');
    router.back();
  }
});

const submitReview = async () => {
  if (!confirm('ยืนยันผลการตรวจ?')) return;

  try {
    await api.post(`/expert/submissions/${route.params.id}/review`, review.value);
    alert('บันทึกผลการตรวจเรียบร้อยแล้ว');
    router.back();
  } catch (error) {
    alert('ไม่สามารถบันทึกได้: ' + error.response.data.message);
  }
};

const goBack = () => router.back();
const formatFileSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};
</script>
```

---

### Step 5: Expert Submits Review

**Endpoint:** `POST /api/expert/submissions/:id/review`

**Request:**
```json
{
  "score": 75,
  "status": "passed",
  "feedback": "แบบประเมินสร้างได้ดีมีความครบถ้วน ข้อสอบชัดเจน เหมาะสมกับการใช้งานจริง"
}
```

**Backend Implementation:**
```php
<?php

class ExpertReviewController extends Controller
{
    public function submit(Request $request, TestAttempt $attempt)
    {
        $expert = auth()->user();

        // Verify expert role
        if ($expert->role !== 'expert') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get expert review record
        $expertReview = ExpertReview::where('attempt_id', $attempt->id)->firstOrFail();

        // Validate input
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:' . $attempt->assessment->max_score,
            'status' => 'required|in:passed,revision_needed',
            'feedback' => 'required|string|min:10|max:5000'
        ]);

        // Update expert review
        $expertReview->update([
            'expert_id' => $expert->id,
            'status' => $validated['status'],
            'score' => $validated['score'],
            'feedback' => $validated['feedback'],
            'reviewed_at' => now()
        ]);

        // Update attempt status
        $attempt->update([
            'status' => $validated['status'] === 'passed' ? 'passed' : 'revision_needed',
            'total_score' => $validated['score'],
            'score_pct' => ($validated['score'] / $attempt->assessment->max_score) * 100,
            'reviewed_by' => $expert->id,
            'reviewed_at' => now()
        ]);

        // Calculate scores for all answers
        $this->gradeAnswers($attempt, $validated['score']);

        // Notify learner
        $this->notifyLearner($attempt, $expertReview);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review_id' => $expertReview->id
        ]);
    }

    protected function gradeAnswers(TestAttempt $attempt, float $totalScore)
    {
        $answers = $attempt->answers;
        $answerCount = $answers->count();
        $scorePerAnswer = $answerCount > 0 ? $totalScore / $answerCount : 0;

        foreach ($answers as $answer) {
            $answer->update([
                'score' => round($scorePerAnswer, 2),
                'is_correct' => $scorePerAnswer >= ($totalScore / $answerCount / 2)
            ]);
        }
    }

    protected function notifyLearner(TestAttempt $attempt, ExpertReview $expertReview)
    {
        $learner = $attempt->user;
        $assessment = $attempt->assessment;

        // Create notification
        $type = $expertReview->status === 'passed'
            ? 'review_completed'
            : 'revision_needed';

        $title = $expertReview->status === 'passed'
            ? 'ผู้เชี่ยวชาญตรวจผลงานของคุณแล้ว'
            : 'ผู้เชี่ยวชาญขอให้แก้ไขผลงาน';

        Notification::create([
            'user_id' => $learner->id,
            'type' => $type,
            'title' => $title,
            'message' => "แบบทดสอบ {$assessment->title} ได้รับการตรวจแล้ว",
            'reference_id' => $attempt->id,
            'is_read' => false
        ]);

        // Send email
        Mail::to($learner->email)->send(
            new ExpertReviewCompleted($attempt, $expertReview)
        );
    }
}
```

---

### Step 6: Learner Receives Notification

**Learner View:**
```vue
<template>
  <div class="notification-item" :class="notification.type">
    <div class="icon">
      {{ notification.type === 'review_completed' ? '✅' : '🔄' }}
    </div>
    <div class="content">
      <h3>{{ notification.title }}</h3>
      <p>{{ notification.message }}</p>
      <div class="metadata">
        <span class="time">{{ formatTime(notification.created_at) }}</span>
        <button
          v-if="notification.reference_id"
          @click="viewResult(notification.reference_id)"
          class="view-btn"
        >
          ดูผลลัพธ์
        </button>
      </div>
    </div>
  </div>
</template>
```

---

## Email Notifications

### Assignment Submitted (to Expert)

```php
<?php

namespace App\Mail;

use App\Models\TestAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssignmentSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TestAttempt $attempt
    ) {}

    public function build()
    {
        return $this->subject('มีใบงานใหม่รอตรวจ - ME-Learning')
            ->markdown('emails.assignment-submitted', [
                'attempt' => $this->attempt,
                'learner' => $this->attempt->user,
                'assessment' => $this->attempt->assessment
            ]);
    }
}
```

**Email Template:**
```blade
@component('mail::message')
# มีใบงานใหม่รอตรวจ

เรียน คุณ{{ $learner->full_name }},

ส่งใบงานสำหรับ **{{ $assessment->title }}** แล้ว

## รายละเอียด

- **ชื่อผู้เรียน:** {{ $learner->full_name }}
- **อีเมล:** {{ $learner->email }}
- **ตำแหน่ง:** {{ $learner->position->name ?? '-' }}
- **สังกัด:** {{ $learner->affiliation->name ?? '-' }}
- **ส่งเมื่อ:** {{ $attempt->submitted_at->thFormat('j F Y H:i') }}

@component('mail::button')
{{ url('/expert/submissions/' . $attempt->id . '/review') }}
@endcomponent

ขอบคุณที่ร่วมเป็นส่วนหนึ่งของ ME-Learning

@endcomponent
```

---

### Review Completed (to Learner)

```blade
@component('mail::message')
# ผลการตรวจใบงาน

เรียน คุณ{{ $learner->full_name }},

ผู้เชี่ยวชาญตรวจใบงานของคุณเรียบร้อยแล้ว

## สรุปผล

- **แบบทดสอบ:** {{ $assessment->title }}
- **คะแนน:** {{ $review->score }} / {{ $assessment->max_score }}
- **สถานะ:** @if($review->status === 'passed') ✅ ผ่าน @else 🔄 รอแก้ไข @endif

## ข้อเสนอแนะ

{!! nl2br($review->feedback) !!}

@component('mail::button')
{{ url('/learn/courses/' . $assessment->course->id . '/assessments/' . $assessment->id . '/result') }}
@endcomponent

@endcomponent
```

---

## SLA Tracking

### Response Time Monitoring

```php
<?php

class ExpertSLAMonitor
{
    /**
     * Check for submissions approaching SLA deadline
     */
    public function checkDeadlines()
    {
        $slaDays = 3;
        $warningThreshold = 2; // Warn after 2 days

        $overdueSubmissions = TestAttempt::with(['user', 'assessment'])
            ->where('status', 'submitted')
            ->where('submitted_at', '<', now()->subDays($slaDays))
            ->whereHas('expertReview', function ($q) {
                $q->where('status', 'pending_review');
            })
            ->get();

        foreach ($overdueSubmissions as $submission) {
            $this->alertAdmin($submission);
        }

        $warningSubmissions = TestAttempt::with(['user', 'assessment'])
            ->where('status', 'submitted')
            ->where('submitted_at', '<', now()->subDays($warningThreshold))
            ->where('submitted_at', '>=', now()->subDays($slaDays))
            ->whereHas('expertReview', function ($q) {
                $q->where('status', 'pending_review');
            })
            ->get();

        foreach ($warningSubmissions as $submission) {
            $this->warnExperts($submission);
        }
    }

    protected function alertAdmin(TestAttempt $submission)
    {
        // Send alert to admin
        Mail::to(config('mail.admin.address'))->send(
            new SubmissionOverdueSLA($submission)
        );
    }

    protected function warnExperts(TestAttempt $submission)
    {
        // Send reminder to all experts
        $experts = User::where('role', 'expert')->get();

        foreach ($experts as $expert) {
            Mail::to($expert->email)->send(
                new SubmissionSLAWarning($submission)
            );
        }
    }
}

// Schedule in console.php
Schedule::call(new ExpertSLAMonitor())->dailyAt('09:00');
```

---

## Testing

### Unit Tests

```php
<?php

testExpertCanViewSubmissions()
{
    $expert = User::factory()->expert()->create();
    $module = Module::factory()->create(['requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['module_id' => $module->id]);
    $submission = TestAttempt::factory()
        ->for($assessment)
        ->create(['status' => 'submitted']);

    ExpertReview::factory()->create([
        'attempt_id' => $submission->id,
        'status' => 'pending_review'
    ]);

    $response = $this->actingAs($expert)
        ->getJson("/api/expert/modules/{$module->id}/submissions");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
}

testExpertCanSubmitReview()
{
    $expert = User::factory()->expert()->create();
    $submission = TestAttempt::factory()->create(['status' => 'submitted']);
    ExpertReview::factory()->create([
        'attempt_id' => $submission->id,
        'status' => 'pending_review'
    ]);

    $response = $this->actingAs($expert)
        ->postJson("/api/expert/submissions/{$submission->id}/review", [
            'score' => 75,
            'status' => 'passed',
            'feedback' => 'Good work!'
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('expert_reviews', [
        'attempt_id' => $submission->id,
        'expert_id' => $expert->id,
        'status' => 'passed',
        'score' => 75
    ]);

    $this->assertDatabaseHas('test_attempts', [
        'id' => $submission->id,
        'status' => 'passed',
        'total_score' => 75
    ]);
}

testLearnerNotifiedOnReview()
{
    Notification::fake();

    $expert = User::factory()->expert()->create();
    $learner = User::factory()->create();
    $submission = TestAttempt::factory()
        ->for($learner)
        ->create(['status' => 'submitted']);

    ExpertReview::factory()->create([
        'attempt_id' => $submission->id,
        'status' => 'pending_review'
    ]);

    $this->actingAs($expert)
        ->postJson("/api/expert/submissions/{$submission->id}/review", [
            'score' => 80,
            'status' => 'passed',
            'feedback' => 'Excellent!'
        ]);

    Notification::assertSentTo(
        $learner,
        function ($notification) {
            return $notification->type === 'review_completed'
                && $notification->reference_id === $submission->id;
        }
    );
}
```

---

**Document Status:** ✅ Ready for Implementation
**Priority:** High (Core feature)
**Dependencies:** Email system, Notification system, File storage
