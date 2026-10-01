<?php

namespace App\Support;

use App\Enums\LeadStatus;
use App\Filament\Pages\Settings;
use App\Filament\Pages\Settings\ContactSettings;
use App\Filament\Resources\LeadResource;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\StoneTypeResource;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StoneType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Checks behind the dashboard's "Content health" list: each one counts the
 * records that need attention and links to a filtered list to fix them.
 */
class ContentHealth
{
    /** Resources with the "Missing image" / "Missing translation" filters. */
    private const RESOURCES = [
        'services' => [ServiceResource::class, Service::class],
        'stones' => [StoneTypeResource::class, StoneType::class],
        'projects' => [ProjectResource::class, Project::class],
        'posts' => [PostResource::class, Post::class],
    ];

    /**
     * @return list<array{key: string, count: int, url: string}>
     */
    public static function checks(): array
    {
        $checks = [];

        foreach (self::RESOURCES as $name => [$resource, $model]) {
            $missingImage = $model::query()->whereDoesntHave('media', fn (Builder $media) => $media->where('collection_name', 'cover'))->count();
            $checks[] = ['key' => "{$name}_without_image", 'count' => $missingImage, 'url' => $resource::getUrl('index', ['tableFilters' => ['missing_image' => ['isActive' => true]]])];

            $bases = array_values(array_filter((new $model)->localizedFields(), fn (string $base) => in_array($base.'_ar', (new $model)->getFillable(), true)));
            $missingTranslation = $resource::whereMissingTranslation($model::query(), $bases)->count();
            $checks[] = ['key' => "{$name}_missing_translation", 'count' => $missingTranslation, 'url' => $resource::getUrl('index', ['tableFilters' => ['missing_translation' => ['isActive' => true]]])];
        }

        $checks[] = ['key' => 'posts_without_excerpt', 'count' => Post::query()->where('is_published', true)->where(fn (Builder $q) => $q->whereNull('excerpt_en')->orWhere('excerpt_en', '')->orWhereNull('excerpt_ar')->orWhere('excerpt_ar', ''))->count(), 'url' => PostResource::getUrl('index')];

        $checks[] = ['key' => 'leads_waiting', 'count' => Lead::query()->where('status', LeadStatus::New->value)->where('created_at', '<', Carbon::now()->subDays(2))->count(), 'url' => LeadResource::getUrl('index', ['tableFilters' => ['status' => ['value' => LeadStatus::New->value]]])];

        $settings = Setting::current();
        $checks[] = ['key' => 'contact_missing', 'count' => count(array_filter([$settings->phone, $settings->whatsapp, $settings->email], 'blank')), 'url' => Settings::getUrl()];
        $checks[] = ['key' => 'map_missing', 'count' => (int) (blank($settings->map_lat) || blank($settings->map_lng)), 'url' => ContactSettings::getUrl()];

        return $checks;
    }
}
