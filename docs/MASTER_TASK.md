# MASTER TASK — Riyadat Al-Jawhara: finish everything possible

This is an EXISTING Laravel 11 + Filament 3 project. It is NOT new.
Baselines:
- `PROJECT_AUDIT_REPORT.md` (the original audit)
- `docs/WORK_LOG.md` (the work done since then)

The actual code is the source of truth.

## 0. HOW TO RUN THIS TASK

- Read `docs/WORK_LOG.md` first.
- If this task was already started, **resume from the last completed step**; don't redo finished work.
- Work through the phases below **in order, autonomously**. Stop **only** at the STOP POINTS listed in §2.
- If a phase is blocked by a stop point:
  - Log it in `docs/WORK_LOG.md` under "Blocked / waiting for owner".
  - Skip only the parts that depend on the stop point.
  - Continue with everything else.
- If the session is interrupted, the next session resumes by reading this file and `docs/WORK_LOG.md`.

### Branches

| Work | Branch | Created from |
|---|---|---|
| Phases 8–11 and 13 | `feature/phase-8-plus` | `fix/phase-1a-critical` |
| Phase 12 (Laravel 12) | `upgrade/laravel-12` | the final `feature/phase-8-plus` |

`feature/phase-8-plus` may already exist, for example as `feature/phase-8-locale-routing`. If so, continue on it.

### Loop for every sub-phase

1. Implement.
2. `php artisan config:clear`.
3. Run the full test suite, which must be green.
4. Run `./vendor/bin/pint --test`.
5. Run `npm run build` if the frontend changed.
6. Commit with a clear message.
7. Update `docs/WORK_LOG.md` with the commit hash, test count and notes.

Every bug fix and every new feature gets tests.

## 1. PERMANENT RULES (never break)

1. Never modify `.env`, credentials, API keys, or server/hosting config. Never print, log or commit secrets.
2. Never run these against the real MySQL database: `migrate:fresh`, `migrate:reset`, `migrate:rollback`, `db:wipe`, or `db:seed`.
3. Never delete or modify existing DB rows, except for additive media attachments in Phase 10, which must be logged.
4. Migrations must be **additive only**: new tables, new nullable or defaulted columns, and indexes. Each needs a working `down()`. Test them on SQLite only. List them for the owner to run on MySQL.
5. Always run `php artisan config:clear` before tests. Confirm that `phpunit.xml` uses SQLite `:memory:`.
6. Never push, never merge into `main`, never force anything.
7. Never delete tracked files or folders unless this task explicitly approves it. This includes `.kilo`.
8. Keep AR/EN parity and RTL. Every new user-facing string goes in BOTH `lang/ar` and `lang/en`.
9. Never invent business facts: statistics, years of experience, client names, certifications, prices, addresses or phone numbers. Anything that needs real data must be Settings-driven and hidden when empty.
10. Keep every earlier guarantee green:
    - tests and query budgets
    - CSP with zero violations
    - no horizontal scroll at 320–1920 px
    - 44 px touch targets
    - no text under 14 px on mobile
    - reduced-motion support
    - WCAG AA contrast
11. New dependencies are allowed only when necessary. Prefer the smallest well-maintained package, and justify each one in the work log. Never add runtime dependencies to the public JS bundle without a reason.

## 2. STOP POINTS (the only reasons to stop and ask)

1. Anything that needs credentials: SMTP, analytics IDs, error-monitoring DSN.
2. Real business content: company photos, contact details, legal text, testimonials, team, and real stone specifications.
3. A destructive change, or a non-additive migration.
4. A test that can't pass without weakening it.
5. Phase 12: a blocking incompatibility, such as a Filament 3 version that doesn't support Laravel 12.

Everything else in this file is **pre-approved**.

---

## PHASE 8 — Housekeeping

