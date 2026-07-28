<?php

namespace App\Http\Controllers;

use App\Models\StoneCategory;
use App\Models\StoneType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoneController extends Controller
{
    public function index(Request $request): View
    {
        $query = StoneType::active()->ordered()->with('stoneCategory');

        if ($request->filled('stone_category')) {
            $query->whereHas('stoneCategory', fn ($q) => $q->where('slug', $request->string('stone_category')));
        }

        return view('stones.index', [
            'stoneTypes' => $query->get(),
            'categories' => StoneCategory::ordered()->get(),
            'activeCategory' => $request->string('stone_category')->toString(),
        ]);
    }

    public function show(StoneType $stoneType): View
    {
        abort_unless($stoneType->is_active, 404);

        return view('stones.show', [
            'stoneType' => $stoneType,
            'projects' => $stoneType->projects()->active()->ordered()->take(6)->get(),
        ]);
    }
}
