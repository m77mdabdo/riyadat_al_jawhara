# TASK — Bug hunt, code quality (SOLID), typography, email notifications, README, repo cleanup

This is an EXISTING Laravel 12 + Filament 3 project (Riyadat Al-Jawhara). It is NOT new.
The actual code is the source of truth. Read the existing docs and git log first to understand the current state.

---

## 0. OWNER INPUTS (the owner fills these in before running this task)

- **TEST_EMAIL:** `<put-your-email-here>`
  - This is the only address you may send test emails to.
  - If it is still a placeholder, don't send anything; just build and preview.
- **FONT NOTES:** `<describe what you don't like about the fonts; leave empty if nothing specific>`
- **README LICENSE:** `Proprietary — All rights reserved`
  - Change this line if the owner wants something else.
- **REPO VISIBILITY:** `private`
  - Set it to `public` if the repo will be public. Then run the extra checks in §7.

---

## 1. HOW TO RUN

- **Branch:** create `refactor/polish` from `upgrade/laravel-12`, or from `main` if that has already been merged. Tell me which one you used.
- **Order:** work through the sections in order, autonomously. Stop only at the STOP POINTS (§10).
- **Loop for every sub-section:**
  1. Implement.
  2. `php artisan config:clear`.
  3. Run the full test suite, which must be green.
  4. Run `./vendor/bin/pint --test`.
  5. Run static analysis (once added in §3).
  6. Run `npm run build` if the frontend changed.
  7. Commit.
  8. Update the internal log.
- **Internal log:** keep it at `.internal/WORK_LOG.md`. That folder is set up in §2.
- **Resuming:** if the session is interrupted, resume by reading this file and `.internal/WORK_LOG.md`.

---

## 2. PERMANENT RULES (never break)

1. **Environment and secrets:**
   - Never modify `.env`.
   - The owner has added SMTP settings to `.env`. You may check that the required mail keys are **present and non-empty**, but NEVER print, log, echo or commit their values.
   - Do not run `config:show mail` or anything else that prints secrets.
2. **Database:**
   - Never run `migrate:fresh`, `migrate:reset`, `migrate:rollback`, `db:wipe` or `db:seed` against the real MySQL database.
   - Never modify existing rows.
   - `php artisan migrate --pretend` is allowed, because it is read-only.
3. **Migrations** must be additive only: new tables, new nullable or defaulted columns, and indexes. Each needs a working `down()`. Test them on SQLite only, and list them for the owner.
4. **Tests:** always run `config:clear` before tests, and confirm that `phpunit.xml` uses SQLite `:memory:`.
5. **Git:** never push, never merge into `main`, never force, and never rewrite git history (see §10).
6. **Tests stay green:**
   - Never weaken or delete an existing test assertion to make a change pass.
   - Refactors must keep all existing tests green without editing their assertions.
   - You may move or rename test files.
7. **Language and design:**
   - Keep AR/EN parity and RTL.
   - Keep the current visual identity.
   - Keep every earlier guarantee: query budgets, CSP with zero violations, no horizontal scroll at 320–1920, 44 px targets, no text under 14 px on mobile, reduced motion, WCAG AA, and Lighthouse ≥ 90 production-like.
8. **Dependencies:** dev-only tools are fine. A new runtime dependency needs a justification in the log.
9. **Emails:** send real email ONLY to TEST_EMAIL. Never email real leads, customers or admins during this task.

---

## 3. SECTION A — Bug hunt: find everything that's broken, then fix it

First build a complete **findings list**. Then fix the findings in order of severity, with a regression test for each fix.

### A1. Automated sweeps

Run all of these and collect every error, warning and exception:

- **Tests:** the full suite; also run it with `--display-deprecations --display-warnings`.
- **Static analysis:** add **Larastan** (dev) at level 6 and fix the real issues. Only if level 6 produces an unreasonable volume, generate a baseline, and list what is baselined in the log.
- **Lazy loading:** `Model::preventLazyLoading()` and `preventSilentlyDiscardingAttributes()` in non-production environments. Crawl the site and fix every violation.
- **Production caches:** `php artisan config:cache && route:cache && view:cache && event:cache` must all succeed. Clear them afterwards.
- **Project sanity:** `composer validate`, `composer audit`, `npm audit --omit=dev`, `php artisan about`, `php artisan route:list`, `php artisan schedule:list`.
- **MySQL compatibility:** `php artisan migrate --pretend` against MySQL (read-only). Look for anything that behaves differently on MySQL than on SQLite, for example enum, JSON, default values, index name length, and the collation for Arabic text (`utf8mb4_unicode_ci`).
- **Logs:** scan `storage/logs/*.log` for exceptions and group them by root cause. Don't print any secrets.

