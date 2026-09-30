<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Models\Testimonial;
use App\Support\FragmentCache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Everything below the hero is visitor-independent: render it once per
        // locale and reuse it until content changes (FragmentCache::flush()).
        $sections = FragmentCache::remember('home.sections', fn () => view('home.sections', [
            'services' => Service::active()->ordered()->with('media')->get(),
            'featuredProjects' => Project::active()->featured()->ordered()->with('media')->take(6)->get(),
            'stoneTypes' => StoneType::active()->ordered()->with('media')->take(8)->get(),
            'testimonials' => Testimonial::active()->ordered()->with('media')->get(),
            'faqs' => Faq::active()->ordered()->take(4)->get(),
            'stoneCategories' => StoneCategory::ordered()->with('media')
                ->withCount(['stoneTypes' => fn ($query) => $query->active()])
                ->get(),
            'settings' => Setting::current(),
        ])->render());

        return view('home', [
            'sliders' => Slider::active()->ordered()->with('media')->get(),
            'settings' => Setting::current(),
            'sections' => $sections,
        ]);
    }
}
