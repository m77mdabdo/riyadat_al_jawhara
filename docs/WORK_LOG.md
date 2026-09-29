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
| 3a — Lead workflow: view page, notes, assignee, follow-up date, CSV export | _(next commit)_ | 71 / 307 | Export is admin-only, respects table filters, CSV-injection guarded, UTF-8 BOM. Notes editable by author/admin. Dashboard: "Follow-ups Due" stat; project stat now counts active only |

## Pending migrations (owner runs on MySQL, in this order)

1. `2026_09_29_100000_add_context_columns_to_leads_table` — adds nullable `project_id` (FK → projects, null on delete), `locale`, `source_url`, `ip_address`, `user_agent`, `consented_at` to `leads`; indexes on `status` and `created_at`. Existing rows untouched. Up/down verified on SQLite.
2. `2026_09_29_110000_add_workflow_columns_to_leads_table` — adds nullable `assigned_to` (FK → users, null on delete) and `follow_up_at` (date, indexed) to `leads`.
3. `2026_09_29_110100_create_lead_notes_table` — new `lead_notes` table (lead_id cascade, user_id null on delete, body).

## Open questions

- Queue worker: lead emails are queued (`QUEUE_CONNECTION=database`), so they are only sent while `php artisan queue:work` runs. Production needs Supervisor (or switch to `sync`).
- Lead emails are rendered in the app default locale (`APP_LOCALE`, currently `en`); strings exist in both AR and EN.
- Side effect: one early test run wrote a single fake "New quote request" entry to the local, git-ignored `storage/logs/laravel.log` (test since changed to the array mailer).
