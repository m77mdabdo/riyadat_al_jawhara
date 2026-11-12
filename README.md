<div align="center">

<img src="public/logo-header-black@2x.png" alt="Riyadat Al-Jawhara logo" height="96">

# Riyadat Al-Jawhara · ريادة الجوهرة الإبداعية

**A bilingual (Arabic / English) website, product catalogue and CRM for a natural-stone, marble and granite company in Saudi Arabia — with a full no-code CMS behind it.**

[![CI](https://github.com/m77mdabdo/riyadat_al_jawhara/actions/workflows/ci.yml/badge.svg)](https://github.com/m77mdabdo/riyadat_al_jawhara/actions/workflows/ci.yml)
![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)
![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Filament 3](https://img.shields.io/badge/Filament-3-FDAE4B)
![Livewire 3](https://img.shields.io/badge/Livewire-3-4E56A6?logo=livewire&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?logo=tailwindcss&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?logo=alpinedotjs&logoColor=white)
<br>
![Tests](https://img.shields.io/badge/tests-480_passing-2EA043)
![Larastan](https://img.shields.io/badge/Larastan-level_6-6C4BFF)
![Lighthouse](https://img.shields.io/badge/Lighthouse-95–98-F44B21?logo=lighthouse&logoColor=white)
![Accessibility](https://img.shields.io/badge/axe-0_serious_issues-2EA043)
![License: Proprietary](https://img.shields.io/badge/License-Proprietary-lightgrey)

[Overview](#overview) · [Screenshots](#screenshots) · [Features](#features) · [Admin CMS](#the-admin-cms) · [Architecture](#architecture) · [Quality](#quality-in-numbers) · [Getting started](#getting-started) · [Roadmap](#roadmap) · [العربية](#نبذة-بالعربية)

<br>

<img src="docs/images/home-ar.webp" alt="Home page in Arabic" width="100%">

</div>

---

## Overview

**Riyadat Al-Jawhara Al-Ibdaeya** supplies and installs marble, granite, quartz and natural stone for residential and commercial projects across Saudi Arabia. This platform is the company's **public website and its back office in one product**:

| | |
|---|---|
| 🏛️ **Presentation** | Services, a filterable stone catalogue and a project portfolio — in Arabic (right-to-left) and English. |
| 🪨 **Catalogue** | Every stone with specifications, gallery, related projects and a downloadable PDF spec sheet in both languages. |
| 🏗️ **Portfolio** | Completed projects with before/after comparison sliders and the materials used. |
| 📈 **Lead generation** | Quote requests and WhatsApp clicks captured *with the page the visitor came from*, followed up in a built-in CRM. |
| 🛠️ **No-code CMS** | Menus, homepage sections, page content, images, every interface text, branding and SEO — all editable from the admin, in both languages. |

> Built to production standards: strict Content Security Policy, WCAG AA accessibility, Lighthouse 95+ on mobile, 480 automated tests and static analysis in CI.

---

## Screenshots

<sub>All screenshots use demo content.</sub>

### Public website

| Home · English | Stone catalogue · Arabic |
|---|---|
| ![Home page in English](docs/images/home-en.webp) | ![Stone catalogue with filters](docs/images/stones-ar.webp) |
| **Stone detail with PDF spec sheet** | **Project with before/after** |
| ![Stone detail page](docs/images/stone-en.webp) | ![Project detail page](docs/images/project-ar.webp) |
| **Projects portfolio** | **Contact & quote request** |
| ![Projects list](docs/images/projects-en.webp) | ![Contact page](docs/images/contact-ar.webp) |

### Mobile first

<p align="center">
  <img src="docs/images/mobile-home-ar.webp" alt="Home on a phone (Arabic)" width="23%">
  <img src="docs/images/mobile-menu-ar.webp" alt="Mobile menu (Arabic)" width="23%">
  <img src="docs/images/mobile-stone-en.webp" alt="Stone page on a phone (English)" width="23%">
  <img src="docs/images/mobile-projects-ar.webp" alt="Projects on a phone (Arabic)" width="23%">
</p>

### Admin panel & CMS

| Dashboard · English | Dashboard · Arabic (RTL) |
|---|---|
| ![Admin dashboard in English](docs/images/admin-dashboard-en.webp) | ![Admin dashboard in Arabic](docs/images/admin-dashboard-ar.webp) |
| **Homepage builder** | **Site texts — Arabic & English side by side** |
| ![Homepage builder](docs/images/admin-homepage-en.webp) | ![Site texts editor](docs/images/admin-site-texts-en.webp) |
| **Bilingual editing with translation hints** | **Roles & permissions per area** |
| ![Editing a project](docs/images/admin-project-ar.webp) | ![Role permissions](docs/images/admin-roles-en.webp) |
| **Menus** | **Site images** |
| ![Menu editor](docs/images/admin-menus-ar.webp) | ![Site images](docs/images/admin-site-images-en.webp) |

---

## Features

<details open>
<summary><b>🌐 Public website</b></summary>

- Cinematic home page: hero with a slow push-in and scroll-out, and a full-screen *From the quarry to your space* journey whose photos scale and drift with scroll (all motion off for visitors who prefer reduced motion).
- Home page with hero slides, services, stone categories, featured projects, process, statistics (shown only once real figures are entered), heritage strip, testimonials and FAQ — **order and visibility controlled from the admin**.
- Services, stones, projects, gallery, blog (categories, tags, moderated comments), FAQ, about, contact, privacy policy and terms.
- Site-wide search across services, stones, projects, posts and FAQs, in both languages.
- Stone filters by category, finish, colour, application and origin; project filters by service, stone and sector.
- Branded catalogue PDF (photo cover, category bands, contact back cover) and one-page spec sheets per stone, in the brand fonts (Cairo, Marcellus), correctly shaped in Arabic (RTL) and English.
- Before/after slider, lightbox galleries, similar projects; a client's name is shown only with their consent.

</details>

<details open>
<summary><b>📇 Leads & CRM</b></summary>

- "Request a quote" buttons pre-fill the service, stone or project the visitor was viewing.
- Status pipeline with tabs and counts, assignee, follow-up dates, a notes timeline, and one-click **WhatsApp / call / email** from the list.
- WhatsApp clicks are counted; clicks from a service, stone or project page also create a lead.
- CSV export of exactly what the filtered table shows (admins only — it is personal data).
- Daily follow-up digest email at a time set in the admin.

</details>

<details open>
<summary><b>✉️ Notifications</b></summary>

- Branded HTML + plain-text emails in Arabic and English: new lead (team + extra recipients), confirmation to the visitor, follow-up digest, comments awaiting review, password reset, team invitation.
- Notification bell inside the admin. Every email is queued and retried; a mail problem never breaks a visitor's request.
- "Email previews" page and `app:mail-test` for checking every template.

</details>

<details open>
<summary><b>🌍 Bilingual & RTL</b></summary>

- Every page lives under `/ar/…` or `/en/…`; the language switch keeps the current page.
- Full RTL layout with Arabic typography (Cairo, Reem Kufi) and English typography (Inter, Marcellus).
- Admin panel in Arabic or English, chosen per user — fully translated, enforced by tests.

</details>

<details open>
<summary><b>🔎 SEO</b></summary>

- Canonical URLs, hreflang, Open Graph / Twitter cards, JSON-LD (LocalBusiness, Article, BreadcrumbList, FAQPage).
- Per-page title/description overrides, a site-wide title pattern, `noindex` switches, Google/Bing verification, bilingual `/sitemap.xml`, dynamic `/robots.txt`.

</details>

<details open>
<summary><b>🔐 Security & privacy</b></summary>

- Content Security Policy and security headers, rate limits on every public form and download, spam honeypots.
- Upload whitelist (JPG / PNG / WebP), sanitized rich text, validated URLs and IDs.
- Consent-gated analytics (GA4 or Plausible — nothing loads before "Accept"); a retention job clears old IP addresses and browser details.
- Roles and per-area permissions, account deactivation, forced password change, full activity log with before/after values.

</details>

<details open>
<summary><b>⚡ Performance</b></summary>

- Self-hosted subset fonts, responsive WebP renditions, preloaded hero image.
- Visitor-independent page fragments, menus, texts and settings are cached and invalidated on every save.
- Per-page query budgets enforced by tests (no N+1 queries).

</details>

---

## The admin CMS

The admin (`/admin`, built with Filament) lets the company run the whole site without a developer. **Every editable item falls back to the built-in version when left empty**, so the site never shows a blank field.

| Area | What can be edited |
|---|---|
| **Homepage** | Section order and visibility, item counts, hero text, buttons and overlay, cinematic journey chapters (text and photo), "why us" cards, process steps, heritage places, closing call-to-action |
| **Menus** | Header (with drop-downs), footer columns and mobile menu — links to pages, records or custom URLs; header button, language switch and search toggles |
| **Pages** | Header title, subtitle and banner for 13 pages; content blocks (text, image + text, gallery, call to action, quote); SEO; contact-form fields shown / required |
| **Site texts** | Every button, label, message and email text, Arabic and English side by side, with search and placeholder validation |
| **Site images** | Hero, textures, heritage and journey photos and mobile logo — upload, alt text in both languages, reset to built-in |
| **Branding · Footer · Floating** | Site name, logos, favicon set, share image; footer blocks and copyright; WhatsApp button, back-to-top, cookie-banner texts |
| **Settings** | General, contact & map (live preview), social, SEO, analytics, notifications, statistics, legal pages |
| **Content** | Services, stones, attributes, projects, testimonials, team, FAQs, slides, blog — with drag-and-drop ordering, duplicate, trash & restore, bulk activate, "view on site" and global search (`Ctrl/⌘ + K`) |
| **People** | Invite team members by email (they choose their own password from a time-limited link; resend or send a new link from the list), custom roles with view / create / edit / delete per area, activity log |

Editing is built for two languages: Arabic and English tabs show a badge when a translation is missing and offer **"Copy from the other language"**; image fields show the recommended size, crop to the right ratio and take alt text in both languages.

A **dashboard** shows KPIs with week-over-week trends, leads per day by source, leads by service, the most requested stones, the latest leads (status editable in place), follow-ups due, and a *content health* list that links straight to whatever is missing.

📘 A task-by-task **[admin guide](docs/ADMIN_GUIDE.md)** (Arabic and English, with screenshots) is also available inside the panel under **Help**.

---

## Architecture

```mermaid
flowchart LR
    V([Visitor]) -->|/ar · /en| R[Localized routes]
    R --> C[Thin controllers]
    C --> A[Actions]
    C --> Q[(MySQL)]
    C --> B[Blade + Tailwind + Alpine]
    B --> FC{{Fragment cache}}
    subgraph CMS layer
      ST[Site texts overrides] --> T[Translator]
      MN[Menus] --> N[Navigation]
      PG[Pages & blocks]
      SI[Site images]
    end
    T --> B
    N --> B
    PG --> B
    SI --> B
    AD([Admin · Filament]) --> P[Policies & permissions]
    P --> Q
    AD -. save .-> FC
    C --> J[[Queue]] --> M[Emails & bell]
```

- **Thin controllers** call single-purpose **Actions** (`app/Actions`); status and type values are **Enums**; authorization lives in **Policies** backed by per-area permissions; swappable infrastructure sits behind **Contracts** (PDF generator, stock-photo providers).
- Bilingual columns (`name_ar` / `name_en`) are read through one trait with automatic fallback to the other language.
- Interface texts come from `lang/*/site.php`, with admin overrides merged in by a **translation-loader decorator** — the lang files stay the single source of defaults.
- Content changes flush exactly the caches they affect (fragments, menus, pages, images, texts) through model observers.

See [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) for the request lifecycle, conventions and step-by-step recipes.

### Tech stack

| Area | Technology |
|---|---|
| Backend | PHP 8.4+, Laravel 12 |
| Admin | Filament 3, Livewire 3 |
| Frontend | Blade, Tailwind CSS 3, Alpine.js, Vite 6 |
| Media | Spatie Media Library (responsive WebP renditions) |
| Roles / audit | Spatie Permission, Spatie Activitylog |
| PDF | mPDF |
| Database | MySQL 8 / MariaDB 10.6+ (SQLite in-memory for tests) |
| Quality | PHPUnit, Laravel Pint, Larastan (level 6), GitHub Actions |

---

## Quality in numbers

| Check | Result |
|---|---|
| Automated tests | **480** tests, ~3,150 assertions (feature, Livewire, policy, cache, query-budget and translation-coverage tests) |
| Static analysis | Larastan **level 6**, no baseline |
| Lighthouse (mobile, production-like) | **95–98** performance · **100** accessibility · **100** best practices · **100** SEO |
| Accessibility (axe-core) | **0** serious or critical issues across 72 page scans (390 / 1440 px, AR + EN) |
| Responsive layout | No horizontal scrolling from **320 to 1920 px**, 44 px touch targets |
| Content Security Policy | **0** violations, public site and admin |
| Translations | Every admin string has an Arabic translation — enforced by a test |

---

## Getting started

### Requirements

- PHP **8.4+** with `mbstring`, `intl`, `pdo_mysql`, `exif`, `fileinfo`, `zip` and `gd` (with WebP support)
- MySQL 8 or MariaDB 10.6+
- Composer 2, Node.js 20+ and npm

### Local installation

```bash
git clone https://github.com/m77mdabdo/riyadat_al_jawhara.git
cd riyadat_al_jawhara

composer install
npm ci

cp .env.example .env                 # then set DB_* for your local MySQL
php artisan key:generate
php artisan migrate
php artisan db:seed --class=ProductionSeeder   # roles
php artisan db:seed --class=DemoSeeder         # optional: invented demo content
php artisan app:import-site-content            # editable menus, pages, image slots, permissions
php artisan storage:link
php artisan app:create-admin                   # prompts for name, email and a hidden password

npm run build                        # or: npm run dev
php artisan serve                    # http://127.0.0.1:8000/ar
```

`DemoSeeder` creates invented content (testimonials, team, leads); never run it in production.

### Updating an existing installation

> [!IMPORTANT]
> New releases add database columns. Always back up and **migrate before** serving the new code — running it on an un-migrated database returns errors.

```bash
mysqldump … > backup_$(date +%F).sql
php artisan migrate --force
php artisan app:import-site-content      # inserts only what is missing; safe to run again
php artisan optimize:clear
```

### Environment variables

Only placeholders live in `.env.example`; set real values in `.env` on each machine.

| Key | Purpose | Required |
|---|---|---|
| `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_KEY` | Environment, debug mode, public URL, encryption key | Yes |
| `APP_LOCALE` | Default language of the admin, CLI and queued jobs | Yes |
| `DB_*` | Database connection | Yes |
| `MAIL_*`, `MAIL_FROM_*` | Outgoing mail (SMTP or provider); sender on the company domain | Production |
| `MAIL_TEST_RECIPIENT` | The only address test emails may be sent to | Optional |
| `QUEUE_CONNECTION`, `CACHE_STORE`, `SESSION_DRIVER` | Queue, cache and session drivers | Yes |
| `ANALYTICS_GA4_ID` / `ANALYTICS_PLAUSIBLE_DOMAIN` | Consent-gated analytics (can also be set in the admin) | Optional |
| `PRIVACY_RETENTION_DAYS` | Days to keep IP/browser details on leads and comments (default 180) | Optional |
| `PEXELS_API_KEY`, `PIXABAY_API_KEY`, `UNSPLASH_ACCESS_KEY` | Stock-image tooling only; leave empty in production | Optional |

Mail credentials and server keys are deliberately **not** editable from the admin.

### Tests and quality checks

```bash
php artisan config:clear      # always first: tests use an in-memory SQLite database
php artisan test
./vendor/bin/pint --test      # code style
composer analyse              # Larastan level 6
npm run build
```

GitHub Actions runs Pint, Larastan, the full test suite and a production build on every push and pull request.

### Queue and scheduler

Notifications are queued: run `php artisan queue:work`, a cron-driven worker on shared hosting, or set `QUEUE_CONNECTION=sync`. One cron entry — `* * * * * php artisan schedule:run` — runs:

| Command | When |
|---|---|
| `privacy:prune` | Daily 03:15 — clears IP and browser details older than the retention period |
| `leads:follow-up-digest` | Daily (time set in Settings → Notifications, default 08:00 Riyadh) |
| `activitylog:clean` | Weekly — removes activity-log entries older than a year |

### Deployment

Production steps, caching, queue options, email DNS (SPF, DKIM, DMARC), backups and web-server tuning are in [`DEPLOY.md`](DEPLOY.md). Everything to prepare before launch is in [`docs/LAUNCH_CHECKLIST.md`](docs/LAUNCH_CHECKLIST.md).

---

## Roles and permissions

| Area | Admin | Editor | Custom role |
|---|:---:|:---:|:---:|
| Content: view, create, edit | ✓ | ✓ | per area |
| Delete / restore from trash | ✓ | – | per area |
| Permanent deletion | ✓ | – | – |
| Leads: view, edit, notes, assign | ✓ | ✓ | per area |
| Lead CSV export | ✓ | – | optional |
| Settings, branding, email previews | ✓ | – | optional |
| Users, roles, activity log | ✓ | – | optional |

Custom roles are created in **Administration → Roles**. `app:import-site-content` creates the permissions and gives the editor role exactly the rights above.

## Project structure

```
app/
  Actions/        business operations (leads, comments, catalogue, site content)
  Contracts/      interfaces for swappable services
  Enums/          status and type values
  Filament/       admin panel (resources, pages, widgets, form helpers)
  Http/           controllers, form requests, middleware
  Models/         Eloquent models and shared traits
  Notifications/  emails and admin-bell notifications
  Policies/       authorization
  Support/        navigation, site texts, images, caching, SEO, dashboard data
  Translation/    translation loader with admin overrides
config/site.php   limits, page sizes, cache lifetimes, notification defaults
database/         migrations, factories, seeders
lang/             ar/ and en/ translations, ar.json (admin labels)
resources/        Blade views, CSS, JS, email templates
tests/            Feature and Unit tests
docs/             architecture, admin guide, launch checklist, image credits
```

---

## Roadmap

### ✅ Delivered

- [x] Bilingual public website with full RTL, SEO and structured data
- [x] Stone catalogue with filters, PDF catalogue and spec sheets
- [x] Project portfolio with before/after comparisons
- [x] Leads & CRM: context-aware quote requests, WhatsApp tracking, follow-ups, notes timeline, CSV export
- [x] Branded bilingual email notifications and admin bell
- [x] Security hardening: CSP, rate limits, upload whitelist, consent-gated analytics, data retention
- [x] Laravel 12 upgrade
- [x] Complete no-code CMS: homepage builder, menus, pages and content blocks, site images, site texts, branding, footer, floating elements
- [x] Settings split into focused pages (general, contact & map, social, SEO, analytics, notifications, statistics, legal)
- [x] Analytics dashboard with KPIs, charts and content health
- [x] Custom roles with per-area permissions, account deactivation, forced password change, last sign-in
- [x] Trash & restore, duplicate, drag-and-drop ordering, bulk actions and global search across the admin
- [x] Admin guide in Arabic and English, built into the panel
- [x] Team invitations by email, cinematic home page, credited stock photos for every stone type

### 🔜 Next

- [x] **Production launch** on Hostinger with automatic deploys from GitHub
- [ ] Company-domain email with SPF/DKIM/DMARC and off-site backups ([launch checklist](docs/LAUNCH_CHECKLIST.md))
- [ ] **Real company photography** to replace the stock images ([missing images](docs/MISSING_IMAGES.md))
- [ ] Error monitoring and uptime alerts in production
- [ ] Drag-and-drop Kanban board for leads (today: status tabs and grouping)
- [ ] Hero video upload from the admin (today: built-in video on/off)
- [ ] End-to-end browser tests (Playwright) in CI
- [ ] Laravel 13 upgrade before Laravel 12 security support ends (February 2027)

---

## Third-party licences

- **mPDF** is licensed under **GPL-2.0-only** and runs server-side to generate the catalogue PDFs.
- The PDF fonts (Cairo, Marcellus) are under the SIL Open Font Licence; see [`resources/fonts/pdf/README.md`](resources/fonts/pdf/README.md).
- Stock images are listed with their sources and licences in [`docs/IMAGE_CREDITS.md`](docs/IMAGE_CREDITS.md).
- Other dependencies are listed in `composer.json` and `package.json` under their own licences.

## Changelog

- **2026-11-12** — A `.htaccess` in the project folder refuses every web request that reaches it, so a local Apache (XAMPP) serving `htdocs` can no longer hand out `.env`, storage or logs; the site itself is still served from `public/`.
- **2026-11-12** — Security fixes. Only an administrator can give the administrator role or change, lock, send a password link to or delete an administrator's account (the role is left out of the options for everyone else and refused if posted anyway); bulk delete checks each account and never deletes the signed-in one. Comment approve/spam, the lead status dropdown and *Duplicate* now need the matching edit or create permission. The WhatsApp click beacon is limited to 6 a minute per visitor, and at most 50 WhatsApp leads a day are created automatically (`WHATSAPP_DAILY_LEAD_CAP`; later clicks are still counted). Only requests for the APP_URL host are answered, and in production every generated link (password reset and invitation emails included) starts with APP_URL. Symfony and related packages updated (`composer audit` clean). The deploy workflow has read-only repository access, the deploy script keeps the shared `.env` owner-only, the editable consent text on the contact form is escaped (the privacy link stays), and failed-email logs no longer contain recipient addresses. 496 tests.
- **2026-11-11** — Dashboard sign-in: the company logo (from Branding) links back to the website, and an Arabic/English switch on the sign-in and password pages (remembered for the visit; signed-in users keep their saved language). Logo in the dashboard sidebar too. 481 tests.
- **2026-11-11** — Live at **https://riyadat.anasfareed.com** (Hostinger, PHP 8.5). Every push to `main` is tested by GitHub Actions and, when the tests pass, deployed automatically (`deploy/remote-deploy.sh`: new release folder, shared `.env` and storage, migrations, cache warm-up, atomic switch, last five releases kept). The scheduler runs every minute from an hPanel cron job. Real phone and WhatsApp number; stone specifications filled in.
- **2026-11-11** — Catalogue and spec-sheet PDFs redesigned in the company identity: black-and-gold cover with a stone photo and the white logo, running header with the logo, category bands, stone entries with photo, origin, description and specifications, a contact back cover, and a one-page spec sheet with a large photo, specifications table and gallery. Embedded brand fonts (Cairo, Marcellus) instead of DejaVu. 480 tests.
- **2026-11-11** — Team members are invited by email from **Administration → Users** and choose their own password from a time-limited link (resend / new link from the list; new *Team invitation* email in both languages and in Email previews). Cinematic home page: new *From the quarry to your space* section (editable chapters and photos, its own place in the homepage layout), hero push-in and scroll-out, and the scroll reveals now run on every page (they were outside an Alpine scope before). Stone catalogue covers replaced with commercially licensed Pexels/Pixabay photos, credited on each media item and in `docs/IMAGE_CREDITS.md` (`php artisan site-images:attach-stones`). Sections added in a later version keep their built-in place in a saved homepage order. 479 tests.

## License

Proprietary — all rights reserved.

## Author

Designed and developed by **Mohamed Abdo** — Full-Stack Developer · [GitHub](https://github.com/m77mdabdo)

---

<div dir="rtl">

## نبذة بالعربية

**ريادة الجوهرة الإبداعية** شركة متخصصة في توريد وتركيب الرخام والجرانيت والكوارتز والحجر الطبيعي للمشاريع السكنية والتجارية في المملكة العربية السعودية. هذا المشروع هو **موقع الشركة ولوحة إدارتها ونظام متابعة عملائها في منتج واحد**، بالعربية والإنجليزية.

### ماذا يقدّم المشروع؟

- **موقع ثنائي اللغة** بتصميم كامل من اليمين لليسار: الخدمات، كتالوج الأحجار مع الفلاتر، معرض المشاريع مع مقارنة قبل/بعد، المدونة، الأسئلة الشائعة، والتواصل.
- **كتالوج PDF** وورقة مواصفات لكل حجر، بالعربية مع تشكيل صحيح للحروف وبالإنجليزية.
- **نظام متابعة العملاء (CRM):** طلبات عرض السعر ونقرات واتساب تُسجَّل مع الصفحة التي جاء منها الزائر، مع الحالات والمسؤول ومواعيد المتابعة وخط زمني للملاحظات وتواصل بنقرة واحدة (واتساب / اتصال / بريد) وتصدير CSV.
- **إشعارات بريدية** بهوية الشركة وبلغة المستلم، وجرس إشعارات داخل اللوحة.
- **أمان وخصوصية:** سياسة أمان المحتوى (CSP)، حدود للطلبات، قائمة بيضاء للرفع، تحليلات لا تعمل قبل موافقة الزائر، وحذف تلقائي للبيانات القديمة.

### لوحة تحكم بدون برمجة

كل ما في الموقع يُعدّل من اللوحة بالعربية والإنجليزية، وأي حقل يُترك فارغًا يعرض النص أو الصورة الأصلية:

- **الصفحة الرئيسية:** ترتيب الأقسام وإظهارها، الواجهة، البطاقات والخطوات وأماكن التراث.
- **القوائم:** الرأس بقوائم منسدلة، أعمدة التذييل، قائمة الجوال، وزر الدعوة.
- **الصفحات:** العناوين وصور الرأس، أقسام محتوى (نص، صورة ونص، معرض، دعوة، اقتباس)، SEO، وحقول نموذج التواصل.
- **نصوص الموقع:** كل زر وعنوان ورسالة بالعربية والإنجليزية جنبًا إلى جنب.
- **صور الموقع، الهوية، التذييل، العناصر العائمة، الإعدادات.**
- **لوحة مؤشرات** بالرسوم البيانية واتجاهات الأداء وقائمة «سلامة المحتوى».
- **أدوار مخصصة** بصلاحيات لكل قسم، وإيقاف الحسابات، وسجل نشاط كامل.

📘 **[دليل لوحة التحكم](docs/ADMIN_GUIDE.md)** خطوة بخطوة بالعربية والإنجليزية، ومتاح داخل اللوحة من **مساعدة**.

### الجودة بالأرقام

- **480** اختبارًا آليًا، وتحليل ثابت Larastan بالمستوى 6.
- **Lighthouse على الجوال:** أداء 95–98، وإمكانية الوصول وأفضل الممارسات وSEO **100**.
- **0** مشاكل وصول جدية (axe)، و**0** مخالفات لسياسة أمان المحتوى، ولا تمرير أفقي من 320 إلى 1920 بكسل.

### التشغيل

راجع قسم [Getting started](#getting-started) أعلاه. عند تحديث نسخة قائمة: خذ نسخة احتياطية من قاعدة البيانات، ثم `php artisan migrate`، ثم `php artisan app:import-site-content`، ثم `php artisan optimize:clear` — **قبل** تشغيل الكود الجديد.

### ما تم إنجازه ✅

الموقع ثنائي اللغة، الكتالوج وملفات PDF، معرض المشاريع، نظام متابعة العملاء، الإشعارات، تحصين الأمان، الترقية إلى Laravel 12، نظام إدارة المحتوى الكامل، تقسيم الإعدادات، لوحة المؤشرات، الأدوار والصلاحيات، ودليل الاستخدام.

### ما هو قادم 🔜

- إطلاق الموقع على الاستضافة مع إعداد البريد (SPF / DKIM / DMARC) والنسخ الاحتياطي.
- استبدال الصور التجريبية بتصوير حقيقي لمشاريع الشركة.
- مراقبة الأخطاء والتنبيهات في بيئة الإنتاج.
- لوحة كانبان بالسحب والإفلات لمتابعة العملاء.
- رفع فيديو الواجهة من اللوحة.
- اختبارات متصفح آلية ضمن CI.
- الترقية إلى Laravel 13 قبل فبراير 2027.

### المطوّر

صمّمه وطوّره **محمد عبده** — مطوّر Full-Stack · [GitHub](https://github.com/m77mdabdo)

</div>