### A2. Browser sweep (Playwright)

- **Public pages:**
  - Every public page in `/ar` and `/en`, with real query variants: filters, search, pagination, and the `?service=`, `?stone_type=` and `?project=` context.
  - Capture console errors and warnings, failed network requests, CSP violations, 4xx/5xx responses, and broken images.
- **Admin, as admin AND as editor:**
  - Open every Filament resource: list, create, edit and view.
  - Also open every custom page and widget.
  - Switch the admin language (ar/en).
  - Capture Livewire/JS errors, 403s where there shouldn't be any, and missing translations. Missing translations show up as raw keys such as `site.x.y` or `filament::...`.
- **Flows, submitted for real:**
  - contact form (valid, invalid, throttled, honeypot)
  - comment + reply
  - WhatsApp click beacon
  - gallery and lightbox
  - search
  - catalogue PDF download (AR and EN; the file must open and the Arabic must be shaped correctly)
  - lead CSV export
  - password reset request
  - profile update
  - `app:create-admin`
- **Jobs and scheduler:** run a queued job with `queue:work --once`, and run every scheduled command manually once.

### A3. Findings report + fixes

- Write `.internal/FINDINGS.md`: `# | area | symptom | root cause | severity (Critical/Important/Minor) | fix commit`.
- Fix everything you found. Give each fix a regression test.
- If a fix needs a decision from the owner, log it and continue with the rest.

---

## 4. SECTION B — Code quality: clean, SOLID, easy to extend

Goal: **behaviour stays identical, and the structure gets clean.** Refactor in small commits, with the tests green after each one.

### B1. Structure

- **Enums** for every status or type string: `LeadStatus`, `LeadSource`, `CommentStatus`, `ProjectSector`, `StoneOriginType`, and any others you find.
  - They implement Filament's `HasLabel` / `HasColor` / `HasIcon` where they are used in the admin.
  - Their labels are translated (AR/EN).
  - Replace every string literal across models, resources, widgets, controllers, views and tests.
  - Do NOT change DB column types.
- **Translation trait:** one `HasLocalizedAttributes` trait replaces the ~25 hand-written `*_ar`/`*_en` accessors. It keeps the current empty-language fallback behaviour exactly, and it gets unit tests.
- **Actions:** move business logic out of controllers and Filament pages into single-purpose Action classes under `app/Actions/<Domain>/`, for example:
  - `CreateLead`
  - `RecordWhatsappClick`
  - `StoreComment`
  - `ExportLeadsCsv`
  - `GenerateCatalogue`

  Controllers stay thin: FormRequest → Action → response.
- **Interfaces for swappable infrastructure,** bound in a service provider:
  - `CatalogueGenerator` (the mPDF implementation)
  - `StockMediaProvider` (the Pexels and Pixabay implementations)
  - analytics provider, if applicable
- **Authorization through Policies** (one per model), plus a `Gate::before` for admins where appropriate. Replace the hard-coded role checks and the `RestrictsDeletionToAdmins` trait where Policies cover them. The existing admin/editor permission matrix must behave EXACTLY as before; the existing authorization tests prove it.
- **Observers / model events** for cache busting and side effects, instead of scattered calls.
- **View composers or a view model** for shared layout data (settings, nav). No queries in Blade partials.
- **Config over magic values:** limits, throttles, retention days, image sizes and the notification recipients live in `config/site.php` (or an existing config file). No magic numbers in code.

### B2. Cleanup

- Remove dead code:
  - unused classes, components, views, routes, translation keys, CSS and JS
  - unused imports
  - commented-out code blocks
  - debug leftovers (`dd`, `dump`, `ray`, `console.log`)
- Consistent naming, and PHPDoc only where it adds information. Return types and parameter types everywhere they are practical.
- Blade: one component per repeated UI pattern (most exist already; finish the job). No inline `<style>`. Inline `<script>` only where the CSP nonce setup allows it.

### B3. Extensibility documentation

Write `docs/ARCHITECTURE.md`. It must cover:

