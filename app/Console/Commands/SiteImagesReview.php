<?php

namespace App\Console\Commands;

use App\Support\SiteImages;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Builds docs/visual-review/index.html: a self-contained page (inline CSS,
 * embedded thumbnails and fonts) showing every image slot's candidates with
 * their IDs and the chosen one, the Phase 10 moodboard, and before/after
 * screenshots (linked from docs/visual-review/shots/, which is git-ignored).
 */
class SiteImagesReview extends Command
{
    protected $signature = 'site-images:review';

    protected $description = 'Build the self-contained visual review page (docs/visual-review/index.html)';

    public function handle(): int
    {
        $slots = config('site-images.slots');
        $choices = SiteImages::choices();

        $slotHtml = collect($slots)->map(function (array $slot, string $key) use ($choices) {
            $file = storage_path("app/stock-candidates/{$key}/candidates.json");
            $candidates = is_file($file) ? json_decode(file_get_contents($file), true) : [];
            $chosen = $choices[$key]['id'] ?? null;

            $cards = collect($candidates)->map(function (array $c) use ($key, $chosen) {
                $thumb = $this->thumbnail(storage_path('app/stock-candidates/'.$c['file']));
                $isChosen = $c['id'] === $chosen;

                return sprintf(
                    '<figure class="cand%s"><img src="%s" alt="%s"><figcaption><code>%s</code>%s<br>score %s · %s KB · %s<br><a href="%s" target="_blank" rel="noopener">source</a> · <code>php artisan site-images:pick %s %s</code></figcaption></figure>',
                    $isChosen ? ' chosen' : '',
                    $thumb,
                    e($c['alt'] ?? ''),
                    e($c['id']),
                    $isChosen ? ' <strong>✔ chosen</strong>' : '',
                    e($c['score']),
                    e($c['kb']),
                    e($c['photographer'] ?? ''),
                    e($c['page_url'] ?? '#'),
                    e($key),
                    e($c['id']),
                );
            })->implode('');

            $status = $chosen
                ? 'Chosen: <code>'.e($chosen).'</code> ('.e($choices[$key]['picked']).')'
                : '<strong class="warn">No image chosen</strong>: no candidate passed review, so the site shows a pattern card / fallback.';

            return '<section class="slot"><h3>'.e($key).'</h3><p>'.e($slot['used']).' · query "<em>'.e($slot['query']).'</em>" · budget '.e($slot['max_kb']).' KB</p><p>'.$status.'</p><div class="cands">'.($cards ?: '<p>No candidates on disk (run <code>php artisan site-images:candidates '.e($key).'</code>).</p>').'</div></section>';
        })->implode('');

        $palette = collect([
            'charcoal' => '#2B2B2B', 'stone-600' => '#726D67', 'cream' => '#F8F6F3',
            'sand-100' => '#F3EBDD', 'sand-200' => '#E8DAC0', 'sand-300' => '#D9C39C', 'sand-700' => '#7D6437',
            'gold-400' => '#C9A45A', 'gold-600' => '#946F2B', 'terracotta-500' => '#B9623F', 'terracotta-600' => '#9C4E30',
        ])->map(fn ($hex, $name) => "<div class=\"sw\"><span style=\"background:{$hex}\"></span><code>{$name}</code><br><small>{$hex}</small></div>")->implode('');

        $shots = $this->screenshotTable();

        $fontFaces = $this->fontFace('Reem Kufi', 'reem-kufi-arabic-700-normal').$this->fontFace('Marcellus', 'marcellus-latin-400-normal');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>JRA — Visual review (Phase 10)</title>
<style>
{$fontFaces}
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;margin:0;background:#F8F6F3;color:#2B2B2B;line-height:1.5}
header,main{max-width:1200px;margin:0 auto;padding:24px}
h1,h2{font-family:Marcellus,serif;font-weight:400}
h2{border-bottom:2px solid #946F2B;padding-bottom:6px;margin-top:48px}
code{background:#EFEBE4;padding:1px 4px;border-radius:4px;font-size:12px}
.slot{background:#fff;border:1px solid #E8DAC0;border-radius:12px;padding:16px;margin:16px 0}
.cands{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}
.cand{margin:0;border:2px solid transparent;border-radius:8px;padding:6px;font-size:12px}
.cand img{width:100%;height:140px;object-fit:cover;border-radius:6px;display:block}
.cand.chosen{border-color:#946F2B;background:#F3EBDD}
.warn{color:#9C4E30}
.swatches{display:flex;flex-wrap:wrap;gap:12px}.sw{width:110px;font-size:12px}.sw span{display:block;height:56px;border-radius:8px;border:1px solid #D9C39C}
.fonts p{margin:8px 0}.ar{font-family:'Reem Kufi',serif;font-size:32px;direction:rtl}.en{font-family:Marcellus,serif;font-size:32px}
.motifs{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}.motif{background:#fff;border:1px solid #E8DAC0;border-radius:12px;padding:16px;color:#946F2B}
table{border-collapse:collapse;width:100%;font-size:13px}td,th{border:1px solid #E8DAC0;padding:6px;vertical-align:top;background:#fff}td img{width:100%;max-height:420px;object-fit:cover;object-position:top}
</style>
</head>
<body>
<header>
<h1>Riyadat Al-Jawhara — visual review</h1>
<p>Generated by <code>php artisan site-images:review</code>. To swap an image, copy the <code>site-images:pick</code> command shown under the candidate you prefer, run it, then rebuild this page. Imagery rules: no flag, emblem, Vision 2030 logo, holy sites, identifiable faces or brand names; stock images are never used as the company's projects.</p>
</header>
<main>
<h2>Moodboard</h2>
<h3>Palette</h3><div class="swatches">{$palette}</div>
<h3>Typography</h3>
<div class="fonts"><p class="ar">الجودة تبدأ من الحجر — Reem Kufi</p><p class="en">Quality Starts With Stone — Marcellus</p><p>Body: Cairo (Arabic) · Inter (English), self-hosted.</p></div>
<h3>Motifs (inline SVG, RTL-safe)</h3>
<div class="motifs">
<div class="motif"><strong>Najdi frieze</strong><svg width="100%" height="14"><defs><pattern id="n" width="16" height="12" patternUnits="userSpaceOnUse"><path d="M0 12 8 2 16 12" fill="none" stroke="currentColor" stroke-width="1.5"/></pattern></defs><rect width="100%" height="100%" fill="url(#n)"/></svg></div>
<div class="motif"><strong>Heading underline</strong><br><svg width="80" height="10" viewBox="0 0 80 10" fill="currentColor"><path d="M0 10 5 0 10 10Z M14 10 19 0 24 10Z M28 10 33 0 38 10Z M42 10 47 0 52 10Z M56 10 61 0 66 10Z M70 10 75 0 80 10Z"/></svg></div>
<div class="motif"><strong>Geometric lattice (≤ 5%)</strong><svg width="100%" height="96" style="color:#2B2B2B;opacity:.3"><defs><pattern id="g" width="48" height="48" patternUnits="userSpaceOnUse"><path d="M24 4 29 19 44 24 29 29 24 44 19 29 4 24 19 19Z" fill="none" stroke="currentColor"/></pattern></defs><rect width="100%" height="100%" fill="url(#g)"/></svg><small>shown at 30% here for visibility</small></div>
</div>
<h2>Image slots and candidates</h2>
{$slotHtml}
<h2>Before / after</h2>
<p>Full-page screenshots (390 px and 1440 px, Arabic and English). Files live in <code>docs/visual-review/shots/</code> (git-ignored; regenerate with the Playwright scripts described in the work log).</p>
{$shots}
</main>
</body>
</html>
HTML;

        File::ensureDirectoryExists(dirname(config('site-images.review_path')));
        File::put(config('site-images.review_path'), $html);
        $this->info('Wrote docs/visual-review/index.html ('.round(strlen($html) / 1024).' KB)');

        return self::SUCCESS;
    }

    private function thumbnail(string $path): string
    {
        $image = @imagecreatefromwebp($path);
        if (! $image) {
            return '';
        }
        $thumb = imagescale($image, 320);
        ob_start();
        imagejpeg($thumb, null, 70);
        $data = ob_get_clean();
        imagedestroy($image);
        imagedestroy($thumb);

        return 'data:image/jpeg;base64,'.base64_encode($data);
    }

    private function fontFace(string $family, string $prefix): string
    {
        $file = collect(glob(public_path("build/assets/{$prefix}-*.woff2")))->first();

        return $file ? "@font-face{font-family:'{$family}';src:url(data:font/woff2;base64,".base64_encode(file_get_contents($file)).") format('woff2');font-display:swap}\n" : '';
    }

    private function screenshotTable(): string
    {
        $pages = ['home', 'services', 'service-show', 'stones', 'stone-show', 'projects', 'project-show', 'blog', 'post-show', 'faq', 'about', 'contact', 'privacy', 'terms'];
        $rows = '';
        foreach ($pages as $page) {
            foreach (['ar', 'en'] as $locale) {
                foreach ([390, 1440] as $width) {
                    $name = "{$locale}-{$page}-{$width}.jpg";
                    $rows .= "<tr><th>{$page}<br>{$locale} · {$width}px</th><td><a href=\"shots/before/{$name}\"><img loading=\"lazy\" src=\"shots/before/{$name}\" alt=\"before\"></a></td><td><a href=\"shots/after/{$name}\"><img loading=\"lazy\" src=\"shots/after/{$name}\" alt=\"after\"></a></td></tr>";
                }
            }
        }

        return '<table><thead><tr><th>page</th><th>before</th><th>after</th></tr></thead><tbody>'.$rows.'</tbody></table>';
    }
}
