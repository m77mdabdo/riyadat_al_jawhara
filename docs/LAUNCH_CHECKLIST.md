# Launch Checklist

Everything the owner has to **provide** or **do** before the site goes live, in order. The code is ready; nothing here needs a developer unless noted. Tick each box as you go.

Technical details for each step are in [`DEPLOY.md`](../DEPLOY.md).

---

## 1. Accounts and credentials (put them in the server `.env` only, never in git)

| # | What | `.env` keys | Why it matters |
|---|---|---|---|
| ☐ 1.1 | **Email sending** (SMTP, Amazon SES, Postmark…) and an address on the company domain with SPF + DKIM set up | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Without it, **no lead emails** reach the company and admins can't reset passwords |
| ☐ 1.2 | **Analytics** (optional): a GA4 measurement ID **or** a Plausible domain | `ANALYTICS_GA4_ID` **or** `ANALYTICS_PLAUSIBLE_DOMAIN` (+ `ANALYTICS_PLAUSIBLE_SRC` if self-hosted) | Leave empty for no analytics. When set, a cookie banner appears and nothing loads until the visitor accepts |
| ☐ 1.3 | **Error monitoring** (optional, recommended): Sentry, Flare or similar | Not wired in yet: needs the provider's DSN and a small package install (developer task) | Otherwise errors are only in `storage/logs` |
| ☐ 1.4 | Production basics | `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`, `APP_KEY` (`php artisan key:generate` once), `DB_*` | Wrong `APP_URL` breaks images and the security policy |
| ☐ 1.5 | Data retention period (ask whoever wrote the privacy policy) | `PRIVACY_RETENTION_DAYS` (default `180`) | IP address and browser details on leads/comments are erased after this many days |

Leave `PEXELS_API_KEY`, `PIXABAY_API_KEY` and `UNSPLASH_ACCESS_KEY` **empty** in production (demo tooling only).

## 2. Server setup (one time)

☐ 2.1 PHP **8.4+** with `gd` (WebP), `intl`, `mbstring`, `pdo_mysql`, `exif`, `fileinfo`, `zip`. MySQL 8 / MariaDB 10.6+. HTTPS.

