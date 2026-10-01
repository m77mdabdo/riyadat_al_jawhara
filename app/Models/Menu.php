<?php

namespace App\Models;

use App\Enums\MenuLocation;
use App\Models\Concerns\LogsAdminActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An editable public menu (header, footer, mobile). Header options (call to
 * action, language switch, search) live in `options`.
 *
 * @property MenuLocation $location
 * @property array<string, mixed>|null $options
 */
class Menu extends Model
{
    use LogsAdminActivity;

    protected $fillable = ['location', 'options'];

    protected $casts = [
        'location' => MenuLocation::class,
        'options' => 'array',
    ];

    /** @return HasMany<MenuItem, $this> top-level items, in order */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('order');
    }

    /** @return HasMany<MenuItem, $this> */
    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function option(string $key, mixed $default = null): mixed
    {
        $value = data_get($this->options, $key);

        return $value === null || $value === '' ? $default : $value;
    }
}
