# Images & Media — Inventory and What the Company Must Supply

Audit date: 2026-09-29 (Phase 4). This lists every image slot on the public site, whether a file exists, and what a real photo should look like.

**Key point:** the code no longer shows a broken image anywhere. Every Media Library slot renders through `<x-media-image>`, which falls back to the branded `<x-image-placeholder>` when nothing has been uploaded, and to the original file when a resized version hasn't been generated yet. What remains is a **content** task: replacing stock and demo photography with real company photos.

Status legend:
- **OK**: the file exists and is final (brand asset).
- **Stock**: the file exists, but it is a stock or demo photo (Pexels) and must be replaced before launch.
- **Placeholder**: nothing is uploaded; the branded placeholder is shown.
- **Missing**: referenced, but the file does not exist. None remain after this phase.

## 1. Static files in `public/` (checked on disk)

| File / slot | Where it's used | Status | Size on disk | Recommended size / ratio | Real company photo needed? |
|---|---|---|---|---|---|
| `logo-header-black@2x.png` | Header logo (≥ 640px), error pages, JSON-LD `logo`; default for Settings → Branding → Logo | OK | 294×256 | Keep the ratio (~1.15:1), transparent PNG, ≥ 2× display size (display is 56px high) | No (brand asset) |
| `logo-header-white@2x.png` | Footer logo; default for Settings → Branding → Light logo | OK | 294×256 | Same as above | No |
| `logo-monogram-header-black@2x.png` | Header logo on phones (< 640px) | OK | 424×256 | ~1.65:1, transparent PNG | No. Note: it is **not** replaced by an uploaded logo; supply a new monogram file if the brand changes |
| `logo-monogram-header-white@2x.png` | Not used | OK (unused) | 424×256 | — | — |
| `logo-full-*-2000.png`, `logo-monogram-black-2000.png` | Not used (source brand files) | OK (unused) | 2000px | — | — |
| `og-image-1200x630.png` | Default social share image (`og:image`, `twitter:image`, JSON-LD `image`); replaceable in Settings → Branding | OK | 1200×630 | 1200×630 (1.91:1), < 300 KB | Optional: a branded photo of real work shares better |
| `favicon.ico` | Browser tab | OK, but 16×16 only | 16×16 | Multi-size ICO (16/32/48) | No |
| `favicon-16x16.png`, `favicon-32x32.png`, `favicon-48x48.png`, `apple-touch-icon.png` (180), `android-chrome-192x192.png`, `android-chrome-512x512.png`, `maskable-icon-512x512.png` | Head links + **new** `public/site.webmanifest` | OK | as named | — | No |
| `images/hero-poster.webp` | Home hero background (poster, and the video's first frame) | Stock | 1280×720, 16 KB | 1920×1080 (16:9) WebP, subject centred (text sits on top) | **Yes**: a signature project or a stone close-up |
| `videos/hero-background.mp4` | Home hero video (≥ 640px, only when no slide has an image) | Stock | 400 KB | 1920×1080, ≤ 8 s loop, muted, < 2 MB | **Yes**, or remove it and rely on slide images |
| `images/about-hero.webp` | About page banner (alt: "Designers reviewing a floor plan on a white marble table") | Stock | 1300×1300, 76 KB | 2100×900 (21:9 desktop; cropped to 16:9 on mobile) | **Yes**: team, showroom or workshop |
| `images/textures/stone-texture.webp` | Testimonials background (10% opacity, decorative) | Stock (acceptable) | 1280×720, **372 KB** | Same, recompressed to < 100 KB | No: decorative texture. Recompressing is recommended |
| `images/textures/marble-texture.webp` | Home CTA background (7% opacity, decorative) | Stock (acceptable) | 960×1280, 68 KB | — | No |

## 2. Media Library slots (uploaded in the admin panel)

The counts are a **read-only** query of the local database on 2026-09-29. Every uploaded file there was fetched from Pexels by the demo seeders, so all of it is stock or demo content.

| Model → collection | Where it's used | Local DB | Status | Recommended size / ratio | Real photo needed? |
|---|---|---|---|---|---|
| Slider → `image` | Home hero background per slide (**new** in Phase 3d; falls back to poster/video) | 3 of 3 slides | Stock | 1920×1080 (16:9), subject centred | **Yes** |
| Service → `cover` | Service cards (4:3) and service page banner (16:9 mobile / 21:9 desktop) | 4 of 4 | Stock | 1600×900; keep the subject in the centre 4:3 area | **Yes**: the company's own work per service |
| StoneType → `cover` | Stone cards (square), stone preview (4:3), stone page (square) | 5 of 5 | Stock | 1200×1200 (square), close-up slab texture | **Yes**: real slabs or tiles from stock |
| StoneType → `gallery` | Stone page gallery + lightbox | 10 files / 5 stones | Stock | 1600×1200, several finishes and applications | **Yes** |
| Project → `cover` | Project cards (4:3), project page (16:9) | 6 of 6 | Stock | 1920×1080 | **Yes**: every portfolio photo must be the company's own project |
| Project → `before` / `after` | Before/after comparison slider (16:10 mobile / 21:9 desktop) | 6 + 6 | Stock | Same camera position and size for both, 1920×1080 | **Yes** (the slider only shows when both exist) |
| Project → `gallery` | Project gallery + lightbox | 12 files / 6 projects | Stock | 1600×1200 | **Yes** |
| Post → `cover` | Blog cards (4:3), article banner, `Article` JSON-LD image | 8 of 10 posts | Stock; **2 posts show the placeholder** | 1600×900 | Recommended |
| TeamMember → `photo` | About page team grid (circle crop) | 4 of 4 | Stock (stock portraits of invented people) | 600×600, face centred | **Yes**: real staff, with their consent, or remove the section |
| Testimonial → `client_photo` | Home testimonials (36px circle) | 3 of 4; **1 shows the placeholder** | Stock (invented clients) | 200×200 | Only with the client's permission; otherwise leave empty (the placeholder is fine) |
| Setting → `logo`, `logo_light`, `og_image` | Branding (**new**, Phase 3d) | not uploaded | Placeholder → static brand files | See section 1 | Optional |

## 3. Code-level guarantees (verified)

- Every public `<img>` has `alt` (meaningful, or empty when decorative/duplicated by adjacent text), `width`/`height` or an aspect-ratio box, `decoding="async"`, and `loading="lazy"` except above-the-fold images (hero, detail-page covers), which use `loading="eager"` + `fetchpriority="high"`.
- No `<img src="">` and no `:src="null"`: the stone preview now renders the placeholder when a stone has no cover (U3).
- Playwright run over 15 pages × 11 widths × 2 locales: **0 broken images, 0 empty `src`** (with an empty media table, so every slot exercised the fallback path).
- Uploads through Settings → Branding accept JPG/PNG/WebP only, max 2 MB. The same whitelist for every other upload field is Phase 5.

## 4. Not done (stop point)

No stock images were downloaded and `fetch:stock-media` was not run. Replacing the stock photos above requires **real company photography** (STOP POINT 3).
