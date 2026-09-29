# Riyadat Al-Jawhara (JRA) — Website & Admin

Bilingual (Arabic / English, full RTL) company website for **Riyadat Al-Jawhara Al-Ibdaeya**, a natural stone, marble and granite supply, installation and design business. It includes a Filament admin panel that the company uses to manage content and incoming quote requests.

| | |
|---|---|
| Framework | Laravel 11 · PHP **8.4+** |
| Admin | Filament 3 (`/admin`) · Livewire 3 |
| Media | Spatie Media Library (JPG/PNG/WebP, WebP responsive renditions) |
| Roles | Spatie Permission (`admin`, `editor`) |
| Frontend | Blade · Tailwind CSS 3 · Alpine.js · Vite 6 |
| Database | MySQL (production) · SQLite `:memory:` (tests) |

## What the site does

**Public site (AR/EN, switch in the header):**
- **Home:** hero slides, services, featured projects, stone collection, process, testimonials, FAQ.
- **Services** and **Stones** (catalogue): stone pages show specifications (finish, colour, size/thickness, application, local/imported), a gallery and related projects. `/stones` filters by category and specification.
- **Projects** (portfolio): filters by service, stone and sector; before/after comparison slider; gallery lightbox; materials used.
- **Blog:** categories, tags and search; threaded comments that appear only after moderation.
- **FAQ, About, Contact, Privacy Policy, Terms of Use.**
- **Quote/contact form:** "Request a quote" buttons pre-fill the service, stone or project the visitor was viewing. The form includes PDPL consent, spam protection (rate limit + honeypot), and an email to the company for every lead.
- **SEO:** canonical URLs, Open Graph / Twitter cards, JSON-LD (LocalBusiness, Article, BreadcrumbList, FAQPage), `/sitemap.xml`, robots rules.

**Admin (`/admin`):**
- **Leads:** status pipeline, assignee, follow-up date, internal notes, "follow-ups due" dashboard stat, and CSV export (admins only).
- **Content:** projects, services, stones, stone categories, stone attributes, hero slides, testimonials, team, FAQs, blog posts, categories, tags, comment moderation.
- **Settings** (admins only): contact details, map, social links, legal pages, logo and share image.
- **Users** (admins only), password reset, and a profile page (the current password is required to change email or password).

| Role | Can do |
|---|---|
| `admin` | Everything, including deleting records, Users, Settings and lead export |
| `editor` | Create and edit content, handle leads and notes. Cannot delete, manage users or change Settings |

## Local setup

```bash
composer install
npm install
cp .env.example .env          # then set DB_* for your local MySQL
php artisan key:generate
php artisan migrate
php artisan storage:link

# Optional fake demo content (services, stones, projects, blog, leads...).
# Without PEXELS_API_KEY it creates no images (placeholders are shown).
php artisan db:seed --class=DemoSeeder

# Create your admin login (prompts for a hidden password)
php artisan app:create-admin

npm run dev                   # or: npm run build
php artisan serve
```

- `php artisan db:seed` on its own runs **ProductionSeeder** (roles only) and is safe everywhere. **DemoSeeder** is fake content: never run it in production.
- No seeder creates user accounts or contains passwords. Use `app:create-admin`.
- Lead emails are queued. Locally, either run `php artisan queue:work` or set `QUEUE_CONNECTION=sync`. With `MAIL_MAILER=log`, emails are written to `storage/logs/laravel.log`.

## Tests

```bash
php artisan config:clear   # ALWAYS first -- see warning below
php artisan test
./vendor/bin/pint --test
```

`phpunit.xml` forces an in-memory SQLite database. **Never run tests while `config:cache` is active:** a cached config ignores that override, and `RefreshDatabase` would wipe your real database. Tests also fake the `public` disk, so they never write into `storage/app/public`.

CI (`.github/workflows/ci.yml`) runs Pint, the full PHPUnit suite and a production asset build on every push and pull request.

## Project structure (the parts specific to this project)

```
app/
  Console/Commands/CreateAdmin.php      app:create-admin
  Console/Commands/FetchStockMedia.php  fetch:stock-media (dev/demo only)
  Filament/                             admin resources, Settings page, widgets
  Filament/Support/ImageUpload.php      the only allowed admin image upload field
  Http/Middleware/SetLocale.php         session-based AR/EN
  Http/Middleware/SecurityHeaders.php   CSP and security headers (site + admin)
  Models/Concerns/                      HasTranslations (AR/EN fallback), media helpers
  Notifications/NewLeadNotification.php email to the company per lead
  Support/                              ServiceIcon list, LeadCsvExporter
database/seeders/ProductionSeeder.php   roles only
database/seeders/DemoSeeder.php         fake demo content
lang/{ar,en}/site.php                   all public UI strings (kept in parity)
resources/views/components/             layout, cards, media-image, lightbox, ...
docs/                                   work log, image inventory
```

## Documentation

- [`DEPLOY.md`](DEPLOY.md): server requirements, first deploy, updates, queue, backups, logs.
- [`PROJECT_AUDIT_REPORT.md`](PROJECT_AUDIT_REPORT.md): the original technical audit (baseline).
- [`docs/WORK_LOG.md`](docs/WORK_LOG.md): what was changed, phase by phase, plus pending migrations and open questions.
- [`docs/MISSING_IMAGES.md`](docs/MISSING_IMAGES.md): every image slot and which real photos the company must supply.
