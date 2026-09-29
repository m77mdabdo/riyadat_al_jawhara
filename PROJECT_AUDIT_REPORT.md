# Riyadat Al-Jawhara — Full Project Audit

**Audit date:** 2026-09-29
**Branch / HEAD:** `main` @ `d1ae783` ("Phase 11 Section 4: standalone FAQ page"), 34 commits
**Scope:** The whole working tree was inspected: every route, controller, model, migration, Filament resource, Blade view, language file, config file, seeder and test. The existing test suite was run, along with a throwaway probe suite that used an in-memory SQLite database. No project files were changed. The only file added is this report.

### How to read this report

| Marker | Meaning |
|---|---|
| **VERIFIED** | Confirmed by reading the code *and* by running it (test suite, runtime probe, or build artefact inspection) |
| **CODE** | Confirmed by reading the code; not exercised at runtime |
| **RECOMMENDED** | Not present in the code; this is a suggestion |
| **NOT VERIFIED** | Could not be confirmed without a browser, a live server, or production data |

> **Uncommitted work in progress at audit time:** `resources/views/components/layout.blade.php` and `resources/views/blog/show.blade.php` have uncommitted changes that add JSON-LD structured data (`LocalBusiness` sitewide, `Article` on blog posts), a `@stack('head')` slot and an `x-default` hreflang tag. This report describes those files **as they are on disk, including the uncommitted changes**.

---

## 1. Executive Summary

Riyadat Al-Jawhara (JRA) is a **bilingual (Arabic/English) company website with a Filament admin panel**. It runs on Laravel 11 + Filament 3. It is a marketing and lead-capture site for a natural-stone, marble and granite supply and installation business. It is **not** an e-commerce store and has no customer accounts.

**What exists and works:** a polished public site covering Home, Services, Stone collection, Projects portfolio, Blog, FAQ, About, Contact, Privacy and Terms. It has full AR/EN translation files with RTL support, a working contact/lead form that saves to the database, and a Filament admin with 14 resources plus a Settings page. Media is handled by Spatie Media Library, and there is role-based admin access (admin/editor), a sitemap, branded 404/500 pages, and a floating WhatsApp button.

**What is broken (verified at runtime):**
1. **Blog comment moderation leaks.** Pending and spam *replies* are shown publicly on blog posts.
2. **Admin user management is broken.** Creating a user with a role in `/admin/users` throws a foreign-key error.
3. **One bad admin input takes down the homepage.** An invalid service `icon` value causes a 500 error on `/`, `/services` and the service page.

**The biggest gaps for a real business launch:**
- **Leads are saved silently.** No email or notification goes to the company when a quote request arrives. There is no follow-up workflow (notes, assignee, history), no spam protection and no rate limit on the contact form.
- **"Request a Quote" buttons carry no context.** Every CTA links to a generic `/contact`, so the lead does not record which stone, service or project the visitor was viewing.
- **The product catalogue is thin.** A "stone type" has only name, origin, description and images. There are no finishes, colours, sizes/thickness, applications (interior/exterior) or availability.
- **Arabic content is effectively invisible to search engines.** The language is held in the session, not the URL, and the default locale is `en`.
- **Deployment is not production-safe yet.** `DatabaseSeeder` mixes required roles with demo data (fake testimonials, fake leads) and admin accounts with a weak, well-known default password. There is no documented way to create the first admin, and `storage:link` and queue workers are not documented.
- **Test coverage is minimal:** 5 smoke tests.

**Recommended next phase:** Phase 1 (fix critical issues), then Phase 2 (lead notification and quote context). Details are in §25.

---

## 2. Current Technology Stack

| Layer | Technology | Version (installed) | Evidence |
|---|---|---|---|
| Language | PHP | `^8.2` declared; **≥ 8.4 effectively required** | `composer.json`; `composer.lock` has `symfony/*` requiring `>=8.4.1`, `openspout` `~8.3‖8.4‖8.5`. Local CLI is PHP 8.5.2 |
| Framework | Laravel | 11.55.0 | `php artisan --version` |
| Admin | Filament | 3.3.54 | `composer show` |
| Reactive layer | Livewire | 3.8.2 | Used by Filament only; no public Livewire components |
| Media | spatie/laravel-medialibrary (+ Filament plugin) | 11.23.3 | `HasMedia` on 7 models |
| Roles | spatie/laravel-permission | 6.25.0 | `User` uses `HasRoles` |
| Slugs | spatie/laravel-sluggable | 3.8.1 | 6 models |
| Frontend CSS | Tailwind CSS 3.4 + `@tailwindcss/typography` | 3.4.19 | `tailwind.config.js` (custom `charcoal`/`stone`/`cream` palette, Inter + Cairo fonts) |
| Frontend JS | Alpine.js | 3.15.12 | `resources/js/app.js` (custom `x-reveal` directive) |
| Build | Vite 6 + laravel-vite-plugin | 6.4.3 | `vite.config.js` |
| Unused JS dep | axios | 1.18.1 | Imported in `resources/js/bootstrap.js` but never used |
| Database | MySQL (local, via XAMPP); SQLite in-memory for tests | — | `.env` `DB_CONNECTION=mysql`; `phpunit.xml` |
| Session / cache / queue | `database` driver for all three | — | `.env` |
| Mail | `log` mailer (nothing is sent) | — | `.env` `MAIL_MAILER=log` |
| Fonts | Google Fonts (Cairo, Inter), loaded from CDN | — | `components/layout.blade.php:70-73` |
| External APIs (dev only) | Pexels, Pixabay, Unsplash for stock media | — | `app/Services/StockMediaFetcher.php` |

**Environment (values not shown):** `.env` is git-ignored and has never been committed (checked with `git log --all -- .env`). It contains live third-party API keys (`PEXELS_API_KEY`, `PIXABAY_API_KEY`). Local settings are `APP_ENV=local`, `APP_DEBUG=true`, `LOG_LEVEL=debug`, `APP_LOCALE=en`, `APP_URL=http://127.0.0.1:8000`. `.env.example` is still the Laravel default (it lists `DB_CONNECTION=sqlite` and has no keys for the stock-media APIs).

---

## 3. Application Architecture

A standard, thin MVC Laravel app. There is no service/repository layer apart from one dev utility service.

```
routes/web.php ──► 11 public controllers (app/Http/Controllers) ──► Blade views (resources/views)
                          │                                             │
                          ▼                                             ▼
                   15 Eloquent models ◄── Filament panel /admin ── components/layout + partials
                   (bilingual *_ar / *_en columns + accessor per field)
```

| Area | What exists | Location |
|---|---|---|
| Public controllers | About, Blog, Contact, Faq, Home, Legal, Locale, Project, Service, Sitemap, Stone | `app/Http/Controllers/` |
| Form Requests | `StoreLeadRequest`, `StoreCommentRequest` | `app/Http/Requests/` |
| Middleware | `SetLocale` (reads `session('locale')`) appended to the `web` group | `app/Http/Middleware/SetLocale.php`, `bootstrap/app.php:17-19` |
| Models | BlogCategory, Comment, Faq, Lead, Post, Project, Service, Setting, Slider, StoneCategory, StoneType, Tag, TeamMember, Testimonial, User | `app/Models/` |
| Shared model trait | `HasActiveScope` (`active()`, `ordered()`) | `app/Models/Concerns/HasActiveScope.php` |
| Admin | 14 Filament resources, 1 custom page (Settings), 3 widgets, 1 trait (`RestrictsDeletionToAdmins`) | `app/Filament/` |
| Services | `StockMediaFetcher` (downloads stock photos/videos; dev/seed use only) | `app/Services/StockMediaFetcher.php` |
| Console | `fetch:stock-media` command | `app/Console/Commands/FetchStockMedia.php` |
| Bootstrap hack | Global `E_DEPRECATED` handler that silences one PHP 8.4+ PDO deprecation | `bootstrap/deprecations.php` |

**Not present:** jobs, events, listeners, notifications, mailables, policies, observers, API routes (`routes/api.php` does not exist), scheduled tasks (`routes/console.php` only has the default `inspire`), repositories, custom exception handling (`withExceptions` is empty), and view composers.

**Bilingual data pattern:** every translatable field is stored twice (`title_ar`, `title_en`), and each model has a hand-written accessor such as `getTitleAttribute()` that picks one by `app()->getLocale()`. The pattern is consistent across all models, but it is repeated by hand roughly 25 times.

---

## 4. Current Features

### EXISTS NOW (verified in code)

