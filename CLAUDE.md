<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>

# ME-Learning Project Context

## What This App Is

ME-Learning is a Thai e-Learning platform for สตผ. (Office of Basic Education Monitoring & Evaluation). It trains education personnel to become professional monitoring & evaluation practitioners. Full specs are in `docs/`.

Key documents:
- `docs/ME-Learning-PRD.md` — full requirements, user stories, functional requirements
- `docs/ME-Learning-Database-Design-v2.md` — 22-table schema with business rules
- `docs/ME-Learning-Frontend-Routes.md` — 35 routes across 4 roles
- `docs/Expert-Review-Workflow-Specification.md` — expert grading workflow
- `docs/ME-Learning QA Checklist.md` — 24 test cases

## User Roles

| Role | Prefix | Description |
|------|--------|-------------|
| `learner` | `/learn` | Self-paced learners; content visibility controlled by group membership |
| `expert` | `/expert` | Reviews essay/file submissions for 3 modules; SLA 3 business days |
| `admin` | `/admin` | Full system management, course/module/user/report management |
| Guest | `/`, `/courses`, etc. | Public pages, no login required |

Role is stored on `users.role` as `App\Enums\UserRole` enum.

## Design System

**Colors (Material Design 3 tokens — defined in `resources/css/app.css`):**
- `primary` = #003e74 (navy blue) — main brand color
- `primary-container` = #1a5694
- `on-primary` = #ffffff
- `secondary` = #745b00, `secondary-container` = #fdd355 (gold accent)
- Surface scale: `surface`, `surface-container-low`, `surface-container-lowest`, `surface-container-high`, `surface-container-highest`
- Error: `error` = #ba1a1a

**Typography:**
- Headlines: `font-headline` → Manrope (bold, tracking-tight)
- Body: `font-body` → Inter
- Labels: `font-label` → Inter

**Icons:** Material Symbols Outlined — load via Google Fonts. Usage: `<span class="material-symbols-outlined">icon_name</span>`. Filled variant: add `style="font-variation-settings: 'FILL' 1;"`.

