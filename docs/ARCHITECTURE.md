# Architecture

How the code is organised, how a request flows through it, and step-by-step recipes for the most common extensions. Read this before adding a feature.

## 1. Folder conventions

```
app/
  Actions/<Domain>/        One class per business operation (CreateLead, StoreComment,
                           RecordWhatsappClick, ExportLeadsCsv, GenerateCatalogue).
                           Public method: handle(...). No HTTP objects inside.
  Contracts/               Interfaces for swappable infrastructure
                           (CatalogueGenerator, StockMediaProvider).
  Console/Commands/        Artisan commands (app:create-admin, privacy:prune, site-images:*).
  Enums/                   String-backed enums for every status/type value
                           (+ Concerns/ with shared helpers).
  Filament/                Admin panel: Resources, Pages, Widgets, Support (shared form
                           pieces), Concerns (TranslatesResourceLabels).
  Http/Controllers/        Thin: FormRequest → Action → response.
  Http/Requests/           Validation for every public form.
  Http/Middleware/         SetLocale (public /{locale}), SetAdminLocale, SecurityHeaders.
  Models/                  Eloquent models; Concerns/ holds shared traits.
  Notifications/           Mail + database notifications (queued).
  Observers/               Model side effects (cache invalidation).
  Policies/                Authorization, one policy per model.
  Services/                Infrastructure implementations (Catalogue/, StockMedia/, …).
  Support/                 Small framework-agnostic helpers (LocalizedUrl, FragmentCache,
                           Navigation, SiteImages, Like, VisitorContext, Analytics).
  View/Composers/          Shared layout data (settings, navigation).
config/site.php            Limits, page sizes, cache lifetimes, upload sizes. No magic
                           numbers in code — add new ones here.
config/locales.php         Public languages and the default (ar).
lang/{ar,en}/site.php      Public UI strings (kept in parity: TranslationParityTest).
lang/{ar,en}/enums.php     Labels for enum values.
lang/ar.json               Arabic admin labels (English keys = the English UI text).
```

## 2. Request lifecycle (public site)

```
routes/web.php  /{locale}/…  (SetLocale sets app locale + URL defaults)
   └─ FormRequest            validation (e.g. StoreLeadRequest)
       └─ Controller          thin: builds a VisitorContext, calls one Action
           └─ Action          business rules, writes models, dispatches notifications
               └─ Model       Eloquent; bilingual fields via HasLocalizedAttributes
   └─ View                    Blade; layout data from View Composers (no queries in partials)
```

Example, the contact form: `ContactController::store(StoreLeadRequest, CreateLead)` → `CreateLead::handle($data, VisitorContext::fromRequest($request))` → `Lead::create()` + `NewLeadNotification::dispatchFor()` → redirect to the thank-you page.

Read-only pages (home, listings) query in the controller and pass collections to the view. Home sections below the hero and the sitemap are cached with `FragmentCache` and invalidated by `PublicContentObserver`.

## 3. Bilingual fields

- Every translatable column exists twice: `name_ar`, `name_en`.
- A model lists its bilingual fields in `$localized` (field ⇒ value when both languages are empty):

  ```php
  use HasLocalizedAttributes;

  protected array $localized = ['name' => '', 'description' => null];
  ```

- `$model->name` returns the current locale's value, falling back to the other language when it is empty. `translated('field')` is available for fields not listed.
- Admin forms show both columns (tabs "English" / "Arabic"). Admin tables and relation selects use `Filament\Support\Localized::column('name')` so they follow the admin's language.

## 4. Enums, policies, actions, contracts

- **Enums** (`app/Enums`): the database stores the plain string (`$lead->status === 'new'`); code compares against `LeadStatus::New->value`. Enums with labels use `HasLabelOptions`: `::options()` for selects, `::labelFor($value)` for display, `getColor()`/`getIcon()` for Filament badges. Labels are translated in `lang/*/enums.php` (or reuse existing public keys).
- **Policies** (`app/Policies`): `ContentPolicy` is the base for everything editors manage (staff may view/create/update; only admins delete). Admins pass every check through `Gate::before` (AppServiceProvider). Admin-only areas (users, settings, activity log, lead export) have policies returning `false`. Filament reads policies automatically; Blade and Livewire use `$user->can(...)`.
- **Actions** (`app/Actions/<Domain>`): take plain data (validated arrays, models, `VisitorContext`) so they can be called from controllers, Filament pages, commands and tests alike.
- **Contracts** (`app/Contracts`) are bound in `AppServiceProvider::register()`: `CatalogueGenerator` → `MpdfCatalogueGenerator`; stock-media providers are tagged `stock-media.providers`. To swap an implementation, bind a different class there.

