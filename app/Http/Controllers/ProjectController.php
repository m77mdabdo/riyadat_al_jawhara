<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::active()->ordered()->with('media');

        if ($request->filled('service')) {
            $query->whereHas('service', fn ($q) => $q->where('slug', $request->string('service')));
        }

        if ($request->filled('stone_type')) {
            $stoneTypeId = StoneType::where('slug', $request->string('stone_type'))->value('id');
            // Unknown slug => no results (rather than silently ignoring the filter).
            $query->usingStone((int) $stoneTypeId);
        }

        $sector = $request->string('sector')->toString();

        if (in_array($sector, Project::SECTORS, true)) {
            $query->where('sector', $sector);
        } else {
            $sector = '';
        }

        return view('projects.index', [
            'projects' => $query->paginate(9)->withQueryString(),
            'services' => Service::active()->ordered()->get(),
            'stoneTypes' => StoneType::active()->ordered()->get(),
            'activeService' => $request->string('service')->toString(),
            'activeStoneType' => $request->string('stone_type')->toString(),
            'sectors' => Project::active()->whereNotNull('sector')->distinct()->pluck('sector')->intersect(Project::SECTORS)->values(),
            'activeSector' => $sector,
        ]);
    }

    public function show(Project $project): View
    {
        abort_unless($project->is_active, 404);

        $project->load(['service', 'stoneType', 'stoneTypes', 'media']);

        return view('projects.show', [
            'project' => $project,
        ]);
    }
}