☐ 2.2 **Take a database backup**, then deploy the code and run:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force   # roles only; safe to re-run
php artisan app:create-admin                           # prompts for a hidden password
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan media-library:regenerate --only-missing    # once, outside peak hours
```

☐ 2.3 The migrations that `migrate --force` will run on the existing database, in this order. All of them only **add** tables or columns; no existing row is changed.

| # | Migration | Effect |
|---|---|---|
| 1 | `2026_09_29_100000_add_context_columns_to_leads_table` | Leads: related project, language, page URL, IP, browser, consent time |
| 2 | `2026_09_29_110000_add_workflow_columns_to_leads_table` | Leads: assignee, follow-up date |
| 3 | `2026_09_29_110100_create_lead_notes_table` | Internal notes on leads |
| 4 | `2026_09_29_120000_create_stone_attributes_table` | Finish / colour / size / application options |
| 5 | `2026_09_29_120100_create_stone_attribute_stone_type_table` | Links stones to those options |
| 6 | `2026_09_29_120200_add_origin_type_to_stone_types_table` | Local / imported |
| 7 | `2026_09_29_130000_add_metadata_to_projects_table` | Project sector, area, city |
| 8 | `2026_09_29_130100_create_project_stone_type_table` | Additional stones per project |
| 9 | `2026_09_29_140000_add_extra_socials_to_settings_table` | LinkedIn, X, YouTube links |
| 10 | `2026_09_30_100000_add_stats_to_settings_table` | Home statistics (hidden while empty) |
| 11 | `2026_09_30_110000_create_whatsapp_clicks_table` | Anonymous WhatsApp click counts |
| 12 | `2026_09_30_120000_add_description_to_stone_categories_table` | Category descriptions |
| 13 | `2026_09_30_130000_add_show_client_name_to_projects_table` | "Show client name" switch (off by default) |
| 14 | `2026_09_30_140000_add_seo_overrides_to_content_tables` | Optional Google title/description per page |
| 15 | `2026_09_30_150000_create_activity_log_table` | Admin activity log |
| 16 | `2026_09_30_150100_add_event_column_to_activity_log_table` | (activity log) |
| 17 | `2026_09_30_150200_add_batch_uuid_column_to_activity_log_table` | (activity log) |
| 18 | `2026_09_30_160000_add_locale_to_users_table` | Admin panel language per user |

☐ 2.4 **Cron** (one line, every minute). It runs the privacy clean-up and the activity-log clean-up:

```cron
* * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
```

☐ 2.5 **Queue** for lead emails: pick one option in DEPLOY.md → "Queue worker" (Supervisor, the cron-driven worker for shared hosting, or `QUEUE_CONNECTION=sync`).

☐ 2.6 **Web server compression and caching:** turn on gzip/Brotli, and 1-year caching for `/build/` and `/images/site/` (snippet in DEPLOY.md). This is what takes mobile Lighthouse performance from the 80s to 94–97.

☐ 2.7 **Backups:** a daily database dump **and** a copy of `storage/app/public/` (all uploaded photos), kept at least 14 days, stored off the server. Do one test restore.

## 3. Real content (in the admin panel, `/admin`)

Sign in with the account from step 2.2. You can switch the panel to Arabic from the user menu (top corner).

**Settings (admins only):**
- ☐ 3.1 Phone, WhatsApp (with country code, e.g. `9665…`), email, address (AR + EN), working hours (AR + EN).
- ☐ 3.2 **Map coordinates**: exact latitude and longitude of the showroom/office (from Google Maps → right-click → the numbers).
- ☐ 3.3 About, Vision, Mission (AR + EN).
- ☐ 3.4 **Privacy Policy and Terms of Use** (AR + EN), **reviewed by a lawyer** for PDPL. They must mention the analytics provider (if any) and the retention period from 1.5.
- ☐ 3.5 **Statistics** (projects completed, years of experience, cities served, clients served): **real figures only**. Leave a field empty and it stays hidden.
- ☐ 3.6 Social links; optional logo / light logo / share image (1200×630).

**Photos:** replace every stock or demo photo with the company's own. Sizes per slot are in [`docs/MISSING_IMAGES.md`](MISSING_IMAGES.md); in short:

| Where | Size | Notes |
|---|---|---|
| ☐ Hero slides | 1920×1080 | Subject centred; text sits on top |
| ☐ Service covers | 1600×900 | Subject in the centre 4:3 area |
| ☐ Stone covers | 1200×1200 | Close-up of the real slab |
| ☐ Stone galleries | 1600×1200 | Several finishes and applications |
| ☐ Stone category images | 1280×853 | Shown on the home tiles; optional (a texture is used otherwise) |
| ☐ Project covers, galleries | 1920×1080 / 1600×1200 | **Only the company's own projects** |
| ☐ Project before/after | 1920×1080 each | Same camera position; the slider shows only when both exist |
| ☐ Team photos | 600×600 | Real staff **with their written consent**, or delete the demo team members |
| ☐ Heritage strip: Diriyah, Najdi architecture | ≤ 1920 px wide, ≤ 250 KB WebP | No licensed stock photo was found; supply licensed photos (see `config/site-images.php`) or leave the pattern cards |

**Text content:**
- ☐ 3.7 **Delete all demo content** (demo testimonials, team members, leads, blog posts) created by `DemoSeeder`, if it was ever run on this database.
- ☐ 3.8 **Testimonials**: real clients only, with their permission to publish name (and photo, if any).
- ☐ 3.9 **Stone specifications** (finish, colour, size/thickness, application, origin) for every stone. The catalogue PDF is built from them.
- ☐ 3.10 Projects: client names stay hidden unless you switch "Show client name on the website" on, and only do that **with the client's permission**.
- ☐ 3.11 FAQ answers checked by the business.

## 4. After go-live

- ☐ 4.1 **Google Search Console**: add the domain, verify it, and submit `https://your-domain/sitemap.xml`. Check that `https://your-domain/robots.txt` shows the correct `Sitemap:` line.
- ☐ 4.2 Send a test quote from `/ar/contact` and `/en/contact`: the thank-you page appears and the email arrives.
- ☐ 4.3 Click a WhatsApp button on a stone page, then check the dashboard's "WhatsApp Clicks" count goes up.
- ☐ 4.4 Download `/ar/stones/catalogue.pdf` and `/en/stones/catalogue.pdf` and check the stone data.
- ☐ 4.5 `php artisan schedule:list` shows `privacy:prune` and `activitylog:clean`.
- ☐ 4.6 Before every later release: `composer audit` and `npm audit --omit=dev`; take a backup before `migrate`.

## 5. Open decisions for the owner

- **Laravel 12:** Laravel 11 security support ended on 12 March 2026. This branch (`upgrade/laravel-12`) runs Laravel 12, which is supported until 24 February 2027 (see `docs/UPGRADE_LARAVEL_12.md`). Launch from this branch; plan Laravel 13 before February 2027.
- **mPDF licence:** the catalogue PDF uses mPDF (GPL-2.0-only), running on the server only. Confirm this is acceptable for the company, or ask for an alternative.
- **Error monitoring** provider (1.3).
