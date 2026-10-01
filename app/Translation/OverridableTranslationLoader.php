<?php

namespace App\Translation;

use App\Support\SiteTexts;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Arr;

/**
 * Decorates the file loader: lang/{locale}/site.php as shipped, with the
 * admin's overrides (App\Support\SiteTexts) merged over string entries.
 */
class OverridableTranslationLoader implements Loader
{
    public function __construct(private Loader $files) {}

    /**
     * @return array<array-key, mixed>
     */
    public function load($locale, $group, $namespace = null)
    {
        $lines = $this->files->load($locale, $group, $namespace);

        if ($group !== SiteTexts::GROUP || ! in_array($namespace, [null, '*'], true)) {
            return $lines;
        }

        foreach (SiteTexts::overrides($locale) as $key => $value) {
            if (is_string(Arr::get($lines, $key))) {
                Arr::set($lines, $key, $value);
            }
        }

        return $lines;
    }

    public function addNamespace($namespace, $hint)
    {
        $this->files->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->files->addJsonPath($path);
    }

    /**
     * @return array<string, string>
     */
    public function namespaces()
    {
        return $this->files->namespaces();
    }
}
