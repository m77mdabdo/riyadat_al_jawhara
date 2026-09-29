# Work Log — Production Readiness Roadmap

Branches:
- `fix/phase-1a-critical` (from local `main` @ `fefd2b5`): Phases 1–7.
- `feature/phase-8-plus` (from `fix/phase-1a-critical` @ `3db6e3a`): Phases 8–11 and 13 (`docs/MASTER_TASK.md`).
- `upgrade/laravel-12` (from the final `feature/phase-8-plus`): Phase 12.
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
| 5d — WebP responsive images (new `card` 800w / `large` 1600w conversions + srcset/sizes) | `7f2f363` | 144 / 747 | Existing JPEG `thumb`/`medium` kept (share images + legacy media). Owner runs `media-library:regenerate --only-missing` after deploy |
| 5e — Remove axios, `lang/ar.json` for framework strings | `53e536f` | 146 / 757 | Public JS 92.8 KB → 46.3 KB (gzip 34.1 → 16.7 KB). Re-verified after Phase 5: 330 visual checks clean; CSP 0 violations |
| 6 — Tests + CI: factories for all 17 models, test matrix (blog, public pages + RTL, every admin resource × role, command, error pages), query budgets, GitHub Actions (Pint + PHPUnit + asset build) | `7e872a3` | 228 / 1150 | Branded 403/419/429/503 pages added (comment throttle showed a raw 429). Pint fixed 5 pre-existing skeleton files so CI can enforce style repo-wide. Tests fake the public disk and skip Vite |
| Security — Laravel 11 email-rule CRLF advisory mitigation; composer.json metadata (`php ^8.4`) | `bee637f` | 230 / 1155 | Lock: content-hash + platform only, no package changes |
| 7 — Docs: README, .env.example, DEPLOY.md | `991454d` | 230 / 1155 | Docs only |
| **8 — Housekeeping** (branch `feature/phase-8-plus`) | `0db80ee` | 230 / 1156 | `public/robots.txt` deleted (dynamic route now served, test asserts no static file). Deleted exactly `storage/app/public/1/g1.jpg`, `…/conversions/g1-thumb.jpg`, `…/conversions/g1-medium.jpg` (stray test files; real media untouched). **Patch/minor updates only:** livewire/livewire v3.8.2→v3.8.10, league/commonmark 2.8.3→2.10.3, laravel/prompts v0.3.21→v0.3.24, nette/schema v1.3.5→v1.3.6, symfony/console, error-handler, http-foundation, http-kernel v7.4.14→v7.4.20, symfony/var-dumper v7.4.14→v7.4.18, symfony/event-dispatcher v8.1.1→v8.1.5, symfony/string v8.1.0→v8.1.7, symfony/service-contracts v3.7.1→v3.7.3, symfony/polyfill-intl-normalizer v1.38.0→v1.42.0. `composer audit`: only laravel/framework 11 advisories left (→ Phase 12). `npm audit --omit=dev`: 0. `SESSION_SECURE_COOKIE` already env-driven (Phase 5c, tested). DEPLOY.md: Supervisor / cron `--stop-when-empty` / sync + `schedule:run` |
| **9 — URL-based localization** (`/ar`, `/en`, Arabic default) | `7b8a53d` | 255 / 1274 | `config/locales.php` (supported + default `ar`, independent of `APP_LOCALE` which now only drives admin/CLI/mail). `/` and legacy un-prefixed URLs → 301 `/ar/...` (raw query kept). `SetLocale` only acts on prefixed URLs (admin/Livewire unaffected) and runs before route-model binding. Tests default to `/en` URLs (`useLocale()`); new `LocalizationTest` (24 cases). Browser re-check: 330 checks clean, `lang`/`dir` correct everywhere, CSP 0 violations |
| **10 — Visual upgrade + imagery** | _(next commit)_ | 268 / 1460 | **Design system:** warm tokens `sand`/`gold`/`terracotta` in `tailwind.config.js` (contrast table in comments: gold-600 for icons/lines on light, gold-400 on charcoal, terracotta-600 for text); Saudi motifs as inline-SVG Blade components (`motif.najdi-band`, `motif.geo-pattern` ≤ 5 %, `motif.mashrabiya-frame`), `section-heading`, `page-header`; gold `link-sweep`, staggered reveals, global reduced-motion. **Fonts self-hosted** via @fontsource (Cairo, Inter, Reem Kufi for AR display, Marcellus for EN display); Google Fonts CDN removed and dropped from the CSP. **Home:** curated hero + quote/WhatsApp CTAs (old grey video only if no curated photo), Why-choose-us (generic, no numbers), stone-category texture tiles → filtered catalogue, mashrabiya-framed featured projects, Najdi process connectors, Settings-driven stats (hidden when empty), heritage strip labelled as inspiration. Branded page header on Services/Stones/Projects/Blog/FAQ/About/Contact; card polish everywhere. **Imagery:** `config/site-images.php` manifest + `site-images:candidates` / `site-images:pick` / `site-images:review`; 8 slots chosen after visual vetting (auto-pick overridden for about, sandstone, Jeddah, AlUla; the Jeddah auto-pick was **the Prophet's Mosque — rejected**); **Diriyah and Najdi have no verifiable stock photo → honest pattern cards** (see Blocked). Credits: `docs/IMAGE_CREDITS.md`. Review page: `docs/visual-review/index.html` (before/after shots in git-ignored `docs/visual-review/shots/`). **Media Library: no records changed** (reasons in IMAGE_CREDITS.md). Browser: 330 checks clean, CSP 0 violations. Unused now: `components/marble-divider.blade.php` (kept; deletion not approved) |

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
10. `2026_09_30_100000_add_stats_to_settings_table` — nullable `stat_projects_completed`, `stat_years_experience`, `stat_cities_served`, `stat_clients_served` on `settings` (home counters; hidden when empty).

## Open questions (earlier phases — status)

- ~~Dependency advisories~~ → livewire and commonmark patched in Phase 8. `laravel/framework` 11 advisories remain until Phase 12 (email-rule CRLF mitigated in code).
- ~~robots.txt~~ → resolved in Phase 8.
- ~~Stray test files~~ → deleted in Phase 8.
- After deploying, run `php artisan media-library:regenerate --only-missing` so existing media gets the WebP conversions (`hero`, `card`, `large`).
- Lead emails are rendered in the app default locale (`APP_LOCALE`); strings exist in both AR and EN.

## Blocked / waiting for owner

- **Heritage photos (Phase 10):** Pexels/Pixabay have no verifiable photos of **Diriyah (At-Turaif)** or **Najdi architecture** (results were other countries, modern Riyadh, or the Saudi flag, which is forbidden). The strip shows honest pattern cards for those two until the owner provides licensed photos. Drop them in with `site-images:pick` after placing candidates, or add files `public/images/site/heritage-diriyah.webp` / `heritage-najdi.webp` (≤ 250 KB, ≤ 1920 px) and credit them.
- **Real company photography** still replaces all project/service/stone/team imagery (see `docs/MISSING_IMAGES.md`).
- **Company statistics:** the counters stay hidden until real figures are entered in Settings → Statistics.

## Stop points reached (waiting for the owner)

1. **URL-based locale routing (`/ar/...`, `/en/...`) and the default language.** A design is proposed in the final report; nothing is implemented. The site still uses the session-based switch with an `x-default` hreflang only.
2. **Credentials needed:** production SMTP (lead emails, password resets), analytics IDs, error-monitoring DSN.
3. **Real business content needed:** photos (see `docs/MISSING_IMAGES.md`), phone/WhatsApp/email/address/map pin, legally reviewed Privacy Policy and Terms, real testimonials and team (with consent), real stone specifications.
4. **No stock images were downloaded** and `fetch:stock-media` was never run against the real storage.
