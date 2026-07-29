# Deployment

## Requirements

- PHP ≥ 8.4 (several dependencies, e.g. `openspout` and current Symfony components, require this — XAMPP's bundled PHP 8.1 will not boot the app; point your web server at a PHP ≥ 8.4 runtime instead)
- MySQL
- Node/npm (build step only, not needed at runtime)

## Build steps (run on every deploy)

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`config:cache`, `route:cache`, and `view:cache` are required for production — without them the app still works, but re-parses every config file, route, and Blade template on each request. These are the standard Laravel production performance win and were verified as part of the Lighthouse pass (Section 7): running the site un-cached vs. cached was the difference between a 74 and a 100 Lighthouse Performance score locally.

## Clearing caches (deploys, or when something looks stale)

```bash
php artisan optimize:clear
```

**Important:** never run `php artisan test` while `config:cache` is active. The test suite relies on `phpunit.xml`'s `<env name="DB_CONNECTION" value="sqlite"/>` override to run against an isolated in-memory database — but a cached config freezes whatever `.env` said at cache time and ignores that override. If you cache config and then run tests, `RefreshDatabase` will run its migration reset against your **real** database instead of SQLite. Always `php artisan optimize:clear` before running tests locally.

## Assets

`npm run build` outputs hashed, minified assets to `public/build/` via Vite. Nothing under `public/build/` is committed to git — it must be rebuilt on every deploy.

## Environment

Copy `.env.example` to `.env` and set at minimum:
- `APP_URL` — must match the actual domain/host the site is served from. If this doesn't match, Spatie Media Library will generate broken image URLs (uploads still work, but previews/thumbnails will fail to load).
- `DB_*` — MySQL connection details.
- `APP_ENV=production`, `APP_DEBUG=false` for a real deploy.
