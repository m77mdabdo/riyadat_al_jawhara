<?php

namespace App\Http\Controllers;

use App\Enums\StoneAttributeType;
use App\Enums\StoneOriginType;
use App\Models\Project;
use App\Models\StoneAttribute;
use App\Models\StoneCategory;
use App\Models\StoneType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoneController extends Controller
{
    /**
     * Query-string filter => attribute type. Values are attribute slugs.
     */
    private const ATTRIBUTE_FILTERS = [
        'finish' => StoneAttributeType::Finish->value,
        'colour' => StoneAttributeType::Colour->value,
        'application' => StoneAttributeType::Application->value,
    ];

    public function index(Request $request): View
    {
        $query = StoneType::active()->ordered()->with(['stoneCategory', 'media']);

        if ($request->filled('stone_category')) {
            $query->whereHas('stoneCategory', fn ($q) => $q->where('slug', $request->string('stone_category')));
        }

        $active = [];

        foreach (self::ATTRIBUTE_FILTERS as $param => $type) {
            $slug = $request->string($param)->toString();
            $active[$param] = $slug;

            if ($slug !== '') {
                $query->whereHas('stoneAttributes', fn ($q) => $q->active()->ofType($type)->where('slug', $slug));
            }
        }

        $origin = $request->string('origin')->toString();
        $active['origin'] = in_array($origin, StoneOriginType::values(), true) ? $origin : '';

        if ($active['origin'] !== '') {
            $query->where('origin_type', $active['origin']);
        }

        // Only offer filter values that at least one visible stone actually has.
        $filterOptions = StoneAttribute::active()
            ->whereIn('type', self::ATTRIBUTE_FILTERS)
            ->whereHas('stoneTypes', fn ($q) => $q->active())
            ->ordered()
            ->get()
            ->groupBy('type');

        $originOptions = StoneType::active()
            ->whereNotNull('origin_type')
            ->distinct()
            ->pluck('origin_type')
            ->intersect(StoneOriginType::values())
            ->values();

        return view('stones.index', [
            'stoneTypes' => $query->get(),
            'categories' => $categories = StoneCategory::ordered()->with('media')->get(),
            'activeCategoryModel' => $categories->firstWhere('slug', $request->string('stone_category')->toString()),
            'activeCategory' => $request->string('stone_category')->toString(),
            'attributeFilters' => self::ATTRIBUTE_FILTERS,
            'filterOptions' => $filterOptions,
            'originOptions' => $originOptions,
            'activeFilters' => $active,
        ]);
    }

    public function show(StoneType $stoneType): View
    {
        abort_unless($stoneType->is_active, 404);

        $stoneType->load(['stoneCategory', 'stoneAttributes', 'media']);

        return view('stones.show', [
            'stoneType' => $stoneType,
            'projects' => Project::active()->usingStone($stoneType->id)->ordered()->with('media')->take(6)->get(),
        ]);
    }
}