| Business capability | Implementation |
|---|---|
| Company services showcase | `Service` model + `/services`, `/services/{slug}` |
| Stone / material catalogue (categories → types) | `StoneCategory` → `StoneType`; `/stones`, `/stones/{slug}` with category filter and gallery |
| Project portfolio with gallery and before/after slider | `Project` (cover, before, after, gallery collections); `/projects` with service/stone filters and pagination |
| Lead capture (contact / quote form) | `/contact` POST → `leads` table → Filament `LeadResource` with status pipeline `new → contacted → in_progress → closed` |
| Blog with categories, tags, search, view counter, threaded comments with moderation | `Post`, `BlogCategory`, `Tag`, `Comment`; `/blog`, `/blog/{slug}`; `CommentResource` with approve/spam actions |
| FAQ | `Faq` model; homepage block (top 4) + `/faq` |
| About (about/vision/mission from settings, team members) | `Setting`, `TeamMember`; `/about` |
| Testimonials | `Testimonial` (optionally linked to a project); homepage only |
| Homepage hero slider | `Slider` (text + button only; **uploaded slider image is never displayed**, see §20) |
| Site-wide settings (contact, address, hours, map, social, legal text) | `settings` table (single row) + Filament `Settings` page |
| Legal pages | `/privacy-policy`, `/terms-of-use` (rich text from settings) |
| WhatsApp contact | Floating button (all pages) + footer icon + contact page button; `wa.me` link with a localized pre-filled message |
| Google Map + "Get Directions" | `contact.blade.php:108-127` (only when lat/lng are set) |
| Arabic/English with RTL | `lang/{ar,en}/site.php` (129 keys each, full parity), `dir="rtl"`, Cairo font |
| SEO basics | Per-page title/description, OG and Twitter tags, `/sitemap.xml`, `robots.txt`, JSON-LD (uncommitted) |
| Admin dashboard | Lead stats, 6-month leads chart, pending-comments stat |
| Role-based admin | `admin` / `editor` roles; only admins can delete records and manage users |

### RECOMMENDED / MISSING (not in code)

These are **not implemented**: product specifications (finish, colour, thickness, dimensions, application area, price range, availability), a dedicated quote request with project details and attachments, product- or project-specific inquiry, lead notifications, CRM follow-up (notes, assignee, reminders), customer accounts, a careers page, a standalone gallery, site-wide search, analytics tracking, downloadable catalogue/brochure, and an admin activity log.

---

## 5. Public Website

### 5.1 Page inventory

All pages extend `components/layout.blade.php` (header, footer, WhatsApp float, back-to-top) except the error pages, which use `components/error-page.blade.php`.

| Route | Purpose | Controller@method | View | DB dependency | Working? | AR | EN | Issues |
|---|---|---|---|---|---|---|---|---|
| `GET /` | Home: hero, services, featured projects, stones, process, testimonials, FAQ, CTA | `HomeController@index` | `home.blade.php` | sliders, services, projects, stone_types, testimonials, faqs, media, settings | **VERIFIED** 200 | ✓ | ✓ | 26 queries (16 media N+1); slider image unused; any invalid `service.icon` → 500 |
| `GET /services` | Service list | `ServiceController@index` | `services/index` | services, media | **VERIFIED** 200 | ✓ | ✓ | Media N+1; invalid icon → 500 |
| `GET /services/{slug}` | Service detail + 6 related projects | `ServiceController@show` | `services/show` | services, projects, media | **VERIFIED** 200 | ✓ | ✓ | CTA doesn't pass the service to the contact form; inactive → 404 (correct) |
| `GET /stones` | Stone catalogue with category chips and interactive preview | `StoneController@index` | `stones/index` | stone_types, stone_categories, media | **VERIFIED** 200 | ✓ | ✓ | Not paginated (loads all); preview shows a broken `<img>` when a stone has no cover |
| `GET /stones/{slug}` | Stone detail, gallery lightbox, related projects | `StoneController@show` | `stones/show` | stone_types, media, projects | **VERIFIED** 200 / 404 for unknown | ✓ | ✓ | CTA has no stone context; `getMedia('gallery')` called 4×, cached per model |
| `GET /projects` | Portfolio grid, filters, 9/page | `ProjectController@index` | `projects/index` | projects, services, stone_types, media | **VERIFIED** 200 | ✓ | ✓ | Media N+1; `client_name` never shown publicly |
| `GET /projects/{slug}` | Project detail, before/after slider, gallery lightbox | `ProjectController@show` | `projects/show` | projects, media | **VERIFIED** 200 | ✓ | ✓ | Before/After labels use physical `left`/`right` (not mirrored in RTL); CTA has no project context |
| `GET /blog` | Posts, search, category and tag filters, 9/page | `BlogController@index` | `blog/index` | posts, blog_categories, tags, media | **VERIFIED** 200 | ✓ | ✓ | Media N+1; cover `alt=""` |
| `GET /blog/{slug}` | Post, tags, related posts, threaded comments | `BlogController@show` | `blog/show` | posts, comments, users, tags, media | ✓ 200 | ✓ | ✓ | **BROKEN: pending/spam replies are shown publicly** (§20) |
| `POST /blog/{slug}/comments` | Submit comment/reply | `BlogController@storeComment` | — | comments | ✓ | ✓ | ✓ | Throttled 5/min; honeypot. Does not check `published_at` (future-dated posts accept comments) |
| `GET /faq` | FAQ accordion | `FaqController@index` | `faq/index` | faqs | ✓ | ✓ | ✓ | Not in the header nav (footer only); no FAQPage schema |
| `GET /about` | About, vision, mission, team | `AboutController@index` | `about` | settings, team_members, media | **VERIFIED** 200 | ✓ | ✓ | `settings` queried 4× per request |
| `GET /contact` | Lead form, contact info, map, directions | `ContactController@index` | `contact` | settings, services, stone_types | **VERIFIED** 200 | ✓ | ✓ | See §11 |
| `POST /contact` | Store lead | `ContactController@store` | — | leads | **VERIFIED** | ✓ | ✓ | **No rate limit, no honeypot, no notification** |
| `GET /privacy-policy` | Legal | `LegalController@privacyPolicy` | `legal/privacy-policy` | settings | ✓ | ✓ | ✓ | Rendered with unescaped `{!! !!}` (§16) |
| `GET /terms-of-use` | Legal | `LegalController@termsOfUse` | `legal/terms-of-use` | settings | ✓ | ✓ | ✓ | Same |
| `GET /lang/{locale}` | Switch locale (session) | `LocaleController@switch` | — | session | **VERIFIED** (302; unknown locale ignored) | — | — | Not SEO-friendly (§14) |
| `GET /sitemap.xml` | XML sitemap | `SitemapController@index` | `sitemap` | services, stone_types, projects, posts | **VERIFIED** 200 | — | — | Missing `/faq`, legal pages and `lastmod`; one URL per page only (no language variants) |
| `GET /up` | Health check | framework | — | — | ✓ | — | — | — |
| fallback | 404 through the `web` group (so the locale applies) | closure | `errors/404` | — | **VERIFIED** 404 | ✓ | ✓ | No custom 403/419/429/503 pages |

**Pages that do not exist:** product detail (separate from stone type), dedicated quote page, careers, gallery, site search, news. All are RECOMMENDED, not required.

### 5.2 Shared UI

| Element | File | Status | Notes |
|---|---|---|---|
| Header / desktop nav | `partials/header.blade.php` | Complete | 7 links + AR/EN switch + "Request Quote". FAQ is missing from the nav |
| Mobile menu | same, lines 40-59 | Works (Alpine) | Toggle button has no `aria-expanded`/`aria-controls`; no focus trap; the mobile active state ignores child routes (desktop uses `routeIs($r.'.*')`) |
| Footer | `partials/footer.blade.php` | Complete | Its WhatsApp link uses the raw `settings.whatsapp` value (not digit-stripped), while the floating button strips non-digits (`whatsapp-float.blade.php:41`). The footer link breaks if an admin types `+966 …` |
| WhatsApp float | `components/whatsapp-float.blade.php` | Complete | Pinned with an inline `right:` style in both LTR and RTL (intentional). The tooltip shows once per session |
| Back-to-top | `layout.blade.php:97-109` | Complete | — |
| Buttons | `components/button.blade.php` | Complete | 2 sizes × 2 variants |
| Image placeholder | `components/image-placeholder.blade.php` | Complete | Hard-coded "JRA" text |
| Loading state | Contact form only (`submitting` spinner) | Partial | The comment form has no submitting state or double-submit guard |
| Empty states | services, stones, projects, blog, faq, comments | Complete | Home sections are hidden when empty |
| Error states | Contact: field-level errors. Comments: summary list | Complete | No inline errors on comment fields |
| Animations | `x-reveal` IntersectionObserver; respects `prefers-reduced-motion` | Complete | The hero slider `setInterval` ignores reduced-motion and runs even with one slide |
| Favicon / touch icons / theme-color | `layout.blade.php:63-68` | Complete | `site.webmanifest` is not present, although `maskable-icon-512x512.png` and `android-chrome-*` exist |