## 5. Recipes

### Add a new content type (example: "Certificates")

1. **Migration** (additive): `php artisan make:migration create_certificates_table` with `title_ar`, `title_en`, `slug` (unique), `is_active`, `order`, timestamps. Write a working `down()`.
2. **Model** `app/Models/Certificate.php`: `HasFactory`, `HasActiveScope`, `HasLocalizedAttributes` (`$localized = ['title' => '']`), `LogsAdminActivity`, `HasSlug` if it has a public page; media via `InteractsWithMedia` + `HasWebpConversions` and `ImageUpload::MIME_TYPES`. Add relation generics (`@return BelongsTo<X, $this>`) so Larastan stays clean.
3. **Factory** `database/factories/CertificateFactory.php`.
4. **Policy** `app/Policies/CertificatePolicy.php`: `class CertificatePolicy extends ContentPolicy {}` (auto-discovered).
5. **Filament resource** `php artisan make:filament-resource Certificate --generate`, then add `use TranslatesResourceLabels;`, `$navigationGroup = 'Content'`, bilingual tabs, `ImageUpload::make('cover')` and `SeoFields::section()` if it has a page. Add Arabic labels to `lang/ar.json` (the `ArabicAdminTest` lists any missing key).
6. **Public page**: route inside the `{locale}` group in `routes/web.php`, a thin controller, a Blade view using `<x-layout>` and `<x-page-header>`, strings in both `lang/*/site.php`.
7. **Cache**: add the model to the observer list in `AppServiceProvider` (so home/sitemap caches refresh).
8. **Sitemap**: add a `$this->page(...)` entry per record in `SitemapController::build()`.
9. **Search**: add a block in `SearchController::search()` using `Like::anyColumn()` and the bilingual columns.
10. **Tests**: add the resource to `AdminResourcesTest::resources()` and `ArabicAdminTest::RESOURCES`, and write a feature test for the public page (AR + EN, inactive records hidden).

### Add a stone attribute type (example: "Thickness class")

1. Add a case to `App\Enums\StoneAttributeType` (e.g. `case Grade = 'grade';`).
2. Add the label key `site.stones.grade` in both `lang/*/site.php`.
3. To make it a public filter, add `'grade' => StoneAttributeType::Grade->value` to `StoneController::ATTRIBUTE_FILTERS`.
4. Values are managed in the admin under Stones → Stone Attributes (no migration needed: `stone_attributes.type` is a string).
5. Tests: extend `StoneAttributesTest` with the new filter.

### Add a notification

1. `php artisan make:notification SomethingHappened`, implement `ShouldQueue`, set `$tries`, `backoff()` and a `failed()` method that logs.
2. Channels in `via()`: `mail` and/or `database` (the Filament bell).
3. Mail: use the branded markdown layout (`resources/views/vendor/mail`) and a subject/body in both `lang/*/site.php`; send in the recipient's language (`$notifiable->locale`).
4. Dispatch from an Action, wrapped so a mail failure never breaks the request.
5. Tests: `Notification::fake()` and assert recipients, locale and channels; add the template to the admin "Email previews" page.

### Add a language (example: French)

1. Add `'fr'` to `config/locales.php` `supported`.
2. Create `lang/fr/site.php`, `lang/fr/enums.php`, `lang/fr/admin.php` (copy `en`, translate). `TranslationParityTest` checks that every file has the same keys.
3. Add `*_fr` columns for every bilingual field (additive migration) and extend `HasLocalizedAttributes::translated()` with the fallback order you want.
4. Add the language to the header switcher and the admin language menu (`AdminPanelProvider`).
5. The sitemap and hreflang tags pick it up from `LocalizedUrl::supported()` automatically.

## 6. Quality gates

Run before every commit (CI runs the same):

```bash
php artisan config:clear
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse      # Larastan level 6, no baseline
npm run build
```