- the folder conventions
- the request lifecycle (route → request → action → model → view)
- how the bilingual fields work
- how enums, policies, actions and interfaces are organized
- step-by-step recipes:
  - add a new content type (model + migration + Filament resource + policy + public page + sitemap + search + tests)
  - add a stone attribute
  - add a notification
  - add a language

---

## 5. SECTION C — Typography polish

Apply the FONT NOTES from §0 first, if there are any. Then:

- **Font inventory:** audit every font file actually loaded. Load only the weights that are used, subset them, keep `font-display: swap`, and preload only the fonts above the fold.
- **Arabic-specific rules:**
  - Never apply `letter-spacing`/`tracking-*` to Arabic text. It breaks letter joining. Scope any tracking to `:lang(en)` / LTR only.
  - Body line-height ≈ 1.7–1.9 for Arabic, and ≈ 1.5–1.7 for English.
  - No `uppercase` on Arabic.
  - Arabic headings get slightly more line-height.
  - Check that the display font renders every Arabic character used in the content, with no fallback boxes.
- **Scale:**
  - Define a single, consistent type scale (a fluid `clamp()` for headings) in the Tailwind config.
  - Make sure h1–h4, body, small and caption sizes are consistent across all pages, in both languages.
  - Arabic body text is optically about 1 step larger than English at the same visual size.
- **Numbers and mixed text:**
  - Pick one numeral style for Arabic pages (Western or Arabic-Indic), apply it consistently, and state it in the log.
  - Latin words, phone numbers and prices inside Arabic text use `<bdi>` / `dir="ltr"` correctly.
- **Weights:** a clear hierarchy (for example 400 body, 600 subheadings, 700 headings). No faux bold or faux italic.
- **Admin panel:** the Filament panel uses Cairo for Arabic and Inter for English, with the same rules.
- **Proof:** add a `/dev/typography` specimen page (local environment only, 404 in production) that shows the full scale in AR and EN. Take before/after screenshots of 3 key pages at 390 and 1440 into `.internal/visual/`.

---

## 6. SECTION D — Notifications and email: complete, reliable, beautifully branded

### D1. Notification inventory

Implement all of these, or complete them if they are partly there. Every one must be queued, with `tries`, `backoff` and a `failed()` handler that logs the failure. A mail failure must NEVER break a user-facing request.

| Notification | To | Channel | Notes |
|---|---|---|---|
| **New lead** | Company email (Settings) + all admin users (no duplicates) | mail + Filament database (bell) | Sent in each admin's own admin language. **Reply-To = the lead's email**, if given. |
| **Lead auto-reply** | The visitor (only if they gave an email) | mail | Sent in the lead's locale. Toggle in Settings (default ON). **Security:** it must NOT include the visitor's free-text message or any other free text they typed (no spam relay). It includes only their name, the chosen service, stone or project, and the company's contact channels. |
| **Follow-up due digest** | The assigned user (or admins, for unassigned leads) | mail + database | Daily at 08:00 **Asia/Riyadh** through the scheduler. Sent only when something is due. |
| **New comment pending moderation** | Admins + editors | database (bell); mail optional through a Settings toggle | Links to the moderation screen. |
| **Admin password reset** | User | mail | Uses the same branded layout. |
| **Test email** | TEST_EMAIL only | mail | Command `php artisan app:mail-test {to}`. It renders every template with sample data, in AR and EN. |

Recipients, toggles and the digest time all come from config/Settings. Nothing is hard-coded.

### D2. Email design (branded, colourful, professional)

- **Layout:** publish and customize the Laravel mail components (`resources/views/vendor/mail`) into ONE branded layout:
  - **Header:** a charcoal band with the logo from Settings (absolute HTTPS URL) plus a text fallback when images are blocked. A thin gold accent line under the header.
  - **Body:** a cream/white card on a soft sand background, with generous padding. The Najdi triangle band is a small, lightweight inline element (a table-based or hosted PNG; no SVG, because many email clients block SVG).
  - **Buttons:** the primary button is gold on charcoal; the secondary button is outlined. Both are bulletproof (table-based) buttons.
  - **Footer:** company name, phone (`tel:`), WhatsApp (`wa.me`), email, address and social links from Settings; plus the copyright line with the year.
