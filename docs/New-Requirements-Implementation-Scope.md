# New Requirements — Implementation Scope

Analysis of what needs to change in the codebase for the following requirements. Based on the code state as of commit `3f5c2c4` (2026-08-05).

## Requirements (source list)

1. Pre, Post test ของแต่ละโมดูล
2. Bug: ผู้เรียนทั่วไปยังเห็นโมดูลของ สตผ (เห็นนอกสิทธิของตนเอง)
3. ใบงาน เพิ่มปุ่มบันทึก เพื่อบันทึกข้อมูลในระบบ แต่ยังไม่ส่งงาน ทำให้สามารถกลับมาแก้ไขต่อได้
4. กำหนดผู้เชี่ยวชาญต่อโมดูล ทำให้สามารถมอบหมายว่าใครตรวจตรงไหนบ้าง
5. การตรวจใบงาน ทำให้สามารถแก้ตัวได้ (กำหนดจำนวนครั้ง) ถ้าผู้เชี่ยวชาญตรวจแล้วไม่ผ่าน
6. Admin สามารถดูความคืบหน้าได้หลากหลายมิติ
7. รองรับจับเวลาในการทำข้อสอบ เปิด/ปิดได้
8. กรณีโมดูลนั้น ๆ มีชิ้นงาน ต้องผ่านก่อน ถึงจะไปเรียนโมดูลต่อไปได้

Suggested build order (interconnected): **2 → 3 → 5 → 8 → 4 → 7 → 1 → 6**

---

## 1. Pre/Post test ต่อโมดูล (per-module, not just per-course)

Schema already supports it — `assessments.module_id` exists and `AssessmentType` already has `PreTest`/`PostTest`. The gap is purely in the admin UI and learner display:

- `app/Livewire/Admin/Courses/Assessments.php` — `saveAssessment()` never sets `module_id` (assessments are always course-level) and `$assessmentType` defaults to `'quiz'`, which isn't even a valid `AssessmentType` case. Need a module picker in the form.
- `app/Livewire/Learner/CoursePath.php` — `preTest`/`postTest` lookups filter `course->assessments()->where('type','pre_test')`, course-wide only; needs per-module lookup when rendering each module row.
- `resources/views/livewire/admin/courses/assessments.blade.php` and `course-path.blade.php` — display updates.

## 2. Bug: learners see modules/content outside their group (สตผ leak)

Root cause found: `ContentGroupAccess` is fully wired in the **admin** side (`app/Livewire/Admin/Courses/Modules.php` sets it) but **never checked anywhere in learner-facing code**. `CoursePlayer.php` and `CoursePath.php` load `module->contents` with no group filter at all.

- Fix in `app/Models/ModuleContent.php` or a new scope: add a check "if this content has group-access rows, the current user must have a matching `UserGroupMembership`."
- Apply that filter in `CoursePlayer::render()` (content list) and `CoursePlayer::isContentAccessible()`, and in `CoursePath::render()` module/content listing.
- Open question: is "สตผ" restriction meant at content-item level (current schema) or should whole **modules** be group-gated too? Right now there's no `module`-level group access table — only per content-item. If the real requirement is "hide entire modules," that needs a new `module_group_access` table (or extending `content_group_access` semantics to modules).

## 3. ใบงาน — "Save draft" button (save without submitting)

Currently `AssessmentPlayer.php` only supports multiple-choice; there's no essay/file-upload UI or draft-vs-submit distinction at all.

- `app/Livewire/Learner/AssessmentPlayer.php` — add `saveDraft()` method that upserts `TestAnswer.essay_text`/`uploaded_file_url` and keeps `TestAttempt.status = in_progress` (vs `finish()`/submit which moves to `pending_review` for manual-graded questions).
- Add `WithFileUploads` trait, file input handling, essay textarea.
- `resources/views/livewire/learner/assessment-player.blade.php` — branch rendering per `question_type`, add "บันทึกร่าง" (save draft) button distinct from submit.

