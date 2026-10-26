<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\StoneCategory;
use App\Models\StoneType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * One filterable gallery across project galleries and stone galleries.
 * Only media whose project/stone is active is shown.
 */
class GalleryController extends Controller
{
    public const TYPES = ['projects' => Project::class, 'stones' => StoneType::class];

    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $type = array_key_exists($type, self::TYPES) ? $type : '';
        $category = $request->string('stone_category')->toString();

        $models = $type ? [self::TYPES[$type]] : array_values(self::TYPES);

        $media = Media::query()
            ->where('collection_name', 'gallery')
            ->whereHasMorph('model', $models, function (Builder $query, string $model) use ($category) {
                $query->where('is_active', true);

                if ($category !== '' && $model === StoneType::class) {
                    $query->whereHas('stoneCategory', fn (Builder $q) => $q->where('slug', $category));
                }
                if ($category !== '' && $model === Project::class) {
                    $query->whereHas('stoneType.stoneCategory', fn (Builder $q) => $q->where('slug', $category));
                }
            })
            ->with('model')
            ->orderByDesc('id')
            ->paginate(24)
            ->withQueryString();

        return view('gallery.index', [
            'media' => $media,
            'type' => $type,
            'categories' => StoneCategory::ordered()->get(),
            'activeCategory' => $category,
        ]);
    }
}