---

## 6. Admin Panel

Filament 3 panel at `/admin`, configured in `app/Providers/Filament/AdminPanelProvider.php`. Brand name "JRA Admin", primary colour `#2B2B2B`, login page enabled. **No** password reset, profile page, email verification, MFA, database notifications, global search configuration or logo.

**Access:** `User::canAccessPanel()` returns true only for the `admin` or `editor` role (`app/Models/User.php:52-55`). **VERIFIED:** a user with no role gets **403** on `/admin`.

### 6.1 Module matrix

Legend: ✓ present · ✗ absent · A = restricted to the admin role (editors cannot do it)

| Module (nav group) | List | Create | Edit | Delete | View page | Search | Filters | Pagination | Validation | Uploads | Status mgmt | Relations |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| **Leads** (Leads) | ✓ | ✓ (manual) | ✓ | A | ✗ | name, phone | status, date range | ✓ (Filament default) | required name/phone, max lengths | — | ✓ inline `SelectColumn`; nav badge = new count | service, stone type (EN names only) |
| **Projects** (Content) | ✓ | ✓ | ✓ | A | ✗ | title EN/AR | service, featured | ✓ | required titles and slug, unique slug | cover, before, after, gallery (reorderable) | active, featured, order | service, stone type |
| **Services** (Content) | ✓ | ✓ | ✓ | A | ✗ | name EN/AR | ✗ | ✓ | required, unique slug | cover | active, order | — |
| **Stone Categories** | ✓ | ✓ | ✓ | A | ✗ | name | ✗ | ✓ | required, unique slug | ✗ | order only (no `is_active`) | stone types count |
| **Stone Types** | ✓ | ✓ | ✓ | A | ✗ | name | category | ✓ | category required, unique slug | cover, gallery | active, order | category |
| **Sliders** | ✓ | ✓ | ✓ | A | ✗ | title | ✗ | ✓ | titles required | image (**not rendered on the site**) | active, order | — |
| **Testimonials** | ✓ | ✓ | ✓ | A | ✗ | client | ✗ | ✓ | name, rating, both comments required | client photo | active, order | project |
| **Team Members** | ✓ | ✓ | ✓ | A | ✗ | name | ✗ | ✓ | names required | photo | active, order | — |
| **FAQs** | ✓ | ✓ | ✓ | A | ✗ | question | ✗ | ✓ | all four fields required | — | active, order | — |
| **Posts** (Content) | ✓ | ✓ | ✓ | A | ✗ | title | published, category | ✓ | titles and slug required | cover | published + `published_at` | category, tags (M:N), author |
| **Blog Categories** | ✓ | ✓ | ✓ | A | ✗ | name | ✗ | ✓ | required, unique slug | — | order | posts count |
| **Tags** | ✓ | ✓ | ✓ | A | ✗ | name | ✗ | ✓ | required, unique slug | — | — | posts count |
| **Comments** (Content) | ✓ | ✗ (by design) | ✓ | A | ✗ | post, name, email, body | status, date range | ✓ | required | — | Approve / Mark-as-spam actions; nav badge | post, parent |
| **Users** (Administration) | ✓ A | **✗ BROKEN** | **✗ BROKEN** | A | ✗ | name, email | ✗ | ✓ | unique email, password on create | — | — | roles |
| **Settings** (custom page) | — | — | ✓ | — | — | — | — | — | email/url/numeric only | ✗ (no logo/OG upload) | — | — |
| **Dashboard widgets** | LeadsOverview, LeadsChart, PendingCommentsWidget, Filament Account + FilamentInfo | | | | | | | | | | | |

### 6.2 Admin findings

| # | Finding | Evidence | Status |
|---|---|---|---|
| A1 | **Creating or editing a user with a role fails.** `Select::make('roles')->relationship('roles','name')->options(['admin'=>…,'editor'=>…])` sends role *names* to `BelongsToMany::sync()`, which expects IDs. The result is an FK violation. On edit, the loaded state (role IDs) does not match the option keys, so the field appears empty | `app/Filament/Resources/UserResource.php:41-48`; probe: `QueryException … FOREIGN KEY constraint failed … insert into "model_has_roles"` | **VERIFIED BROKEN** |
| A2 | Editors can open and edit **Settings**, including raw-HTML legal content and every public contact detail | `app/Filament/Pages/Settings.php` has no `canAccess()`; probe: editor → `/admin/settings` = **200** | **VERIFIED** |
| A3 | Editors can edit and re-status leads (only deleting is restricted). This may be intended; worth confirming with the business | probe: editor `/admin/leads` = 200 | **VERIFIED** |
| A4 | No granular permissions. Spatie permission tables exist, but no permissions are defined or used; authorization is hard-coded role checks | `grep` shows no `can(`, `Gate`, `Policy` or `permission` usage | CODE |
| A5 | Deleting a Stone Category **cascade-deletes all its stone types** (FK `cascadeOnDelete`) and silently unlinks their projects and leads (`nullOnDelete`). No warning is shown | `…171724_create_stone_types_table.php:16` | CODE |
| A6 | Leads have no view/detail page, no internal notes, no assignee, no follow-up date, no history, no export, no email or stone-type column in the table, and no message preview | `LeadResource.php` | CODE |
| A7 | `LeadsOverview` labels `Project::count()` as "Published portfolio items", but the count includes inactive projects | `app/Filament/Widgets/LeadsOverview.php:21-23` | CODE |
| A8 | The admin UI is English-only; relation labels always use `name_en`/`title_en` | All resources | CODE |
| A9 | `FilamentInfoWidget` (Filament version/docs links) is shown on the production dashboard | `AdminPanelProvider.php:42` | CODE |
| A10 | Uploads use only `->image()` (`image/*`). There is no explicit `maxSize` (Livewire temp limit 12 MB, media-library 10 MB) and no MIME whitelist. `image/*` accepts SVG | All `SpatieMediaLibraryFileUpload` calls; `vendor/filament/forms/src/Components/FileUpload.php:119-123` | CODE |
| A11 | The Service `icon` field is free text but is rendered as `<x-dynamic-component>`. One typo causes a 500 on 3 public pages | `ServiceResource.php:62`; `home.blade.php:64`; probe: `ViewException Unable to locate a class or view for component` | **VERIFIED** |
| A12 | Filament Create/Edit pages are default stubs; there are no `mutateFormDataBeforeSave` hooks or custom logic | `app/Filament/Resources/*/Pages/*` | CODE |

---

## 7. Database

**18 migrations** (Laravel defaults, media, permission, 12 domain tables, 2 alters). All migrations run cleanly on SQLite (**VERIFIED** by the test suite). `storage/logs/laravel.log` shows an earlier MySQL `errno: 150` FK failure while creating `projects`; the current migration ordering (services and stone_types before projects) resolves it.

### 7.1 Table inventory

| Table | Purpose | Key columns | Relationships / FKs | Indexes | Notes / issues |
|---|---|---|---|---|---|
| `users` | Admin/editor accounts | name, email (unique), password | roles via `model_has_roles` | email unique | — |
| `settings` | Single-row site config | phone, whatsapp, email, address_ar/en, 4 social URLs, working_hours_ar/en, about/vision/mission ar/en, map_lat/lng, privacy_policy_ar/en, terms_ar/en | — | — | Accessed via `Setting::current()` = `firstOrCreate([])`: an **unconditional write path on GET requests** if the table is empty; queried 3–4× per page. No logo, SEO defaults or LinkedIn/X/YouTube fields |
| `sliders` | Hero slides | title/subtitle/button_text ar/en, button_link, order, is_active | media `image` | — | The media image is never rendered |
| `services` | Company services | name ar/en, slug (unique), icon, description ar/en, is_active, order | hasMany projects, leads | slug | No index on `is_active`/`order` (fine at this size) |
| `stone_categories` | Marble / Granite / Quartz / Natural Stone | name ar/en, slug, order | hasMany stone_types | slug | No `is_active` column, so an empty category still shows as a filter chip |
| `stone_types` | The "product" | stone_category_id, name ar/en, slug, origin ar/en, description ar/en, is_active, order | FK → stone_categories **cascade delete** | slug | **No product attributes** (finish, colour, thickness, sizes, application, price, availability) |
| `projects` | Portfolio | title/description/location ar/en, slug, client_name, service_id, stone_type_id, completed_at, is_featured, is_active, order | FK → services, stone_types (null on delete) | slug | Single service and single stone type only (real projects often use several materials); no area, sector (residential/commercial) or city fields |
| `testimonials` | Reviews | client_name, rating, comment ar/en, project_id | FK → projects (null on delete) | — | `rating` has no DB check (the form restricts it to 1–5) |
| `team_members` | About page | name/position ar/en, phone, email | — | — | phone/email stored but never displayed |
| `faqs` | FAQ | question/answer ar/en | — | — | `question_*` is `string(255)`, which may be short for long questions |
| `leads` | Inquiries | name, phone, email, service_id, stone_type_id, city, message, status enum(new, contacted, in_progress, closed), source enum(website, whatsapp) | FK → services, stone_types (null on delete) | — | **No index on `status`/`created_at`** (queried by the nav badge, widget and chart on every admin page). `message` is nullable in the DB but required by the form. No project_id, notes, assignee, locale, IP/UA, UTM or page-of-origin fields. `whatsapp` source is never produced by code |
| `posts` | Blog | title/excerpt/body ar/en, slug, is_published, published_at, author_id, blog_category_id, views_count | FK → users, blog_categories (null on delete) | slug | No index on `(is_published, published_at)`; no SEO meta columns |
| `blog_categories`, `tags`, `post_tag` | Blog taxonomy | name ar/en, slug | M:N with composite PK | slug | — |
| `comments` | Blog comments | post_id, parent_id, name, email, body, status enum(pending, approved, spam), ip_address, user_agent | FK → posts cascade, self-FK cascade | `(post_id, status)` | Good |
| `media` | Spatie media | morphs, collection_name, … | polymorphic | morph index, uuid | ~535 files / 49 MB in `storage/app/public`. Some media folders hold several copies of the same file (e.g. `storage/app/public/16/`, 3 copies), which suggests **orphaned files** from repeated seeding (NOT VERIFIED against DB rows) |
| permission tables, `sessions`, `cache`, `jobs`, `failed_jobs`, `password_reset_tokens` | Framework | — | — | — | `permissions` table is always empty; `jobs` is only used by queued media conversions |