## 4. กำหนดผู้เชี่ยวชาญต่อโมดูล (assign experts per module)

No such table exists today — any user with role `expert` can review any module's submissions (confirmed: `SubmissionsList::mount()` only checks `module->requires_expert_review`, not identity).

- New migration: `module_expert_assignments` (`module_id`, `expert_id`, unique pair).
- New model `ModuleExpertAssignment`, relations on `Module`/`User`.
- Admin UI: new Livewire component (e.g. `app/Livewire/Admin/Courses/ExpertAssignments.php`) to assign experts to modules — likely alongside `Modules.php` management screen.
- Enforce in `app/Livewire/Expert/SubmissionsList.php::mount()` — `abort_unless` current expert is assigned to that module (or is admin).
- Filter `app/Livewire/Expert/Dashboard.php` — `$expertModules` query should scope to `Module::whereHas('expertAssignments', fn($q) => $q->where('expert_id', auth()->id()))`.

## 5. ตรวจใบงานไม่ผ่าน → แก้ตัวได้ (configurable retry count on revision_needed)

Mostly schema-ready: `TestAttemptStatus::RevisionNeeded` exists, `Assessment.max_attempts` exists, `ReviewSubmission.php` already sets status to `revision_needed`. Gaps:

- `AssessmentPlayer.php::startAttempt()` — needs to recognize a `revision_needed` attempt and let the learner start a new attempt (currently attempt-counting logic doesn't special-case this status vs a plain failed auto-graded attempt, and depends on item 3 being built first since worksheets can't be submitted at all yet).
- Open question: does "จำนวนครั้ง" here reuse `assessments.max_attempts`, or is a **separate** revision-attempt cap needed, distinct from the initial attempt limit? If separate, needs a new column (e.g. `max_revision_attempts`) on `assessments`.
- Learner UI needs to surface expert feedback + a clear "แก้ไขและส่งใหม่" action when `revision_needed`.

## 6. Admin ดูความคืบหน้าหลากหลายมิติ (multi-dimensional progress reporting)

`app/Livewire/Admin/Reporting/CourseProgress.php` and `UserProgress.php` exist (66/68 lines — fairly thin) plus `Admin/Reports/Index.php`. Needs input on which dimensions matter before scoping precisely — candidates: by group/affiliation, by module, by date range, by expert reviewer turnaround time.

## 7. จับเวลาทำข้อสอบ เปิด/ปิดได้ (toggleable exam timer)

No timer field exists anywhere (video `duration_minutes` is unrelated — that's content length, not exam time).

- New migration: add `is_timed` (bool) and `time_limit_minutes` (int, nullable) to `assessments`.
- `app/Livewire/Admin/Courses/Assessments.php` — expose the two fields in the form.
- `app/Livewire/Learner/AssessmentPlayer.php` — track `started_at` (already exists on `TestAttempt`) against `time_limit_minutes`, auto-submit (`finish()`) on expiry; needs a client-side countdown (Alpine/JS) synced to server time.

## 8. โมดูลมีชิ้นงาน → ต้องผ่านก่อนไปโมดูลถัดไป

Partially supported via `ModulePrerequisite` (type=`assessment`), but that requires an admin to manually create a prerequisite record per module pair. Likely intent is this should be **automatic**: if a module has an `Assessment` of type `assignment`/worksheet, passing it should implicitly gate the *next* module, without manual prerequisite setup.

- `app/Livewire/Learner/CoursePath.php::checkModuleAccessibility()` — add an implicit rule: for the *previous* module in `sort_order`, if it has any required assignment/worksheet assessment, check it's `passed` (via `TestAttempt`/`ExpertReview`) in addition to existing explicit `ModulePrerequisite` checks.
- Open question: should this be fully automatic (any assignment blocks the next module), or should it stay opt-in per module via a new `Module.requires_assignment_pass_to_proceed` flag? The latter is safer — avoids retroactively locking existing courses that have assignments not meant to be blocking.
