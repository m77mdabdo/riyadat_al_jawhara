<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Lang;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * The admin panel is fully translatable: no hard-coded UI strings in
 * app/Filament or the admin views, and every string they pass to __()
 * exists in Arabic (lang/ar.json, or both lang/*\/admin.php for dotted keys).
 */
class AdminTranslationCoverageTest extends TestCase
{
    private const UI_METHODS = 'label|heading|description|helperText|placeholder|hint|title|tooltip|modalHeading|modalDescription|modalSubmitActionLabel|emptyStateHeading|emptyStateDescription|successNotificationTitle|badgeTooltip|body';

    /**
     * @return array<string, string> path => contents
     */
    private function sources(): array
    {
        $files = [];
        foreach ((new Finder)->files()->in([app_path('Filament'), resource_path('views/filament')])->name(['*.php']) as $file) {
            $files[$file->getRelativePathname()] = $file->getContents();
        }

        return $files;
    }

    public function test_no_hard_coded_ui_strings(): void
    {
        $violations = [];
        foreach ($this->sources() as $path => $code) {
            // ->label('Literal'), Section::make('Literal') …
            preg_match_all('/->('.self::UI_METHODS.")\\(\\s*'([^']*[A-Za-z\\x{0600}-\\x{06FF}][^']*)'/u", $code, $m);
            foreach ($m[2] as $i => $literal) {
                $violations[] = "{$path}: ->{$m[1][$i]}('{$literal}')";
            }
            preg_match_all("/(?:Section|Tab|Fieldset|Stat|Step)::make\\(\\s*'([^']*[A-Z][^']*)'/", $code, $m);
            foreach ($m[1] as $literal) {
                $violations[] = "{$path}: ::make('{$literal}')";
            }
            if (str_ends_with($path, '.blade.php')) {
                $text = preg_replace(['/\{\{--.*?--\}\}/s', '/\{\{.*?\}\}/s', '/\{!!.*?!!\}/s', '/@php.*?@endphp/s', '/<[^>]+>/s', '/^\s*@\w+.*$/m'], ' ', $code);
                if (preg_match_all('/\b[A-Za-z]{3,}(?:\s+[A-Za-z]{2,})+\b/', $text, $m)) {
                    foreach ($m[0] as $literal) {
                        $violations[] = "{$path}: text '{$literal}'";
                    }
                }
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_every_admin_string_has_an_arabic_translation(): void
    {
        $arabic = json_decode(file_get_contents(lang_path('ar.json')), true);
        $missing = [];

        foreach ($this->sources() as $path => $code) {
            preg_match_all("/__\\(\\s*'((?:[^'\\\\]|\\\\.)+)'/", $code, $m);
            // Conditional keys: __($cond ? 'One' : 'Other')
            preg_match_all("/__\\(\\s*\\$[^?()]*\\?\\s*'([^']+)'\\s*:\\s*'([^']+)'\\s*\\)/", $code, $c);
            foreach (array_unique([...$m[1], ...$c[1], ...$c[2]]) as $key) {
                $key = stripslashes($key);
                if (str_ends_with($key, '.')) {
                    continue; // dynamic key prefix, e.g. 'admin.activity.events.'.$state
                }
                $dotted = preg_match('/^[a-z_]+(\.[a-z0-9_-]+)+$/', $key) === 1;

                if ($dotted) {
                    foreach (['en', 'ar'] as $locale) {
                        if (! Lang::hasForLocale($key, $locale)) {
                            $missing[] = "{$path}: {$key} ({$locale})";
                        }
                    }
                } elseif (! array_key_exists($key, $arabic)) {
                    $missing[] = "{$path}: \"{$key}\" (ar.json)";
                }
            }
        }

        $this->assertSame([], array_values(array_unique($missing)));
    }
}