### 7.2 Schema ↔ Model ↔ Admin ↔ View mismatches

| Mismatch | Where |
|---|---|
| `Post` uses `HasActiveScope`, but `posts` has no `is_active` or `order` column. Calling `Post::active()` or `Post::ordered()` would throw | `app/Models/Post.php:19` |
| `Slider` media collection `image` is managed in admin and seeded, but no view reads it; the hero always shows the static `images/hero-poster.webp` / `hero-background.mp4` | `home.blade.php:8-16` |
| `projects.client_name` is managed in admin; the `site.projects.client` translation exists but is never shown | `projects/show.blade.php` |
| `team_members.phone/email` are managed but never displayed | `about.blade.php` |
| `leads.message` is nullable in the DB but required in `StoreLeadRequest` | migration vs request |
| `leads.source = whatsapp` exists but nothing records WhatsApp clicks | — |
| Unused translation keys suggest planned sections that were never built: `home.why_choose_us`, `common.call_us`, `common.local`, `common.imported`, `stones.category`, `projects.client`, `blog.published_on`, `blog.tags`, `contact.working_hours`, `common.submit`, `common.back` | `lang/*/site.php` |

---

## 8. Authentication & Permissions

| Item | Status | Evidence |
|---|---|---|
| Public users / registration | **Not implemented, and not needed** for this site | No auth routes outside Filament |
| Admin login | ✓ Filament `->login()` with session auth, CSRF, `AuthenticateSession` | `AdminPanelProvider.php:29,44-57` |
| Login rate limiting | ✓ Filament's built-in login throttle (5 attempts) | Filament default (CODE) |
| Password reset | ✗ Not enabled (`->passwordReset()` absent) | — |
| Email verification | ✗ | `User` does not implement `MustVerifyEmail` |
| Profile / change own password | ✗ (`->profile()` absent) | — |
| MFA | ✗ | — |
| Roles | `admin`, `editor` (created by `RoleSeeder`) | `database/seeders/RoleSeeder.php` |
| Permissions | Tables exist; **none defined or used** | — |
| Policies | None | — |
| Panel gate | `canAccessPanel()` → admin or editor | **VERIFIED** (no role → 403) |
| Users module | Admin only (`canViewAny` etc.) | **VERIFIED** (editor → 403 on `/admin/users`) |
| Delete restriction | `RestrictsDeletionToAdmins` on 12 resources | CODE |
| Settings restriction | ✗ **None**; editors have full access | **VERIFIED** |
| First-admin bootstrap | Only via `RoleSeeder`, which creates `admin@…` and `editor@…` accounts with a **hard-coded, well-known weak password**. `php artisan make:filament-user` creates a user *without* a role, who then gets 403. The Users screen cannot assign roles (A1) | CODE + VERIFIED |

**Conclusion:** normal (non-role) users cannot reach admin features (**VERIFIED**). The real risks are (a) seeded default credentials reaching production, (b) editors being able to change settings and inject raw HTML, and (c) broken user management leaving no safe way to onboard staff.

---

## 9. Products & Catalogue

**EXISTS NOW:** a two-level catalogue, `StoneCategory` (4 seeded: Marble, Granite, Quartz, Natural Stone) → `StoneType` (seeded examples: Carrara, Tuscany, Black Granite, Engineered White Quartz, Hashemi). Each stone type has a bilingual name, origin, description, a cover image and a gallery. The public list at `/stones` has category chips and an interactive preview panel. The detail page has a gallery lightbox, related projects and a "Request Quote" CTA.

**Gaps (RECOMMENDED):**

| Gap | Why it matters for a stone business |
|---|---|
| No finishes (polished, honed, brushed, flamed, sandblasted) | Core buying parameter |
| No colours / tones, and no colour filter | Primary browse criterion for marble/granite |
| No thickness / slab or tile sizes | Needed for quoting |
| No application (flooring, cladding, countertops, stairs, facade, interior/exterior) | Matches the business's interior/exterior work |
| No price range, availability or stock status (local vs imported) | `common.local` / `common.imported` keys exist but are unused, which suggests this was planned |
| No technical sheet or downloadable PDF | Common B2B requirement |
| No pagination or search on `/stones` | Fine now; won't scale |
| CTA does not pre-fill the stone on the contact form | Lead loses context (§11) |
| Stone categories have no image or description | Category landing pages are not possible |

**Status: PARTIAL.** It works as a showcase but is not a product catalogue yet.

---

## 10. Projects / Portfolio

**EXISTS NOW (MOSTLY COMPLETE):** a filterable (service, stone type), paginated grid; a featured flag used on the homepage; a detail page with cover, meta (location, service, stone type, completion date), a keyboard-accessible before/after comparison slider (ARIA `role="slider"`), and a gallery lightbox with keyboard and swipe navigation. Admin has full CRUD with 4 media collections and reorderable gallery images.

**Issues:**
- Media N+1 on the list (6 media queries for 6 cards in the probe).
- The gallery lightbox merges `gallery` + `before` + `after`, so before/after images appear twice on the page.
- No project sector (residential/commercial/government), area (m²), city filter or multiple materials. Only one `service_id` and one `stone_type_id`.
- `client_name` is never displayed.
- Before/After corner labels use physical `left-4`/`right-4` and the clip direction is LTR. This is acceptable, because it matches the images, but it is not mirrored in RTL.
- No "similar projects" block on the project page.

---

## 11. Leads & Forms

### 11.1 Form inventory

| Form / CTA | Location | Submits to | Stored? | Notified? | In admin? |
|---|---|---|---|---|---|
| Contact / Request Quote form | `/contact` | `POST /contact` | ✓ `leads` | ✗ | ✓ LeadResource |
| "Request Quote" buttons (header, home CTA, service, stone, project, FAQ pages) | many | Plain link to `/contact` with **no parameters** | — | — | — |
| Floating WhatsApp button | all pages | `wa.me/<number>?text=<generic message>` | ✗ | — | ✗ (not tracked) |
| Footer / contact WhatsApp link | footer, contact | `wa.me/<raw setting>` | ✗ | — | ✗ |
| Phone / email | footer, contact | **Plain text, not `tel:`/`mailto:` links** | — | — | — |
| Blog comment form | `/blog/{slug}` | `POST /blog/{slug}/comments` | ✓ `comments` | ✗ | ✓ CommentResource |

### 11.2 Contact/quote flow, traced end to end

```
contact.blade.php (form, @csrf, Alpine submitting state)
  → StoreLeadRequest            ✓ name, phone, message required; email format; service/stone exists
  → ContactController@store     ✓ Lead::create(validated + status=new, source=website)
  → leads table                 ✓ VERIFIED by existing test + probe
  → Notification / email        ✗ STOPS HERE — nothing is sent (no Mail/Notification classes exist)
  → Admin panel                 ✓ appears in LeadResource with "new" badge + dashboard stat
  → Status / follow-up          ◑ status select only; no notes, owner, reminders, history, export
```

**The flow stops at notification.** The company only learns about a lead if someone opens the admin panel.

### 11.3 Form defects

