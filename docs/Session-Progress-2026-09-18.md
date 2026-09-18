# Session Progress — 2026-09-18

Working session covering: fixing the learner video/dashboard experience, importing
the real ME-Learning course content, and working through the fix list from
`fix_me_learnning.pdf` (a QA walkthrough deck). All 12 items on that list are now
done. All work below is committed and pushed to `origin/master` (latest: `625482d`).

## Where things stand

- **Real course content is live.** The two fake demo courses are gone. There's
  one real course — "หลักสูตรพัฒนาศักยภาพนักติดตาม ประเมินผลการบริหารและการจัดการศึกษาขั้นพื้นฐาน" —
  with 9 modules, real MCQ pre/post-tests parsed from the source `.docx` files
  (answer keys recovered from Word's bold-text convention), and real knowledge
  sheets rendered as rich text with their embedded images (see L6 below).
  **The course's id changed from `10` to `1`** at some point mid-session when
  the dev DB got a fresh reseed outside this conversation — if older notes
  reference course id 10, that's stale; check `Course::first()` instead of
  assuming an id.
  See `database/seeders/RealCourseSeeder.php`,
  `app/Services/RealCourseContentParser.php`, `app/Services/DocxTextExtractor.php`.
- **Fix tracker artifact**: <https://claude.ai/artifact/GH8mN3MXTJszhvVzCWh6eW> —
  the 12-item checklist built from `fix_me_learnning.pdf`, with shared checkboxes.
  **12 of 12 items done.**
- All 8 original demo users are intact (`admin@`, `expert1@`/`expert2@`,
  `learner1@`–`learner5@me-learning.go.th`).
- Full test suite: 329 passing, Pint clean, as of the last commit.

## Fix tracker — all 12 done

| # | Item | What changed |
|---|---|---|
| L1 | Post-login goes to `/` | `config/fortify.php` `home` |
| L2 | Structured course description | New `target_audience`/`learning_format`/`completion_criteria`/`instructor_team`/`certification_info` columns on `courses`; admin form + public course page section; `RealCourseContentParser::parseCourseOverview()` parses them straight from the source docx |
| L3 | Module stat badges | Already matched seeded data |
| L4 | Trim course roadmap | Removed the course-wide pre/post-test rows from `course-path.blade.php` — the spotlight card already surfaces them |
| L5 | Outline content item per module | New `ContentType::Outline`; shown before the module's own pre-test in the tree and reachable before that pre-test is attempted (with a bypass-prevention check on `selectContent()`); `CoursePath` now routes learners to it first |
| L6 | Rich-text knowledge sheets | New `module_contents.body` (rich HTML); Document content renders it instead of a PDF iframe; Quill editor gained image upload; `RealCourseContentParser::renderKnowledgeSheetHtml()` converts the source docx into headed/listed HTML with its embedded images appended. All 9 real modules backfilled — 150 images extracted |
| L7 | Worksheet download system | New `ContentType::Worksheet`, timed answer-key unlock (`ModuleContent::answerKeyUnlockedFor()`), admin content editor support. Seeded placeholder worksheets on modules 1,2,4,5,6,9 |
| L8 | Shuffled retry questions | `AssessmentPlayer` reorders questions/choices per attempt (seeded, deterministic) |
| L9 | 3-strike module lockout | `Module::isLockedOutFor()`/`resetProgressFor()`, restart flow in `CoursePath` |
| L10 | Score & certificate display | Verified only — scores already render as percentages consistently, and the certificate card already shows the exact "ไฟล์ PDF กำลังเตรียมการ" state when `pdf_url` is null |
| E1 | Scoped expert dashboard | Already matched existing `requires_expert_review` mechanism |
| E2 | Exportable expert report | Added a CSV export button to `Expert\IndividualReport` — pre-test/post-test/worksheet attempts per learner across the expert's assigned modules |

## Post-ship fixes found while browser-testing L4/L5/L6

- **Overflow/scroll bug (commit `625482d`)**: the shared content-player
  container in `course-player.blade.php` centers its children
  (`items-center`), which without `overflow-hidden` let flexbox's
  automatic-minimum-size behavior force the container to grow to fit a tall
  rich-text body instead of respecting its `flex-1` sizing — content past the
  viewport was silently clipped with no way to scroll to it. Fixed by adding
  `overflow-hidden` to the shared container and `self-stretch` to the
  scrollable panels (Document body + Outline view). Caught by actually
  scrolling the real M1 knowledge sheet (~25,000px of content) in the browser
  — worth remembering that Pest/Livewire tests never catch this class of bug
  since they only inspect server-rendered HTML strings, not layout.
- **`APP_URL` mismatch**: `.env` had `APP_URL=http://127.0.0.1:8000`, but only
  `localhost` is reachable from the browser in this dev setup — every stored
  file URL (course thumbnails, worksheet PDFs, certificate templates, and the
  150 newly-extracted knowledge-sheet images) had the unreachable host baked
  in at creation time. Fixed by changing `APP_URL` to `http://localhost:8000`
  (config cache cleared) **and** backfilling the already-stored rows — a
  `.env` edit alone only affects URLs generated after the change:
  - `module_contents.file_url`: 6 rows
  - `module_contents.body` (the `<img>` tags in knowledge sheets): 7 rows
  - `module_contents.answer_key_url`: 6 rows
  - `certificate_templates.template_image_url`: 1 row
  - `.env` is gitignored by design, so this fix has no commit — if you reseed
    or move to a new machine/port, check `APP_URL` matches how you actually
    browse the site, or stored URLs will 404 again.

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
  and cleaned up). Direct `tinker` DB pokes are fine for reversible
  verification (e.g. marking a demo learner's `ContentView`/`TestAttempt` rows
  to unlock content for a browser check, then deleting them again afterward —
  done twice this session, both cleaned up), just don't leave them behind.
  Prefer Pest tests with `RefreshDatabase` for anything that doesn't need a
  real browser.
- Knowledge-sheet images from the source docx are anchored/floating drawings
  with no reliable link back to a specific paragraph, so
  `renderKnowledgeSheetHtml()` appends them as a gallery at the end rather
  than attempting to reproduce their exact original in-text position.
- `storage:link` has been run; files live in `storage/app/public/course-content/`
  (knowledge-sheet images under `course-content/knowledge-sheet-images/`).

## Handy commands to pick back up

```bash
# Dev server is normally already running via: npm run dev (concurrently runs
# php artisan serve, queue:listen, pail, vite)

# Quick-login as any seeded user (local env only) — also available as buttons
# on the /login page itself under "DEV QUICK LOGIN":
# POST /quick-login with { email: "learner1@me-learning.go.th" }

# Re-check test/build health:
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build   # needed after editing resources/js or resources/css
```

## Where to find things

- Fix tracker artifact: <https://claude.ai/artifact/GH8mN3MXTJszhvVzCWh6eW>
- Source QA deck: `~/Downloads/fix_me_learnning.pdf`
- Source course materials (imported): `ข้อมูลทำระบบ ME-Learning/` (repo root)
- Real course importer: `database/seeders/RealCourseSeeder.php`
- Docx parsing: `app/Services/DocxTextExtractor.php`,
  `app/Services/RealCourseContentParser.php`
- Rich-text editor (course/module descriptions, knowledge sheets, with
  optional image upload): `resources/views/partials/rich-editor.blade.php`,
  `resources/js/app.js` (`Alpine.data('richEditor', ...)`)