- Delete `public/robots.txt` so the dynamic `/robots.txt` is served. Add a test that `/robots.txt` returns 200, contains the Sitemap line, and disallows `/admin`.
- Delete EXACTLY these three files and nothing else, then report it:
  - `storage/app/public/1/g1.jpg`
  - `storage/app/public/1/conversions/g1-thumb.jpg`
  - `storage/app/public/1/conversions/g1-medium.jpg`
- Run patch-level updates only: `composer update livewire/livewire league/commonmark --with-dependencies`.
  - Report the before and after versions.
  - Stop if anything beyond a patch or minor update is pulled in.
- Make sure `SESSION_SECURE_COOKIE` is env-driven: secure in production, not forced on local http.
- `DEPLOY.md` must cover the queue worker in both setups:
  - with Supervisor
  - on shared hosting without Supervisor, using a cron-driven `queue:work --stop-when-empty` every minute
- `DEPLOY.md` must also cover `schedule:run`.
- Run `composer audit` and `npm audit --omit=dev`. Fix what can be fixed safely with patch or minor updates, and log the rest.

## PHASE 9 — URL-based localization (Arabic default)

- All public routes live under `/{locale}`, where locale is `ar` or `en`. Arabic is the default.
- Redirects:
  - `/` → 301 to `/ar`.
  - Every old un-prefixed public URL → 301 to the same path under `/ar`, with the query string preserved.
- NO automatic redirect based on `Accept-Language` or cookies for GET page requests.
- The language switcher links to the same page (same slug and query) in the other locale. The old `/lang/{locale}` route redirects to the new style.
- Slugs are shared between locales.
- On every page:
  - hreflang tags for `ar`, `en` and `x-default` (pointing to ar)
  - a canonical URL per locale
  - `og:locale` and `og:locale:alternate`
  - `<html lang dir>` correct
- The sitemap lists both locales for every URL, with `xhtml:link` alternates.
- These stay un-prefixed: `/admin`, `/up`, `/sitemap.xml`, `/robots.txt`.
- POST routes (contact and comments) must work under both prefixes, with CSRF, throttle and honeypot unchanged.
- Update every `route()` call and link helper, including emails and JSON-LD. The 404/fallback must render in the right locale.
- Tests:
  - redirect matrix (301, query preserved)
  - hreflang and canonical per page
  - switcher targets
  - sitemap alternates
  - forms under both locales
  - admin unaffected

## PHASE 10 — Visual upgrade + imagery (stone + Saudi identity, bright and joyful)

The owner explicitly requested this visual upgrade. EVOLVE the existing identity (charcoal/stone/cream, Cairo/Inter); don't replace it.

Pexels and Pixabay are approved, through the existing `fetch:stock-media` / `StockMediaFetcher` and the keys already in `.env` via `config()`.

### 10a. Design system

- **Palette:** add warm accent tokens: desert sand, gold (for lines, icons and highlights only; not large blocks) and soft terracotta. Keep AA contrast.
- **Saudi motifs:** inline SVG / CSS components only, no raster images.
  - a Najdi triangular band as section dividers and heading underlines
  - a subtle geometric pattern (≤ 5% opacity) on the cream sections
  - mashrabiya-style framing on featured cards
  - all of them must mirror correctly in RTL
- **Stone textures:** very subtle marble/granite/sandstone backgrounds on 2–3 sections at most. WebP, ≤ 120 KB each.
- **Typography:**
  - Keep Cairo for body text.
  - Add ONE self-hosted Arabic display font for headings (Reem Kufi or Tajawal), subset, with `font-display: swap`, plus a matching English display pairing.
  - Self-host Cairo and Inter as well, and remove the Google Fonts CDN. The CSP must still pass.
- **Motion:** image zoom on hover, a gold underline sweep, card lift and staggered reveals. Everything must respect reduced motion.
- Put everything in ONE shared place: Tailwind tokens plus Blade components. No copy-paste.

### 10b. Homepage and page enrichment