| # | Issue | Evidence |
|---|---|---|
| L1 | **No rate limit** on `POST /contact`. The probe sent 12 submissions in a row: all accepted, 12 leads created | `routes/web.php:38`; probe `[throttle] … 302 leads=12` |
| L2 | **No honeypot or CAPTCHA** on the contact form (the comment form has one) | `contact.blade.php` |
| L3 | No context passed from CTAs, so a lead never records the stone, service or project viewed | All `x-button :href="route('contact')"` |
| L4 | Phone has no format validation (`string|max:30`); the input is `type="text"`, not `tel`, with no `inputmode`/`autocomplete` | `StoreLeadRequest.php:21`; `contact.blade.php:29` |
| L5 | No HTML `required` attributes, so all validation is server-side only | `contact.blade.php` |
| L6 | No privacy-consent checkbox or link, although a Privacy Policy page exists (relevant to Saudi PDPL) | — |
| L7 | No attachments (drawings, site photos), dimensions, area (m²), budget or timeline fields: the typical inputs for a stone quote | — |
| L8 | `service_id`/`stone_type_id` validate with `exists` but accept **inactive** records | `StoreLeadRequest.php:24-25` |
| L9 | Lead does not store locale, page of origin, IP/user-agent or UTM data | `leads` schema |
| L10 | The success message is shown via `back()`, so the form re-renders; there is no dedicated thank-you page for conversion tracking | `ContactController.php:28` |

---

## 12. Emails & Notifications

| Item | Present? |
|---|---|
| `app/Mail/*` Mailables | **None** |
| `app/Notifications/*` | **None** (`User` has the `Notifiable` trait but it is unused) |
| Events / listeners | **None** |
| Filament database notifications | Not enabled |
| Queued jobs | None written by the app. Media conversions are `->nonQueued()` on all models, although `queue_conversions_by_default=true` |
| Mail config | `MAIL_MAILER=log`; SMTP keys present in `.env` but unused |
| Scheduled tasks | None |

**Status: NOT IMPLEMENTED.** No email or notification functionality exists anywhere, so nothing is written but untriggered. Password-reset emails are also impossible, because password reset is not enabled.

---

## 13. Localization

| Item | Status | Evidence |
|---|---|---|
| Languages | AR + EN | `lang/ar`, `lang/en` (auth, pagination, passwords, validation, site) |
| Translation key parity | **Complete**: 129 keys in both `site.php` files; every `__('site.*')` key used in views and app code is defined | **VERIFIED** by script |
| Validation messages | Translated, with Arabic `attributes` for the lead fields | `lang/ar/validation.php:161-169` |
| Switching | `/lang/{ar|en}` stores the locale in the session and redirects back | `LocaleController`, `SetLocale` |
| Default locale | **`en`** (`APP_LOCALE=en`). For a Saudi audience, Arabic is the likely correct default (business decision) | `.env`, `config/app.php:81` |
| RTL | `dir="rtl"`, logical properties (`start`/`end`/`ps`), `rtl:` variants, `rtl:rotate-180` arrows, `<bdi>` for numbers | Views |
| Fonts | Cairo (AR), Inter (EN) | `tailwind.config.js` |
| DB content | Every content model has `_ar`/`_en` columns, and admin forms have EN/AR tabs. **No fallback** when one language is empty (for example, an empty `description_ar` shows blank in Arabic) | Models |
| Hard-coded strings | Minimal: honeypot label "Website", social `aria-label`s (brand names), "JRA" in the placeholder | `components/blog/comment-form.blade.php:18` |
| Pagination text | **Partially untranslated.** Laravel's Tailwind pagination view uses JSON keys (`__('Showing')`, `__('to')`, `__('of')`, `__('results')`), and there is no `lang/ar.json`, so these words stay English in Arabic mode | CODE (NOT VERIFIED visually) |
| Dates | Hard-coded `format('Y-m-d')`; `diffForHumans()` follows the app locale | Views |
| Admin panel | English only | Filament resources |
| Localized SEO | **Incomplete.** Session-based locale means one URL per page. Crawlers carry no session, so they only ever see the default (`en`) version. The uncommitted layout comment correctly explains why only `x-default` hreflang is emitted | `layout.blade.php:48-59` |
| `og:locale` | Set per locale; no `og:locale:alternate` | `layout.blade.php:40` |

**Status: MOSTLY COMPLETE** for the UI; **PARTIAL** for SEO and the admin.

---

## 14. SEO

| Item | Status | Notes |
|---|---|---|
| `<title>` | ✓ per page via `x-layout :title` | Home falls back to "Site name \| tagline" |
| Meta description | ✓ | List pages (`/services`, `/stones`, `/projects`) pass no description, so they fall back to the global default |
| Open Graph / Twitter | ◑ | Static `og:image` on every page (entity cover images are not used); `og:type` always `website`; **no `og:url`**, no `twitter:title/description` |
| Canonical | ✗ | Missing on every page. Filter and paginated URLs (`?service=`, `?page=`, `?q=`) produce duplicates |
| hreflang | ◑ | `x-default` only (uncommitted); real AR/EN alternates need URL-based locales |
| Sitemap | ◑ | Dynamic, includes active entities. Missing `/faq`, `/privacy-policy`, `/terms-of-use`; no `<lastmod>`; `Content-Type: text/xml` |
| robots.txt | ◑ | `Disallow:` (allow all). Does not reference the sitemap and does not disallow `/admin` or `/lang/*` |
| Structured data | ◑ (uncommitted) | `LocalBusiness` sitewide (with hard-coded `addressCountry: SA`) and `Article` on posts. Missing: `BreadcrumbList`, `FAQPage`, `Organization` logo, `Service`, `Product` |
| Heading hierarchy | ✓ | One `<h1>` per page; card titles use h2/h3 appropriately |
| Image alt text | ◑ | Mostly the entity name; blog cards use `alt=""`; the About hero alt is the page title |
| Slugs | ✓ | Generated from English only; Arabic-language slugs are not supported |
| Per-entity SEO fields | ✗ | No meta title/description overrides in admin |
| Analytics / Search Console verification | ✗ | None |

**Status: PARTIAL.**

---

## 15. UI/UX & Responsive

These are code-level observations. No browser rendering was done in this audit (**NOT VERIFIED** visually). Git history shows earlier responsive and accessibility sweeps (`7b44f2b`, `fc88540`, `81b706f`).

**Strengths (CODE):** a consistent design system (custom palette, `x-button`, uniform card pattern, `max-w-7xl` containers, `px-4 sm:px-6 lg:px-8` gutters); responsive grids at `sm`/`lg` breakpoints; `aspect-*` media boxes that prevent layout shift; a skip link; `focus-visible` rings site-wide; reduced-motion handling for reveals; `env(safe-area-inset-bottom)` for fixed buttons; the hero video loads only at ≥640px.

**Issues (CODE):**

| # | Issue | Location |
|---|---|---|
| U1 | Mobile menu button has no `aria-expanded`/`aria-controls`; the homepage FAQ buttons also lack `aria-expanded` (the `/faq` page has it) | `partials/header.blade.php:40`, `home.blade.php:201` |
| U2 | The hero autoplays every 6s with no pause control and ignores `prefers-reduced-motion` | `home.blade.php:5` |
| U3 | The stone preview panel renders `<img :src="null">` for stones without a cover (broken image icon) | `stones/index.blade.php:26` |
| U4 | The stone preview text box is absolutely positioned inside `min-h-[220px]`, so long descriptions (up to 200 chars + origin + name) may overflow on narrow screens (NOT VERIFIED) | `stones/index.blade.php:29-31` |
| U5 | Comment form: no submitting state, no per-field error display, and several reply forms can be open with duplicated `old()` values | `components/blog/comment-form.blade.php` |
| U6 | Phone and email are not clickable (`tel:`/`mailto:`); this matters most on mobile | footer, contact |
| U7 | FAQ is missing from the main navigation | `partials/header.blade.php:2-10` |
| U8 | The contact button label is built with `x-text="submitting ? '…' : '…'"` from translated strings. A translation containing an apostrophe would break the JS expression | `contact.blade.php:79` |
| U9 | Lightbox: no focus trap and no focus return on close; the images for all slides are in the DOM (lazy) | `projects/show.blade.php:97-118` |
| U10 | Error pages use `@vite(['resources/css/app.css'])`. If the build manifest is missing, the error page itself throws | `components/error-page.blade.php:11` |
| U11 | **The local `public/build` is stale.** It was built 2026-07-30, and newer views (FAQ, legal, error pages) use classes missing from the compiled CSS (e.g. `underline-offset-2`). Production rebuilds on deploy per `DEPLOY.md`, so this only affects local previews | `public/build/assets/app-IhpJh-ll.css` |