**Custom utilities (defined in `app.css`):**
- `.glass-nav` — frosted glass navbar (rgba + backdrop-filter)
- `.hero-gradient` — linear-gradient(135deg, #003e74 → #1a5694)

**Flux UI components** are used throughout: `<flux:sidebar>`, `<flux:sidebar.item>`, `<flux:input>`, `<flux:button>`, `<flux:dropdown>`, `<flux:menu>`, `<flux:toast>`, etc.

## Layouts

Two app layouts exist:
- `x-layouts::app` (`layouts/app.blade.php`) → uses `layouts/app/sidebar.blade.php` — **use this for all authenticated pages**. Has the custom ME-Learning sidebar with Thai labels and role-based nav sections.
- `x-layouts::auth` (`layouts/auth.blade.php`) → for login/register/forgot-password pages.

The `layouts/app/header.blade.php` is the old Livewire starter-kit header (unused — the sidebar layout is active).

Sidebar nav items use `href="#"` placeholders. Wire them up as routes are built.

## What Is Already Built

**Database layer (complete):**
- All 22+ migrations in `database/migrations/`
- All 22 Eloquent models in `app/Models/`
- All Enums in `app/Enums/`: `UserRole`, `UserPrefix`, `UserExperience`, `AffiliationType`, `AssessmentType`, `ContentType`, `EnrollmentStatus`, `ExpertReviewStatus`, `GradingMode`, `ModuleProgressStatus`, `NotificationType`, `PrerequisiteType`, `QuestionType`, `TestAttemptStatus`

**Auth (Fortify — partially complete):**
- Routes: login, register, logout, forgot-password, reset-password, confirm-password, profile settings
- Views: `pages/auth/` — all auth pages exist
- ⚠️ Register form (`pages/auth/register.blade.php`) only has basic `name`/`email`/`password` fields. PRD requires: `prefix` (dropdown), `first_name`, `last_name`, `position_id`, `position_other`, `experience`, `affiliation_id`, `school_name`, `phone`.
- `CreateNewUser.php` action needs updating to match extended registration fields.

**Public landing page (complete):**
- `welcome.blade.php` — full landing page: glassmorphism navbar, hero, stats (static), 3 course cards (static placeholder), CTA section, footer.

**Authenticated dashboard (stub):**
- `dashboard.blade.php` — placeholder grid pattern only, not implemented.

## What Is NOT Built Yet

All 35 routes from the frontend spec are not yet registered. Priority order based on PRD:

1. **Learner area** (`/learn/*`) — 10 routes:
   - `/learn` — enrolled courses + progress
   - `/learn/courses/:courseId` — learning path (module list with lock/progress status)
   - `/learn/courses/:courseId/modules/:moduleId` — module content (video + documents)
   - `/learn/courses/:courseId/assessments/:assessmentId` — take assessment
   - `/learn/courses/:courseId/assessments/:assessmentId/result` — results page
   - `/learn/courses/:courseId/review` — course review (1–5 stars)
   - `/learn/results` — overall learning results
   - `/learn/certificates` — certificates list + PDF download
   - `/learn/profile` — edit profile
   - `/learn/change-password`

2. **Expert area** (`/expert/*`) — 6 routes:
   - `/expert` — dashboard (pending/completed counts)
   - `/expert/modules/:moduleId/submissions` — submission list with status filter
   - `/expert/submissions/:attemptId/review` — review interface
   - `/expert/reports`, `/expert/reports/:userId`, `/expert/change-password`

3. **Admin area** (`/admin/*`) — 12 routes:
   - `/admin` — stats dashboard
   - Course, module, content, assessment management
   - Group and user management
   - Reports + export

4. **Public pages** — `/courses`, `/courses/:courseId`, `/directory`, `/contact`

## Key Business Rules to Enforce in Code

- **Sequential modules**: A learner cannot access module N until module N-1 is `completed`. Check `module_prerequisites` table.
- **Content group access**: `content_group_access` table controls per-item visibility. If no record exists for a content item → visible to everyone. If records exist → only matching `user_group_memberships`.
- **Test attempts**: Max 3 per assessment. Stars: attempt 1 = 3 stars, attempt 2 = 2 stars, attempt 3 = 1 star.
- **Auto vs manual grading**: `grading_mode = 'auto'` on `questions` → grade immediately on submit. `grading_mode = 'manual'` → create `expert_reviews` record with `status = 'pending'`.
- **Certificate conditions** (all must be true):
  1. All `modules.is_required = TRUE` have `module_progress.status = 'completed'`
  2. All `assessments.is_required_for_cert = TRUE` have a `test_attempts.status = 'passed'`
  3. All `expert_reviews` for required assessments have `status = 'passed'`
  4. `course_reviews` record exists for this user+course
  5. Post-test `score_pct >= courses.passing_score_pct`

## Route Naming Convention

Follow this pattern when registering routes:
- Learner: `learn.dashboard`, `learn.courses.show`, `learn.modules.show`, `learn.assessments.show`, `learn.assessments.result`, `learn.results`, `learn.certificates`, `learn.profile`, `learn.review`
- Expert: `expert.dashboard`, `expert.submissions.index`, `expert.submissions.review`
- Admin: `admin.dashboard`, `admin.courses.index`, etc.

## Livewire Component Convention

Pages are Livewire full-page components stored in `app/Livewire/`. Follow the role-based directory structure:
- `app/Livewire/Learn/` — learner pages
- `app/Livewire/Expert/` — expert pages
- `app/Livewire/Admin/` — admin pages
- Views: `resources/views/livewire/learn/`, `resources/views/livewire/expert/`, `resources/views/livewire/admin/`

## User Model Helpers

`User` model has: `fullName()` (returns `"{prefix} {first_name} {last_name}"`), `initials()`. Use these in views.

