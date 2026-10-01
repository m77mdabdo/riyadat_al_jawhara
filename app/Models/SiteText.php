<?php

namespace App\Models;

use App\Models\Concerns\LogsAdminActivity;
use App\Support\SiteTexts;
use Illuminate\Database\Eloquent\Model;

/**
 * One admin override of a public interface text. `key` is the full
 * translation key ("site.home.cta_title"); an empty value falls back to the
 * text in lang/{locale}/site.php.
 *
 * @property string $key
 * @property string|null $value_ar
 * @property string|null $value_en
 */
class SiteText extends Model
{
    use LogsAdminActivity;

    protected $fillable = ['key', 'value_ar', 'value_en'];

    protected static function booted(): void
    {
        static::saved(fn () => SiteTexts::flush());
        static::deleted(fn () => SiteTexts::flush());
    }
}
