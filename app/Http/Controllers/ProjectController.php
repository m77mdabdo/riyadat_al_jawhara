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
        $query = Project::active()->ordered()->with(['service', 'stoneType']);

        if ($request->filled('service')) {
            $query->whereHas('service', fn ($q) => $q->where('slug', $request->string('service')));
        }

        if ($request->filled('stone_type')) {
            $query->whereHas('stoneType', fn ($q) => $q->where('slug', $request->string('stone_type')));
        }

        return view('projects.index', [
            'projects' => $query->paginate(9)->withQueryString(),
            'services' => Service::active()->ordered()->get(),
            'stoneTypes' => StoneType::active()->ordered()->get(),
            'activeService' => $request->string('service')->toString(),
            'activeStoneType' => $request->string('stone_type')->toString(),
        ]);
    }

    public function show(Project $project): View
    {
        abort_unless($project->is_active, 404);

        return view('projects.show', [
            'project' => $project,
        ]);
    }
}
