# Work Log — Production Readiness Roadmap

Branch: `fix/phase-1a-critical` (created from local `main` @ `fefd2b5`).
Baseline: `PROJECT_AUDIT_REPORT.md` §25. Nothing is pushed or merged from this branch.

## Standing rules
- Tests run only against SQLite `:memory:` (`phpunit.xml`), always after `php artisan config:clear`.
- No `migrate` on local MySQL. New migrations are listed under "Pending migrations" for the owner to run.
- No `.env`, credential, or hosting changes. No pushes.

## Baseline
- Test suite before any change: **5 tests, 18 assertions, all passing**.
- Read-only icon query (`Service::distinct()->pluck('icon')`) on local MySQL returned:
  `heroicon-o-truck`, `heroicon-o-wrench-screwdriver`, `heroicon-o-building-office-2`, `heroicon-o-sparkles`.

## Log

| Sub-phase | Commit | Tests (passed / assertions) | Notes |
|---|---|---|---|
| 1A — B1 reply leak, B2 user roles, B3 icon crash, L1/L2 contact spam | `c26b54e` | 23 / 98 | Allowed icons: truck, wrench-screwdriver, building-office-2, sparkles, cube. Pint fixed 3 pre-existing style issues in touched files |
| 1B — Settings admin-only, rich-text sanitizing, Post trait, env→config, seeder split, `app:create-admin` | `663fcc4` | 41 / 183 | `db:seed` now seeds roles only; demo via `--class=DemoSeeder`. Sanitizer = Filament's `sanitizeHtml()` (symfony/html-sanitizer, already installed) |
| 2 — Leads: notification, contextual CTAs, lead context columns, consent, tel/mailto, WhatsApp normalization, active-only refs, phone input | `900f96d` | 61 / 241 | Mail goes to Settings email + admin users (queued, deduped). Phone accepts Arabic-Indic digits (normalized) |
| 3a — Lead workflow: view page, notes, assignee, follow-up date, CSV export | `5be71e8` | 71 / 307 | Export is admin-only, respects table filters, CSV-injection guarded, UTF-8 BOM. Notes editable by author/admin. Dashboard: "Follow-ups Due" stat; project stat now counts active only |
| 3b — Stone attributes (finish / colour / size / application + local/imported), admin + public filters + spec block | `405658f` | 83 / 379 | One lookup table `stone_attributes` (type column) + pivot. Demo options listed in `StoneAttributeSeeder::OPTIONS` (demo links are illustrative) |
| 3c — Project metadata: sector, area m², city (AR/EN), additional stones (M:N) | `efbfc88` | 89 / 414 | `stone_type_id` kept as "primary stone"; stone filters and stone pages match primary OR additional stones |
| 3d — Settings branding (logo, light logo, default OG image), LinkedIn/X/YouTube, slider images in hero | `815d81e` | 97 / 444 | Branding via Media Library on `Setting` (JPG/PNG/WebP ≤ 2 MB). Hero: slide image if present, else poster/video (video skipped when any slide has an image). Mobile monogram logo stays static |
| 3e — Admin password reset + profile page (current password required for email/password change), one password policy | `d553d62` | 105 / 492 | Policy: min 12 chars, letters + numbers (profile, reset, Users form, `app:create-admin`). Reset emails need a real mailer in production |
| 4 — UI/UX + responsive + missing-image pass | `29f350a` | 118 / 617 | Playwright (cached npx 1.62, no new deps): 15 pages × 11 widths × EN/AR = 330 checks, 0 overflow / 0 broken img / 0 text < 14px; lightbox behaviour verified in RTL. Fixed invisible form fields (pre-existing). Desktop nav now from `xl` (AR labels don't fit at 1024). See `docs/MISSING_IMAGES.md` |
| 5a — Settings cache (no write on GET), media eager-loading, empty-language fallback | `2bbe480` | 123 / 654 | `Setting::current()` = read-only, request-scoped + cached; `Setting::editable()` for writes. `HasTranslations` trait on 13 models |
| 5b — SEO: canonical, og:url, per-entity share images, twitter title/description, article meta, BreadcrumbList, FAQPage, sitemap (lastmod + FAQ + legal), robots | `f3c4f96` | 133 / 695 | Canonical drops filter/search params, keeps `?page=N`. Blog search results `noindex, follow`. Dynamic `/robots.txt` (absolute Sitemap line) is shadowed by `public/robots.txt` — see open questions |
| 5c — Security: upload whitelist (JPG/PNG/WebP, 5 MB), model MIME rules, security headers + CSP (public + admin), secure cookies in production | `df76cdf` | 140 / 734 | CSP verified with Playwright: 0 violations on 8 public pages + admin login/leads/projects/settings; Alpine, Livewire and the Maps embed work |
| 5d — WebP responsive images (new `card` 800w / `large` 1600w conversions + srcset/sizes) | _(next commit)_ | 144 / 747 | Existing JPEG `thumb`/`medium` kept (share images + legacy media). Owner runs `media-library:regenerate --only-missing` after deploy |

## Pending migrations (owner runs on MySQL, in this order)

1. `2026_09_29_100000_add_context_columns_to_leads_table` — adds nullable `project_id` (FK → projects, null on delete), `locale`, `source_url`, `ip_address`, `user_agent`, `consented_at` to `leads`; indexes on `status` and `created_at`. Existing rows untouched. Up/down verified on SQLite.
2. `2026_09_29_110000_add_workflow_columns_to_leads_table` — adds nullable `assigned_to` (FK → users, null on delete) and `follow_up_at` (date, indexed) to `leads`.
3. `2026_09_29_110100_create_lead_notes_table` — new `lead_notes` table (lead_id cascade, user_id null on delete, body).
4. `2026_09_29_120000_create_stone_attributes_table` — new lookup table (type, name_ar/en, slug unique per type, hex_color, order, is_active).
5. `2026_09_29_120100_create_stone_attribute_stone_type_table` — new pivot (cascade on both sides).
6. `2026_09_29_120200_add_origin_type_to_stone_types_table` — nullable, indexed `origin_type` (local/imported) on `stone_types`.
7. `2026_09_29_130000_add_metadata_to_projects_table` — nullable `sector` (indexed), `area_m2`, `city_ar`, `city_en` on `projects`.
8. `2026_09_29_130100_create_project_stone_type_table` — new pivot for additional stones per project.
9. `2026_09_29_140000_add_extra_socials_to_settings_table` — nullable `linkedin_url`, `x_url`, `youtube_url` on `settings`.

## Open questions

- **robots.txt:** `public/robots.txt` was updated (Disallow `/admin`, `/lang/`) but cannot contain an absolute `Sitemap:` line without knowing the production domain. A dynamic `/robots.txt` route that adds it is implemented and tested, but the web server serves the static file first. Approve deleting `public/robots.txt` to switch to the dynamic one (or submit the sitemap in Search Console instead).

- After deploying, run `php artisan media-library:regenerate --only-missing` so existing media gets the new WebP conversions (`hero` for sliders, `card`/`large` for covers and galleries). Until then pages fall back to the JPEG/original files.

- Queue worker: lead emails are queued (`QUEUE_CONNECTION=database`), so they are only sent while `php artisan queue:work` runs. Production needs Supervisor (or switch to `sync`).
- Lead emails are rendered in the app default locale (`APP_LOCALE`, currently `en`); strings exist in both AR and EN.
- **Cleanup needed (not deleted without approval):** before the global `Storage::fake('public')` guard was added, one test run wrote 3 unreferenced files into the real media folder: `storage/app/public/1/g1.jpg`, `storage/app/public/1/conversions/g1-thumb.jpg`, `storage/app/public/1/conversions/g1-medium.jpg`. Safe to delete. The real media files in that folder were not touched.
- Side effect: one early test run wrote a single fake "New quote request" entry to the local, git-ignored `storage/logs/laravel.log` (test since changed to the array mailer).
