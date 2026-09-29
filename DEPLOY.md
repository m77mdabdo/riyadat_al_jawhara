# Deployment

## Requirements

- **PHP ≥ 8.4.** `composer.json` requires `^8.4`, and the locked Symfony packages need at least 8.4.1. XAMPP's bundled PHP 8.1/8.2 will not boot the app.
- PHP extensions: `mbstring`, `intl`, `pdo_mysql`, `exif`, `fileinfo`, `zip`, and **`gd` with WebP support** (image conversions produce WebP). Check with `php -r 'var_dump(gd_info()["WebP Support"]);'`.
- MySQL 8 (or MariaDB 10.6+).
- Node 20 + npm, **build step only** (not needed at runtime).
- HTTPS. Secure cookies and HSTS switch on automatically when `APP_ENV=production`.
- A process supervisor (Supervisor or systemd) for the queue worker.

## Production `.env` (minimum)

Copy `.env.example` to `.env` and set:

| Key | Production value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | The exact public URL, e.g. `https://example.sa`. Image URLs are built from it; if it's wrong, images break and the CSP blocks them |
| `APP_KEY` | `php artisan key:generate` (once) |
| `APP_LOCALE` | Default language for new visitors (`ar` or `en`), a business decision |
| `DB_*` | MySQL credentials |
| `MAIL_*` | A real mailer (SMTP / SES / Postmark). Needed for lead notifications and admin password resets |
| `MAIL_FROM_ADDRESS` | An address on the company domain (SPF/DKIM configured) |
| `QUEUE_CONNECTION` | `database` (with a worker, below) or `sync` (simpler, but slower form submissions) |
| `LOG_STACK` / `LOG_LEVEL` | `daily` / `warning` |
| `PEXELS_API_KEY` etc. | Leave **empty**. Only the demo tooling uses them |

## First deployment

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build          # public/build is not committed

php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force   # roles only; safe to re-run
php artisan app:create-admin     # prompts for name, email and a hidden password
php artisan storage:link         # serves uploaded images from /storage

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Then, in the admin panel (`/admin`):
1. **Settings:** real phone, WhatsApp, email, address, map coordinates, social links, Privacy Policy and Terms (legally reviewed), logo and share image.
2. Replace every demo/stock photo and all demo content (see `docs/MISSING_IMAGES.md`).
3. **Never** run `DemoSeeder` on production. Its testimonials, team members and leads are invented.

## Every deployment after that

```bash
php artisan down --render="errors::503"
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart         # workers pick up the new code
php artisan up
```

### One-off after deploying this release

Existing uploads don't have the new WebP renditions (`card`, `large`, `hero`) yet. Pages fall back to the older JPEG files until you run:

```bash
php artisan media-library:regenerate --only-missing
```

This can take a while with many images; run it outside peak hours.

## Pending migrations in this release

Run in order with `php artisan migrate --force`. All are additive (new tables, or nullable columns and indexes), so no existing row is changed:

1. `2026_09_29_100000_add_context_columns_to_leads_table`: lead project, locale, source URL, IP, user agent, consent time, plus indexes.
2. `2026_09_29_110000_add_workflow_columns_to_leads_table`: assignee and follow-up date.
3. `2026_09_29_110100_create_lead_notes_table`
4. `2026_09_29_120000_create_stone_attributes_table`
5. `2026_09_29_120100_create_stone_attribute_stone_type_table`
6. `2026_09_29_120200_add_origin_type_to_stone_types_table`
7. `2026_09_29_130000_add_metadata_to_projects_table`: sector, area, city.
8. `2026_09_29_130100_create_project_stone_type_table`
9. `2026_09_29_140000_add_extra_socials_to_settings_table`

Take a database backup first. Every migration has a working `down()` (verified on SQLite), so `php artisan migrate:rollback --step=N` undoes them.

## Queue worker (lead emails)

Lead notifications are queued so a slow mail server never slows the contact form. Keep one worker running. Supervisor example (`/etc/supervisor/conf.d/jra-worker.conf`):

```ini
[program:jra-worker]
command=php /var/www/jra/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/jra/storage/logs/worker.log
stopwaitsecs=3600
```

Without a worker, set `QUEUE_CONNECTION=sync` instead. Emails are then sent during the form submission. A mail failure is still caught and logged, and the lead is still saved.

## Scheduler (cron)

The app currently defines **no scheduled tasks**, so no cron entry is required. If tasks are added later, use:

```cron
* * * * * cd /var/www/jra && php artisan schedule:run >> /dev/null 2>&1
```

## Backups

Back up **both** every day and keep at least 14 days:

- **Database:** `mysqldump --single-transaction --routines riyadat_al_jawhara | gzip > jra-$(date +%F).sql.gz`
- **Uploaded media:** `storage/app/public/` (all admin uploads: logos, project/stone/blog photos). This is not in git.

Copy backups off the server, and test a restore at least once.

## Logs

- Set `LOG_STACK=daily` and `LOG_DAILY_DAYS=14` so `storage/logs/laravel-YYYY-MM-DD.log` rotates and old files are removed automatically.
- The queue worker log (`storage/logs/worker.log`, above) should be rotated by `logrotate`.
- Error monitoring (Sentry, Flare, etc.) is not configured and needs an account and credentials (open decision).

## Caching: important warning

`config:cache`, `route:cache` and `view:cache` are required in production for performance.

**Never run `php artisan test` while `config:cache` is active.** The test suite relies on `phpunit.xml` pointing `DB_CONNECTION` at an in-memory SQLite database. A cached config ignores that override, and `RefreshDatabase` would reset your **real** database. Always run `php artisan config:clear` before running tests.

To clear everything when something looks stale: `php artisan optimize:clear`.

## robots.txt and sitemap

- `/sitemap.xml` is generated automatically.
- `public/robots.txt` disallows `/admin` and `/lang/`. The app also has a dynamic `/robots.txt` that adds the absolute `Sitemap:` line for your domain, but the web server serves the static file first. Either delete `public/robots.txt` to use the dynamic one, or submit `https://your-domain/sitemap.xml` in Google Search Console.

## Security notes

- Security headers (CSP, `X-Frame-Options`, `nosniff`, Referrer and Permissions policies; HSTS over HTTPS in production) are sent by `App\Http\Middleware\SecurityHeaders` on both the site and the admin panel. If you add third-party scripts (analytics, chat), their domains must be added to the CSP there.
- Admin uploads accept only JPG, PNG and WebP (no SVG), up to 5 MB.
- `composer audit` currently reports advisories for `laravel/framework` 11.x, `league/commonmark` and `livewire/livewire`. See `docs/WORK_LOG.md` for which are mitigated in code and which need a dependency upgrade.