---

## 16. Security

No credentials are reproduced in this report.

| # | Area | Finding | Severity | Status |
|---|---|---|---|---|
| S1 | Default credentials | `RoleSeeder` creates admin and editor accounts with a hard-coded, publicly guessable password. `DatabaseSeeder` always calls it, and `DEPLOY.md` gives no seeding guidance | **Critical** (if seeded in prod) | CODE |
| S2 | Comment moderation bypass | Pending and **spam** replies render publicly. Honeypot-flagged spam is displayed in full on the post | **High** | **VERIFIED** |
| S3 | Stored XSS by staff | `{!! $settings->privacy_policy !!}`, `{!! $settings->terms !!}` and `{!! $post->body !!}` render RichEditor HTML without server-side sanitizing. Editors (not only admins) can edit both Settings and Posts | Medium | CODE |
| S4 | Lead spam / abuse | `POST /contact` has no throttle, honeypot or CAPTCHA | Medium–High | **VERIFIED** |
| S5 | Upload hardening | `->image()` accepts `image/*`, which includes SVG. SVGs are served from the same origin under `/storage` (possible XSS vector by staff). No explicit size limits per field | Medium | CODE |
| S6 | DoS via content | An invalid `icon` value breaks the homepage (500) | Medium (availability) | **VERIFIED** |
| S7 | Debug / env | Local `.env` has `APP_DEBUG=true`, `LOG_LEVEL=debug`. `DEPLOY.md` does say to set `APP_DEBUG=false` for production | Info (local) | CODE |
| S8 | `env()` in app code | `StockMediaFetcher` reads `env('PEXELS_API_KEY')` etc. directly. These return `null` after `config:cache`, so the command silently fails in production | Low | CODE |
| S9 | Session cookie | `SESSION_SECURE_COOKIE` is not set; `SESSION_ENCRYPT=false` | Low (set in prod) | CODE |
| S10 | Security headers | No HSTS, CSP, X-Frame-Options, Referrer-Policy or Permissions-Policy middleware | Low–Medium | CODE |
| S11 | Mass assignment | All models use `$fillable`; `Lead::create($request->validated() + …)` is safe. `Comment` `status` is fillable, but the controller sets it explicitly | OK | CODE |
| S12 | CSRF | `@csrf` on all public forms; Filament uses `VerifyCsrfToken` | OK | CODE |
| S13 | SQL | Eloquent/query builder throughout. The blog `LIKE` search uses bindings | OK | CODE |
| S14 | Output escaping | `{{ }}` everywhere except S3 and JSON-LD (JSON-LD uses `JSON_HEX_TAG`, which is correct) | OK | CODE |
| S15 | Secrets | `.env` is git-ignored and absent from git history. It holds live third-party API keys; rotate them if the file was ever shared | Info | **VERIFIED** |
| S16 | Admin lockdown | No IP allow-list, MFA or password reset. Login is throttled by Filament | Low–Medium | CODE |
| S17 | Comment PII | Commenter emails and IPs are stored indefinitely; no retention policy | Low (PDPL) | CODE |
| S18 | Global error handler | `bootstrap/deprecations.php` installs a process-wide `set_error_handler` for `E_DEPRECATED`. It is narrowly scoped, but it replaces any previous handler | Low | CODE |

---

## 17. Performance

Query counts were measured on seeded data in the probe suite. The test environment attaches no media, so the real numbers with images are equal or higher.

| Page | Total queries | `settings` queries | `media` queries |
|---|---|---|---|
| `/` | **26** | 3 | **16** |
| `/projects` | 15 | 3 | 6 |
| `/blog` | 17 | 3 | 8 |
| `/stones` | 11 | 3 | 5 |
| `/services` | 8 | 3 | 4 |
| `/about` | 9 | 4 | 4 |
| `/contact` | 6 | 4 | 0 |

### Actual problems

| # | Problem | Fix direction |
|---|---|---|
| P1 | **Media N+1**: `getFirstMediaUrl()` lazy-loads `media` per model on every list | `->with('media')` in the controllers (Home, Project, Service, Stone, Blog, About, related lists) |
| P2 | **`Setting::current()` runs 3–4×** per request (layout JSON-LD, footer, WhatsApp float, controller) and can write on GET | Memoize once per request and cache (`Cache::rememberForever`, busted on save) |
| P3 | Nested comment replies lazy-load `replies` per level | Eager-load to a bounded depth |
| P4 | Admin navigation badges (leads/comments) + 3 widgets query on every admin page; `leads.status` is not indexed | Add an index; the load is acceptable at current scale |

### Optional optimizations

- `axios` is bundled but unused (it contributes to the 92 KB `app-*.js`; exact share NOT VERIFIED); remove it.
- Google Fonts load from a third-party CDN; self-hosting would help privacy and first paint. The stylesheet is already preloaded non-blocking.
- Media conversions are `nonQueued()`, so uploads block the admin request while resizing. Consider queued conversions + WebP output + responsive images (`withResponsiveImages()`).
- Some stored originals reach ~860 KB. `StockMediaFetcher` caps width at 1920px/q80, but admin uploads are not resized.
- Full-page or fragment caching for anonymous pages. Content changes rarely.
- `/stones` loads all stone types unpaginated; fine now.

---

## 18. Testing

| Metric | Value |
|---|---|
| Test files | **1** (`tests/Feature/PublicPagesTest.php`); `tests/Unit/` is empty |
| Test methods / assertions | 5 / 18 |
| Result (run during audit, SQLite `:memory:`) | **OK: 5 passed** in 0.6s. `.phpunit.result.cache` from Aug 1 recorded earlier contact-test failures, which now pass |
| Test type | Feature smoke tests (HTTP 200, locale persistence, contact validation, lead stored) |

**Covered:** core page 200s, three detail pages, locale switch, contact validation and lead creation.

**Not covered:** blog (index, show, comment submission, moderation visibility, throttle, honeypot), FAQ, legal, sitemap content, 404, inactive-entity 404s, filters, every Filament resource, role/authorization rules, the Settings page, the Users module, media handling, `StockMediaFetcher`, the `fetch:stock-media` command, and RTL output.

**Important missing tests (would have caught the bugs in this audit):**
1. Blog shows only approved replies (S2).
2. Admin can create a user with a role (A1).
3. Editor cannot access Settings or Users (A2).
4. Contact endpoint is rate-limited (L1).
5. Home renders when a service icon is invalid (A11).
6. Query-count budget per page (P1/P2).

**Safety note (verified):** tests use `RefreshDatabase` + `phpunit.xml` SQLite overrides. `DEPLOY.md` correctly warns that running tests while `config:cache` is active would reset the **real** database. No config cache existed during this audit.

---

## 19. Code Quality

**Positives:** small, readable controllers; Form Requests for public input; consistent naming; explanatory comments where decisions are non-obvious (fallback route, honeypot, deprecation shim, hreflang); consistent Blade component usage; incremental, descriptive commit history.

**Weaknesses:**
- The bilingual accessor boilerplate is duplicated about 25 times. A `HasTranslations`-style trait or `spatie/laravel-translatable` would remove it.
- Status values (`new`, `contacted`, …, `pending`, `approved`, `spam`) are string literals repeated across models, resources, widgets and migrations. There are no enums.
- The card markup (project card, service card, stone card) is copy-pasted across 6+ views instead of being a Blade component.
- The gallery lightbox is duplicated between `projects/show` and `stones/show`.
- Query logic sits in views (`Setting::current()` in partials and the layout).
- `README.md` is still the stock Laravel README. `.env.example` does not reflect the project (it lists sqlite and no API keys). `composer.json` still says `"name": "laravel/laravel"`.
- `laravel/pint` is installed, but there is no Pint config and no CI.
- A stale AI-agent worktree lives in `.kilo/worktrees/standing-freezer` (an old snapshot at the "first commit" stage, ignored by `.kilo/.gitignore`). It is confusing for tooling and should be removed once confirmed unneeded.

---

## 20. Broken / Incomplete Features

| # | Feature | State | Evidence |
|---|---|---|---|
| B1 | Blog reply moderation | **BROKEN**: pending and spam replies are publicly visible | `Comment::replies()` unfiltered (`app/Models/Comment.php:38-41`), rendered at `components/blog/comment.blade.php:29-31`; probe |
| B2 | Admin → Users create/edit with roles | **BROKEN**: FK violation | `UserResource.php:41-48`; probe |
| B3 | Homepage/services resilience to a bad icon value | **BROKEN** on invalid input | probe |
| B4 | Slider image | **DISCONNECTED**: uploaded and seeded, never rendered | `home.blade.php` |
| B5 | Lead notification | **MISSING**: the flow ends at the DB | §11 |
| B6 | Quote context from CTAs | **MISSING** | §11 |
| B7 | JSON-LD / hreflang | **IN PROGRESS** (uncommitted) | git diff |
| B8 | `Post` uses `HasActiveScope` without the columns | **LATENT BUG** | `app/Models/Post.php:19` |
| B9 | `leads.source = whatsapp` | **UNUSED** | — |
| B10 | Localized pagination words | **INCOMPLETE** (no `lang/ar.json`) | §13 |
| B11 | Stock-media command after `config:cache` | **BROKEN in prod config** (`env()` in app code) | `StockMediaFetcher.php:72,98,134,163` |