- **RTL:** Arabic emails get `dir="rtl"` and `lang="ar"`, right-aligned text, and mirrored layouts. English emails get LTR.
- **Fonts:** email-safe stacks. Arabic: `Tahoma, 'Segoe UI', Arial, sans-serif`. English: `'Segoe UI', Helvetica, Arial, sans-serif`. Webfonts are progressive enhancement only.
- **Compatibility:**
  - All CSS is inlined, which the Laravel markdown mailer does.
  - Max width 600 px. It must be mobile-responsive.
  - Dark mode: `color-scheme` meta plus colours that stay readable when inverted.
  - Preheader text on every email.
  - It must render correctly in Gmail, Outlook (desktop + web), and Apple Mail / iOS Mail.
- **Plain-text version** for every email.
- **Content for the new-lead email:**
  - a bold header, for example "طلب عرض سعر جديد" / "New quote request"
  - a status chip
  - a clean two-column details table: name, phone, email, city, service, stone, project, source page, language, time in Asia/Riyadh
  - the message in a quoted block
  - quick-action buttons: **Open in admin**, **WhatsApp the customer** (`wa.me` with a pre-filled greeting), **Call** (`tel:`), and **Reply by email**
  - context links to the stone, service or project page the visitor came from
- **Subjects:** bilingual per recipient locale, informative, and without spammy words. For example: `طلب جديد: {name} — {stone/service}` / `New request: {name} — {stone/service}`.
- **Sender:** the From name and address come from config. Check and document SPF, DKIM and DMARC for the sending domain in `DEPLOY.md` (instructions only; don't change DNS).

### D3. Preview and verification

- An **admin-only Filament page, "Email previews"**. It renders every email with sample data, in AR and EN, in an iframe, with a "Send test to…" action that only accepts TEST_EMAIL.
- **Tests:**
  - `Notification::fake` / `Mail::fake` for every trigger
  - recipients and deduplication
  - the auto-reply excludes free text
  - locale per recipient
  - queue configuration
  - the digest only sends when something is due
  - a rendered HTML snapshot sanity check (contains RTL for AR, the logo fallback, and the buttons)
- If TEST_EMAIL is set: run `app:mail-test TEST_EMAIL` once, after checking that the mail config keys are present (without printing them). Report success or failure, and the error class only (no secrets).
- Save rendered HTML previews of each email to `.internal/emails/` for the owner.

---

## 7. SECTION E — Remove AI-tool traces and tidy the repository

1. **Search** all tracked files for these terms and list every hit: `claude`, `anthropic`, `copilot`, `chatgpt`, `openai`, `gpt`, `llm`, `ai agent`, `ai-generated`, `generated by`, `kilo`, `cursor`, `co-authored-by`, `prompt`, `probe suite`. Look in code comments, docs, config, composer/package metadata, `.github`, views and tests.
2. **Rewrite or remove** every hit, so the codebase reads as normal professional work. Keep all technical meaning, and keep any comment that explains a real decision, reworded in neutral language.
3. **Move internal process files out of the repo** into a git-ignored `.internal/` folder: `git rm --cached` + move, so they stay on disk locally. Add `.internal/` to `.gitignore`.
   - Move: `PROJECT_AUDIT_REPORT.md`, `docs/WORK_LOG.md`, `docs/MASTER_TASK.md`, this task file, `docs/visual-review/`, and any agent config files (`CLAUDE.md`, `.claude/`, `AGENTS.md`, `.cursor/`, and similar).
   - Keep in the repo: `README.md`, `DEPLOY.md`, `docs/ARCHITECTURE.md`, `docs/LAUNCH_CHECKLIST.md`, and `docs/IMAGE_CREDITS.md` (the image licence tracking is required).
4. **The `.kilo/` folder:** it is ignored by its own `.gitignore`. Confirm it is not tracked, add `.kilo/` to the root `.gitignore`, and list its size. Do NOT delete it; the owner will.
5. **Git history:** count the commits whose messages or trailers mention AI tools, and list them. Do NOT rewrite history (see §10).
6. **If REPO VISIBILITY is `public`:** also scan the history and tree for secrets, and check for client-confidential data (real phone numbers, emails, addresses in the seeders, and customer data in the test fixtures). Report anything you find, and replace it in the tree with placeholders.

---

## 8. SECTION F — README.md for GitHub (professional, bilingual)

Write a polished `README.md`, English first, followed by a concise Arabic section.

1. **Title block:** the project name in English and Arabic, a one-line description, and badges: CI status (the GitHub Actions workflow), PHP 8.4+, Laravel 12, Filament 3, Tailwind, License.
2. **Screenshots:**
   - Select about 6 optimized screenshots and commit them to `docs/images/` (WebP, ≤ 200 KB each): home in AR and EN, stones, a project detail, mobile views, and the admin dashboard.
   - Use demo data only. No real customer data.
3. **About:** the business context (a natural stone, marble and granite supply and installation company in Saudi Arabia) and what the platform does (presentation, catalogue, portfolio, lead generation).
4. **Features,** grouped:
   - public site
   - catalogue and filters
   - portfolio
   - leads and CRM workflow
   - notifications
   - bilingual/RTL
   - SEO
   - admin panel and roles
   - security
   - performance
5. **Tech stack** table.
6. **Architecture overview** (short), with a link to `docs/ARCHITECTURE.md`.
7. **Requirements:** PHP 8.4+ and extensions, MySQL 8 / MariaDB, Node LTS, Composer.
8. **Local installation:** step by step. Clone, `composer install`, `npm ci`, `.env` from `.env.example`, key generate, migrate, `ProductionSeeder`, optional `DemoSeeder`, `storage:link`, `app:create-admin`, `npm run dev`, serve.
9. **Environment variables** table: key, purpose, required or optional. Include mail, analytics, stock-media and queue. Placeholders only.
10. **Running tests and quality checks:** tests, Pint, Larastan, the CI description.
11. **Queue and scheduler:** what runs and when.
12. **Deployment:** a short summary with a link to `DEPLOY.md` and `docs/LAUNCH_CHECKLIST.md`.
13. **Project structure** tree (top levels only).
14. **Roles and permissions** matrix (admin vs editor).
15. **Localization:** how to add or edit translations, and the URL structure.
16. **Third-party licences:** note mPDF (GPL-2.0) and point to `docs/IMAGE_CREDITS.md`.
17. **License:** as set in §0.
18. **Author:** "Developed by **Mohamed Abdo** — Full Stack Developer", with a GitHub link to `https://github.com/m77mdabdo`.

No mention of AI tools anywhere. Verify that every command in the README actually works, by running them on a clean clone in a temp directory with SQLite.

Also update `.env.example` (placeholders only), `DEPLOY.md` and `docs/LAUNCH_CHECKLIST.md` for everything new: the notifications, the scheduler entry for the digest, and the SPF/DKIM/DMARC notes.

---

## 9. SECTION G — Final QA

- Re-run the whole of Section A. The findings list must end with every Critical and Important item fixed.
- Screenshot matrix: all public pages × 360 / 768 / 1440 × AR/EN. No horizontal scroll, no broken images, no text under 14 px.
- Lighthouse mobile (production-like) on `/ar`, `/ar/stones` and `/ar/projects`. No regression.
- axe: 0 serious or critical issues.
- CI workflow updated with Larastan; it must pass locally with `act` if available, otherwise by running the same commands locally.

---

## 10. STOP POINTS (the only reasons to stop and ask)

1. **Rewriting git history** to remove AI mentions from commit messages. Provide a plan only:
   - a backup branch/tag
   - the exact `git filter-repo` / rebase commands
   - a warning about the force-push this needs if anything was already pushed

   Do NOT execute it.
2. Anything that needs changing `.env`, DNS or hosting.
3. A non-additive migration or any destructive change.
4. A test that can't pass without weakening it.
5. Sending email to any address other than TEST_EMAIL.

If a stop point blocks one item, finish everything else first.

---

## 11. FINAL REPORT

1. The branch and its commits (hash + message).
2. The findings table summary: counts by severity, what was fixed, and what is still open.
3. The refactor summary: new structure, what moved where, and anything removed as dead code.
4. Typography: what changed, and the before/after screenshot paths.
5. Notifications: the list of emails, where the previews are, and the test-send result.
6. Repo cleanup: what moved to `.internal/`, the AI-trace hits removed, the history rewrite plan (not executed).
7. README: done, and the commands verified on a clean clone.
8. Tests before → after; Larastan level and baseline size; Lighthouse; axe.
9. Pending migrations for MySQL, in order, with a one-line impact for each.
10. Everything ASSUMED or NOT VERIFIED.
11. What the owner must do next.

Then STOP.