- **Hero:** sunlit Saudi architecture or a marble close-up. A video is optional (≤ 4 MB, 720p, keep the poster). Add a strong bilingual headline and two CTAs (quote, and WhatsApp).
- **"Why choose us":** the translation key already exists. Use icon cards. The copy must be generic and truthful; no invented numbers.
- **Stone categories:** texture tiles that link to the filtered catalogue.
- **Process timeline:** with Najdi-pattern connectors.
- **Saudi heritage-in-stone strip** (inspiration imagery, clearly labelled as inspiration): Jeddah Al-Balad coral stone, Diriyah, AlUla sandstone, Najdi architecture.
- **Stats counters:** Settings-driven only (additive columns if needed) and hidden when empty.
- **Inner pages:**
  - add a branded page-header band (texture + pattern) to Services, Stones, Projects, Blog, FAQ, About and Contact
  - apply the same card polish everywhere

### 10c. Imagery rules

- **NEVER use:**
  - the Saudi flag
  - the national emblem
  - the Vision 2030 logo
  - images of the Kaaba or the Two Holy Mosques
  - identifiable faces for testimonials or the team
  - other companies' logos or visible brand names
- **NEVER attach stock images to Projects** as if they were the company's work. Projects keep their current demo media until real photos arrive. Stock is allowed for:
  - hero
  - backgrounds
  - services
  - stone types (as material samples)
  - blog posts
  - About banner
  - slider slides
  - the heritage strip
- **Files:** download locally (no hotlinking). Max width 1920 px, WebP, ≤ 250 KB each (hero ≤ 350 KB).
- **Credits:** record every image in `docs/IMAGE_CREDITS.md`:

  | file | used where | source | photographer | original URL | license link |
  |---|---|---|---|---|---|

- **Selection process:**
  1. Fetch 4–6 candidates per slot into `storage/app/stock-candidates/` (git-ignored).
  2. Auto-pick the best one per slot using these criteria: relevance, warmth and brightness, composition space for text, resolution, and consistency across the set.
  3. Build `docs/visual-review/index.html` (self-contained). It shows every slot's candidates with IDs, marks the chosen one, and includes the moodboard (palette swatches, SVG patterns, font samples).
  4. Make swapping easy: drive the static image choices from one manifest (for example `config/site-images.php`), so the owner can swap by candidate ID later.
- **Media Library changes:** additive media rows only; never delete existing media. Log exactly which records changed in `docs/WORK_LOG.md`, so they can be reverted.
- **Screenshots:** BEFORE and AFTER, for every public page at 390 and 1440, in ar and en. Put them in the before/after section of `docs/visual-review/index.html`.

## PHASE 11 — Business enhancements

Each item below is pre-approved. Implement it only if it isn't already done (check the work log first).

1. **WhatsApp click tracking.**
   - Use a throttled beacon endpoint.
   - It records an anonymous `whatsapp_click` event with page, locale and context (service, stone or project).
   - Create a lead with `source=whatsapp` ONLY when the context is known. Otherwise record the event only.
   - Show a dashboard stat.
2. **Analytics with a consent banner.**
   - Config-driven GA4 and/or Plausible, completely off when no ID is set.
   - A bilingual cookie/consent banner that complies with PDPL: nothing loads before consent, and the choice is remembered.
   - Conversion events: lead submitted, WhatsApp click, phone click.
   - Update the CSP only for the configured provider.
3. **Thank-you page** after a lead submission (per locale), if one doesn't exist yet. It is the conversion event target.
4. **Gallery page** (`/{locale}/gallery`).
   - A filterable masonry grid across project and stone galleries, using the existing shared lightbox.
   - Add it to the nav and the sitemap.
5. **Site search** (`/{locale}/search`).
   - Searches services, stones, projects, blog posts and FAQs, in both languages.
   - Uses bindings, pagination and a search icon in the header.
   - Results pages get `noindex`.
6. **Downloadable catalogue PDF.**
   - Generated from the stone catalogue in AR (RTL) and EN.
   - Use a library with proper Arabic shaping; for example, mPDF handles Arabic better than dompdf.
   - Cache the file and bust the cache on stone changes.
   - Add a download CTA on `/stones` and on the stone detail pages.
   - Optionally, a per-stone spec sheet.