---

## 21. Missing Features

### Critical (required before production)

1. Fix B1 (reply moderation leak).
2. Fix B2 (user management) and provide a safe first-admin bootstrap (a command or env-driven seeder) that does not use default passwords.
3. Split seeders into **production** (roles only) and **demo** (all fake content). Never ship fake testimonials, leads or team members.
4. Lead **notification** to the company (email at minimum) when a contact/quote form is submitted.
5. Rate limiting + honeypot on `POST /contact`.
6. Restrict the Settings page to admins, and sanitize rich-text HTML on output (or restrict it to admins).
7. Harden the service `icon` field (a select list of allowed icons, or a safe render fallback).
8. Production configuration checklist: `APP_DEBUG=false`, `APP_ENV=production`, real `APP_URL`, HTTPS + secure cookies, a real mailer, `storage:link`, a queue worker (or `sync`), and backups.
9. Replace placeholder business data (phone, WhatsApp number, email, address, map coordinates, social URLs, legal text) with real, legally reviewed content.

### Important (for a professional release)

1. Contextual quote requests: pass `?stone=` / `?service=` / `?project=` from CTAs, pre-select them, and store them on the lead (add `project_id`, page URL, locale).
2. Quote-specific fields: area (m²), application, timeline, attachments (drawings/photos) with safe upload handling.
3. A lead workflow in admin: a view page, internal notes, assignee, follow-up date, activity history, CSV export, and email/stone columns.
4. Product attributes for stone types: finishes, colours, thickness/sizes, application, origin type (local/imported); plus filters.
5. SEO: canonical tags, `og:url`, per-entity OG images, a complete sitemap (FAQ, legal, `lastmod`), a sitemap line in `robots.txt`, `BreadcrumbList`/`FAQPage` schema, and meta overrides in admin.
6. URL-based localization (`/ar/...`, `/en/...`) with real hreflang, and a decision on Arabic as the default.
7. Eager-load media and cache settings (P1, P2).
8. Clickable `tel:`/`mailto:`, FAQ in the nav, `aria-expanded` fixes, and a hero reduced-motion pause.
9. Admin password reset + profile page; consider MFA for admins.
10. Test suite expansion (see §18).
11. Project README, a correct `.env.example`, and updated `DEPLOY.md` (storage link, seeding, queue, cron, backups).
12. Privacy-consent checkbox on forms (PDPL).

### Enhancement (after launch)

- WhatsApp click tracking as leads (`source=whatsapp`), plus analytics (GA4/GTM or a privacy-friendly alternative) with conversion events.
- Downloadable catalogue/brochure, product spec sheets.
- Standalone gallery page, site-wide search, careers page.
- Arabic admin UI, content-fallback logic when one language is empty.
- Security headers middleware, admin activity log, and a retention policy for comment IPs and emails.
- Queued media conversions, responsive images, self-hosted fonts, page caching.
- Refactors: translation trait, status enums, card and lightbox components.

---

## 22. Technical Debt

| Debt | Impact | Effort |
|---|---|---|
| Duplicated bilingual accessors (~25) | Maintenance, easy to miss a field | M |
| String-literal statuses (no enums) | Typos, drift between admin, widgets and DB enums | S |
| Duplicated card and lightbox markup | UI inconsistency over time | S–M |
| Queries in views (`Setting::current()` in partials) | Hidden queries, hard to cache | S |
| `Post` + `HasActiveScope` mismatch | Latent runtime error | XS |
| `env()` outside config | Breaks under config cache | XS |
| Global deprecation handler | Hides the root cause; fragile | XS (revisit on the next Laravel upgrade) |
| Stale `.kilo` worktree, boilerplate README, default `.env.example`, `composer.json` name | Onboarding confusion | XS |
| Unused axios | Bundle weight | XS |
| Orphaned media files (suspected) | Disk usage | S (`media-library:clean`, after verifying) |
| PHP floor mismatch (`^8.2` declared vs ≥ 8.4 required by the lock) | Deploy surprises | XS (set `"php": "^8.4"`) |

---

## 23. Production Readiness

| Check | Status |
|---|---|
| `APP_ENV=production`, `APP_DEBUG=false` | ✗ local values (documented in DEPLOY.md) |
| Real `APP_URL` (media URLs depend on it) | ✗ `127.0.0.1:8000` |
| HTTPS / secure cookies / HSTS | ✗ not configured |
| Mail transport | ✗ `log` |
| Queue worker | ✗ undocumented (`QUEUE_CONNECTION=database`) |
| Scheduler / cron | N/A (no scheduled tasks) |
| `storage:link` | ✗ missing from DEPLOY.md (exists locally as a symlink) |
| Production seeding strategy | ✗ demo and default credentials mixed in |
| First admin creation | ✗ no safe path (B2) |
| Build pipeline | ✓ `npm run build` documented; `public/build` git-ignored |
| Config/route/view caching | ✓ documented |
| Backups (DB + `storage/app/public`) | ✗ not addressed |
| Error monitoring (Sentry/Flare) / log rotation | ✗ `LOG_STACK=single` |
| PHP version | ⚠ ≥ 8.4 required (DEPLOY.md is correct; composer.json understates it) |
| Tests / CI | ✗ no CI; minimal tests |
| Real content (contacts, legal, testimonials) | ✗ seeded placeholders |

**Status: EARLY.** The code largely works, but the operational setup is not ready.

---

## 24. Recommended Improvements

### Status by area

| Area | Status | Evidence |
|---|---|---|
| Public Website | **MOSTLY COMPLETE** | All 17 public routes implemented and rendering (VERIFIED 200/404); polished, responsive-oriented UI; issues are context, a11y details and SEO |
| Admin Panel | **MOSTLY COMPLETE** | 14 resources + Settings with CRUD, filters and uploads; **Users module broken**; Settings not restricted |
| Products / Catalogue | **PARTIAL** | Categories → types with media; no product attributes |
| Projects / Portfolio | **MOSTLY COMPLETE** | Filters, featured, before/after, gallery; N+1; limited metadata |
| Lead Management | **PARTIAL** | Capture + admin status only; no notification, context, follow-up or spam protection |
| Blog | **MOSTLY COMPLETE / BROKEN moderation** | Full module; reply leak (B1) |
| Authentication | **MOSTLY COMPLETE** (admin-only scope) | Login works; no password reset, profile or MFA |
| Authorization | **PARTIAL** | Panel gate is correct; Settings open to editors; no permissions/policies |
| User Management | **BROKEN** | B2 |
| Localization | **MOSTLY COMPLETE** (UI) / **PARTIAL** (SEO, admin) | Full key parity; session-based locale |
| SEO | **PARTIAL** | Titles, descriptions, sitemap, OG present; canonical, hreflang, complete sitemap missing |
| Email / Notifications | **NOT IMPLEMENTED** | No classes exist |
| Security | **PARTIAL** | Good defaults (CSRF, escaping, fillable) but S1–S6 |
| Performance | **MOSTLY COMPLETE** | Good front-end work; N+1 and repeated settings queries |
| Testing | **EARLY** | 5 smoke tests |
| Code Quality | **MOSTLY COMPLETE** | Clean but duplicated patterns |
| Production Readiness | **EARLY** | §23 |

### Top 10 improvements (by value ÷ effort)

1. Filter replies to `approved` (one line + test).
2. Fix the roles select in `UserResource` (use the relationship without overriding options, or map names to IDs).
3. Lead email notification (Mailable + queued Notification to the `settings.email` / admin users).
4. `throttle` + honeypot on `/contact`.
5. Restrict Settings to admins; sanitize rich HTML.
6. Production seeder split + an `app:create-admin` command.
7. Contextual CTAs (`?stone_type=…`), pre-selected on the contact form.
8. `->with('media')` everywhere + a cached `Setting::current()`.
9. Canonical + `og:url` + complete sitemap + robots sitemap line.
10. Icon field → `Select` of allowed heroicons, with a render fallback.

---

## 25. Implementation Roadmap

The phases are ordered by technical dependency. Each phase assumes the previous one is merged.

### PHASE 1 — Fix critical issues

