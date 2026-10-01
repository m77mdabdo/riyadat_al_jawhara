<?php

namespace App\Models;

use App\Enums\MenuLinkType;
use App\Models\Concerns\HasLocalizedAttributes;
use App\Models\Concerns\LogsAdminActivity;
use App\Models\Contracts\HasPublicUrl;
use App\Support\LocalizedUrl;
use App\Support\Navigation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $menu_id
 * @property int|null $parent_id
 * @property MenuLinkType $link_type
 * @property string|null $route_name
 * @property string|null $url
 * @property bool $open_in_new_tab
 * @property bool $is_active
 * @property string|null $icon
 * @property-read string|null $label
 */
class MenuItem extends Model
{
    use HasLocalizedAttributes, LogsAdminActivity;

    /** @var array<string, string|null> */
    protected array $localized = ['label' => null];

    /** Icons an item may show before its label. */
    public const ICONS = [
        'heroicon-o-home', 'heroicon-o-cube', 'heroicon-o-squares-2x2', 'heroicon-o-photo', 'heroicon-o-newspaper',
        'heroicon-o-question-mark-circle', 'heroicon-o-information-circle', 'heroicon-o-phone', 'heroicon-o-envelope',
        'heroicon-o-map-pin', 'heroicon-o-document-arrow-down', 'heroicon-o-star', 'heroicon-o-sparkles',
    ];

    protected $fillable = [
        'menu_id', 'parent_id', 'label_ar', 'label_en', 'link_type', 'route_name',
        'linkable_type', 'linkable_id', 'url', 'open_in_new_tab', 'icon', 'is_active', 'order',
    ];

    protected $casts = [
        'link_type' => MenuLinkType::class,
        'open_in_new_tab' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'link_type' => 'route',
        'open_in_new_tab' => false,
        'is_active' => true,
        'order' => 0,
    ];

    /** @return BelongsTo<Menu, $this> */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /** @return BelongsTo<MenuItem, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /** @return HasMany<MenuItem, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /** @return MorphTo<Model, $this> */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Address of the item in $locale, or null when its target is gone or
     * hidden (the item is then left out of the menu).
     */
    public function href(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return match ($this->link_type) {
            MenuLinkType::Route => array_key_exists((string) $this->route_name, Navigation::routes())
                ? route($this->route_name, ['locale' => $locale])
                : null,
            MenuLinkType::Record => $this->linkable instanceof HasPublicUrl ? $this->linkable->publicUrl($locale) : null,
            MenuLinkType::Url => filled($this->url) ? LocalizedUrl::link($this->url, $locale) : null,
        };
    }
}