7. **Stone categories:** image and description (additive), plus category landing sections.
8. **Projects:**
   - a "similar projects" block
   - stop before/after images from appearing twice in the lightbox
   - show `client_name` only behind a per-project "show client name" toggle (additive, default off)
9. **Per-entity SEO overrides** (meta title and description, bilingual, additive) for services, stones, projects and posts, if not already done.
10. **Arabic admin panel.**
    - Filament locale switch (ar/en) per user, with the preference stored (additive).
    - Arabic labels for all resources, fields, navigation and widgets.
    - Relation labels follow the admin locale.
11. **Admin activity log.**
    - Use `spatie/laravel-activitylog`.
    - Log create/update/delete on content models, leads and settings.
    - Admin-only viewer.
12. **Privacy and data retention.**
    - A scheduled command that nulls commenter IP/user-agent, and lead IP/user-agent, older than a configurable number of days (default 180).
    - It is registered in the scheduler and documented in `DEPLOY.md`.
13. **Performance.**
    - Fragment-cache the heavy anonymous sections (home blocks, sitemap), busted by model events.
    - Do NOT full-page-cache pages that contain forms or CSRF tokens.
    - Preload the LCP image.
    - Lighthouse mobile target is ≥ 90 on all four categories for `/ar`, `/ar/stones` and `/ar/projects`. Report before and after, plus LCP and CLS.
14. **PWA basics.** Make sure `site.webmanifest` is complete, with theme colours and maskable icons. No service worker.

## PHASE 12 — Laravel 12 upgrade (separate branch `upgrade/laravel-12`)

1. First write `docs/UPGRADE_LARAVEL_12.md`:
   - the Laravel 11 security-support end date, from official sources
   - compatibility of Filament 3, Livewire and all the Spatie packages with Laravel 12
   - the breaking changes that affect THIS codebase
   - effort and risk
2. If there is no blocker, upgrade:
   - follow the official upgrade guide
   - update the constraints
   - fix the breakages
   - remove the email-validation workaround if Laravel 12 fixes it
   - remove `bootstrap/deprecations.php` only if it is no longer needed
3. The full suite, query budgets, CSP and screenshots must all still pass. Log everything.
4. If there is a blocker, STOP at this phase only. Leave `feature/phase-8-plus` fully usable.

## PHASE 13 — Final QA and launch pack

- **Screenshot matrix:** all public pages × 320 / 360 / 390 / 768 / 1024 / 1440 / 1920 × ar and en. Check for no horizontal scroll, no broken images and no text under 14 px.
- **Link checker:** every internal link and asset returns 200 in both locales. No mixed content.
- **Accessibility:** axe-core via Playwright on the key pages; zero serious or critical issues.
- **Security:**
  - `composer audit` and `npm audit`
  - headers check
  - the admin requires auth
  - the upload whitelist
  - the rate limits
- **`docs/LAUNCH_CHECKLIST.md`:** everything the owner must provide or do, with exact steps.
  - credentials: SMTP, analytics, monitoring
  - real content: photos per slot with sizes, contact details, map coordinates, legal text, testimonials with consent, team, real stone specs and statistics
  - the MySQL migration list in order
  - `app:create-admin`, `storage:link`, cron and queue
  - backups
  - Search Console submission
- Update `README.md`, `DEPLOY.md` and `.env.example` (placeholders only) for everything new.

---

## FINAL REPORT (at the end, or at a stop point)

1. Branches and commits (hash + message).
2. Per phase: what changed, files, tests added, PASS/FAIL.
3. Test count before and after; query budgets; Lighthouse before and after.
4. Pending migrations for MySQL, in order, with a one-line impact for each.
5. Media and DB records changed (to allow a revert); `IMAGE_CREDITS.md` summary; path to `docs/visual-review/index.html`.
6. New dependencies, with a justification for each.
7. Everything ASSUMED or NOT VERIFIED.
8. Blocked items and the exact inputs needed from the owner.

Then STOP.