| | |
|---|---|
| **Tasks** | (1) Filter `Comment::replies()` to approved and eager-load nested replies; (2) fix the `UserResource` roles field; (3) add `canAccess()` to `Settings` (admin only); (4) replace the free-text `icon` with a validated `Select` and add a safe fallback in views; (5) throttle + honeypot on `POST /contact`; (6) sanitize `{!! !!}` rich text (e.g. a purifier, or `strip_tags` with an allow-list); (7) remove `HasActiveScope` from `Post`; (8) replace `env()` in `StockMediaFetcher` with `config('services.*')` |
| **Files** | `app/Models/Comment.php`, `app/Http/Controllers/BlogController.php`, `app/Filament/Resources/UserResource.php`, `app/Filament/Pages/Settings.php`, `app/Filament/Resources/ServiceResource.php`, `resources/views/{home,services/*}.blade.php`, `routes/web.php`, `resources/views/contact.blade.php`, `app/Http/Requests/StoreLeadRequest.php`, `resources/views/legal/*`, `resources/views/blog/show.blade.php`, `app/Models/Post.php`, `app/Services/StockMediaFetcher.php`, `config/services.php` |
| **Dependencies** | None |
| **Risk** | Low: small, isolated changes; each gets a regression test |
| **Outcome** | No known functional or security defects; staff onboarding works |

### PHASE 2 — Complete core functionality (leads)

| | |
|---|---|
| **Tasks** | (1) `NewLeadNotification` (mail, queued) to the configured company address and admin users; (2) contextual CTAs (`?service=`, `?stone_type=`, `?project=`), pre-selected on the form; (3) migration adding `project_id`, `locale`, `source_url`, `ip_address`, `user_agent` to `leads` + indexes on `status` and `created_at`; (4) consent checkbox; (5) `tel:`/`mailto:` links; (6) digit-normalise WhatsApp everywhere; (7) optional thank-you page for conversion tracking |
| **Files** | new `app/Notifications/NewLeadNotification.php`, `ContactController`, `StoreLeadRequest`, `Lead` model, new migration, `contact.blade.php`, CTA views, `partials/footer.blade.php`, `lang/*/site.php`, `config/mail.php` / `.env` |
| **Dependencies** | Phase 1 (throttle/honeypot in place); a working SMTP account |
| **Risk** | Medium: needs mail infrastructure and a queue worker in production |
| **Outcome** | The company learns about every inquiry immediately, with context |

### PHASE 3 — Admin and business workflow

| | |
|---|---|
| **Tasks** | (1) Lead view page with notes/activity (new `lead_notes` table), assignee (`assigned_to` → users), follow-up date, CSV export; (2) Stone type attributes: finishes, colours, thickness/sizes, application, local/imported (new columns or lookup tables + M:N), with admin fields and public filters; (3) project metadata: sector, area, city, multiple stones (M:N); (4) Settings: logo, default OG image, extra social links; (5) render the slider image or remove that field; (6) admin password reset + profile; (7) seeder split + `app:create-admin` command; (8) optional: permissions via Spatie instead of hard-coded roles |
| **Files** | `app/Filament/Resources/LeadResource*`, new migrations/models, `StoneTypeResource`, `ProjectResource`, `stones/*`, `projects/*`, `Settings` page, `AdminPanelProvider`, `database/seeders/*`, new `app/Console/Commands/CreateAdmin.php` |
| **Dependencies** | Phase 2 (lead schema); business input on the catalogue taxonomy (finishes, colours, sizes) |
| **Risk** | Medium: schema changes on existing data; requires content re-entry |
| **Outcome** | A usable sales pipeline and a real product catalogue |

### PHASE 4 — UI/UX and responsive polish

| | |
|---|---|
| **Tasks** | Extract card and lightbox components; a11y fixes (U1, U2, U9); stone preview image fallback (U3); comment form states (U5); FAQ in nav (U7); safe `x-text` construction (U8); Arabic JSON translations for pagination; empty-language fallback; browser QA at 360 / 768 / 1024 / 1440 in AR and EN |
| **Files** | `resources/views/**`, new `components/*`, `lang/ar.json`, `lang/en.json` |
| **Dependencies** | Phase 3 (new catalogue/project fields affect cards) |
| **Risk** | Low |
| **Outcome** | Consistent, accessible UI verified on real devices |

### PHASE 5 — SEO, performance and security

| | |
|---|---|
| **Tasks** | (1) Decide on and implement URL-based locales (`/ar`, `/en`) + hreflang + localized sitemap; (2) canonical, `og:url`, per-entity OG image, meta overrides; complete sitemap with `lastmod`; robots sitemap line and `/admin` disallow; FAQPage/Breadcrumb schema; commit the in-progress JSON-LD; (3) `->with('media')` everywhere; cached `Setting::current()` shared via a view composer; (4) queued/WebP/responsive media conversions; upload MIME whitelist (no SVG) + size limits; (5) security headers middleware, `SESSION_SECURE_COOKIE`, remove axios; (6) self-host fonts (optional) |
| **Files** | `routes/web.php`, `SetLocale`, `LocaleController`, `layout.blade.php`, `SitemapController`, `public/robots.txt`, all list controllers, `AppServiceProvider`, models (`registerMediaConversions`), Filament upload fields, new middleware, `resources/js/bootstrap.js` |
| **Dependencies** | Phase 4 (views stable). Locale routing touches every route, so do it before content is indexed |
| **Risk** | **High** for locale routing (URL structure change, redirects needed); low for the rest |
| **Outcome** | Arabic and English both indexable; fewer queries per page; hardened headers and uploads |

### PHASE 6 — Testing

| | |
|---|---|
| **Tasks** | Feature tests: blog moderation, comments throttle/honeypot, contact throttle + notification (`Notification::fake`), role/authorization matrix (admin/editor/no-role against every resource + Settings), Filament resource CRUD via Livewire tests, sitemap content, locale routing, inactive-entity 404s, query-count budgets; unit tests for translation accessors and `StockMediaFetcher` (HTTP faked); CI (GitHub Actions) running Pint + PHPUnit |
| **Files** | `tests/Feature/*`, `tests/Unit/*`, `.github/workflows/ci.yml`, `database/factories/*` (only `UserFactory` exists now) |
| **Dependencies** | Phases 1–5 (tests encode the final behaviour). Write the regression tests for Phase 1 fixes *during* Phase 1 |
| **Risk** | Low |
| **Outcome** | Regressions caught before deploy |

### PHASE 7 — Production deployment

| | |
|---|---|
| **Tasks** | Production `.env` (debug off, real URL, HTTPS, SMTP, secure cookies); production seed (roles) + create admin; `storage:link`; queue worker (Supervisor) or `sync`; backups for DB + `storage/app/public`; error monitoring; log rotation (`daily`); content entry of real data (contacts, legal text, testimonials, projects, catalogue); Search Console + sitemap submission; smoke test; update README/DEPLOY.md/`.env.example`; set `"php": "^8.4"` in composer.json |
| **Files** | `.env` (server), `DEPLOY.md`, `README.md`, `.env.example`, `composer.json`, server config |
| **Dependencies** | All previous phases |
| **Risk** | Medium (operational) |
| **Outcome** | Live, monitored, recoverable site with real content |

---

## 26. Final Checklist

### Critical fixes
- [ ] Blog replies filtered to approved (B1/S2)
- [ ] Users create/edit with roles works (B2/A1)
- [ ] Settings page admin-only (A2)
- [ ] Service icon validated, with a safe fallback (B3/A11)
- [ ] Contact form throttled + honeypot (L1/L2)
- [ ] Rich-text output sanitized (S3)
- [ ] Production seeder without default passwords + create-admin command (S1)

### Core business
- [ ] Lead email notification
- [ ] Contextual quote CTAs, stored on the lead
- [ ] Lead follow-up (notes, assignee, export)
- [ ] Stone attributes (finish, colour, size, application)
- [ ] Slider image rendered or field removed

### SEO / Localization
- [ ] Canonical + `og:url` + per-entity OG image
- [ ] Complete sitemap + robots sitemap line
- [ ] Locale strategy decided (URL-based + hreflang; default AR?)
- [ ] Arabic pagination strings
- [ ] Commit or finish the in-progress JSON-LD

### Performance / Security
- [ ] Eager-load media; cache settings
- [ ] Upload MIME whitelist and size limits
- [ ] Security headers; secure cookies
- [ ] `env()` removed from app code
- [ ] Remove unused axios

### Quality / Ops
- [ ] Test suite covering the items above + CI
- [ ] README, `.env.example`, DEPLOY.md updated (storage:link, queue, seeding, backups)
- [ ] `composer.json` PHP constraint `^8.4`
- [ ] Remove stale `.kilo` worktree (after confirming)
- [ ] Real business content entered and legal text reviewed
- [ ] `APP_DEBUG=false`, HTTPS, backups, monitoring on the server
