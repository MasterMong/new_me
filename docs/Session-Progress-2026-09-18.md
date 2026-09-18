# Session Progress — 2026-09-18

Working session covering: fixing the learner video/dashboard experience, importing
the real ME-Learning course content, and working through the fix list from
`fix_me_learnning.pdf` (a QA walkthrough deck). All work below is committed and
pushed to `origin/master` (latest: `7d66732`).

## Where things stand

- **Real course content is live.** The two fake demo courses are gone. There's
  one real course (id `10` in the dev DB) — "หลักสูตรพัฒนาศักยภาพนักติดตาม
  ประเมินผลการบริหารและการจัดการศึกษาขั้นพื้นฐาน" — with 9 modules, real
  MCQ pre/post-tests parsed from the source `.docx` files (answer keys recovered
  from Word's bold-text convention), and real converted-to-PDF knowledge sheets.
  See `database/seeders/RealCourseSeeder.php` and
  `app/Services/RealCourseContentParser.php`.
- **Fix tracker artifact**: <https://claude.ai/artifact/GH8mN3MXTJszhvVzCWh6eW> —
  the 12-item checklist built from `fix_me_learnning.pdf`, with shared checkboxes.
  **6 of 12 items done** (L1, L3, L7, L8, L9, E1) — see breakdown below.
- All 8 original demo users are intact (`admin@`, `expert1@`/`expert2@`,
  `learner1@`–`learner5@me-learning.go.th`).
- Full test suite: 302 passing, Pint clean, as of the last commit.

## Fix tracker — done this session

| # | Item | What changed |
|---|---|---|
| L1 | Post-login goes to `/` | `config/fortify.php` `home` |
| L3 | Module stat badges | Already matched seeded data |
| L7 | Worksheet download system | New `ContentType::Worksheet`, timed answer-key unlock (`ModuleContent::answerKeyUnlockedFor()`), admin content editor support. Seeded placeholder worksheets on modules 1,2,4,5,6,9 |
| L8 | Shuffled retry questions | `AssessmentPlayer` reorders questions/choices per attempt (seeded, deterministic) |
| L9 | 3-strike module lockout | `Module::isLockedOutFor()`/`resetProgressFor()`, restart flow in `CoursePath` |
| E1 | Scoped expert dashboard | Already matched existing `requires_expert_review` mechanism |

## Fix tracker — still open

| # | Item | Notes |
|---|---|---|
| L2 | Course description as structured content | Course detail page needs target-audience/format/completion-criteria/etc. fields, not just one plain description |
| L4 | Trim pre/post-test cards from course roadmap | Revisit against the `CoursePath` redesign done earlier this session |
| L5 | "Outline" content item per module | No outline content type yet; admin reordering already works |
| L6 | Rich-text knowledge sheets | Currently rendered as a PDF iframe, not editable rich text w/ inline images |
| L10 | Score/certificate display polish | Needs a check, not confirmed broken |
| E2 | Expert report export | Needs a check against `Expert\Reports.php` |

## Known loose ends / gotchas

- **Placeholder content**: every module's videos, and the 6 self-download
  worksheets, point at placeholder files (a real working MP4 for video; DomPDF-
  generated placeholder PDFs for worksheets). An admin needs to swap in real
  files via the module content editor (`/admin/courses/{course}/modules`) once
  they exist.
- **M8 post-test**: one question (Hattie & Timperley) had a stray-bold-formatting
  bug in the source `.docx` that made two choices look correct — fixed directly
  in the source file (`ข้อมูลทำระบบ ME-Learning/4. Pre-Post Test/M8 แบบทดสอบ.docx`).
- **Don't debug with raw `tinker` factory calls against the dev DB** — I did
  this once this session and left phantom `Course`/`User` rows behind (caught
  and cleaned up, but a reminder for next time: prefer Pest tests with
  `RefreshDatabase`, or wrap manual DB pokes in something reversible).
- `storage:link` has been run; PDFs live in `storage/app/public/course-content/`.

## Handy commands to pick back up

```bash
# Dev server is normally already running via: npm run dev (concurrently runs
# php artisan serve, queue:listen, pail, vite)

# Quick-login as any seeded user (local env only):
# POST /quick-login with { email: "learner1@me-learning.go.th" }

# Re-check test/build health:
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

## Where to find things

- Fix tracker artifact: <https://claude.ai/artifact/GH8mN3MXTJszhvVzCWh6eW>
- Source QA deck: `~/Downloads/fix_me_learnning.pdf`
- Source course materials (imported): `ข้อมูลทำระบบ ME-Learning/` (repo root)
- Real course importer: `database/seeders/RealCourseSeeder.php`
- Docx parsing: `app/Services/DocxTextExtractor.php`,
  `app/Services/RealCourseContentParser.php`
