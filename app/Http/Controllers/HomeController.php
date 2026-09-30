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
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'sliders' => Slider::active()->ordered()->with('media')->get(),
            'services' => Service::active()->ordered()->with('media')->get(),
            'featuredProjects' => Project::active()->featured()->ordered()->with('media')->take(6)->get(),
            'stoneTypes' => StoneType::active()->ordered()->with('media')->take(8)->get(),
            'testimonials' => Testimonial::active()->ordered()->with('media')->get(),
            'faqs' => Faq::active()->ordered()->take(4)->get(),
            'stoneCategories' => StoneCategory::ordered()->with('media')
                ->withCount(['stoneTypes' => fn ($query) => $query->active()])
                ->get(),
            'settings' => Setting::current(),
        ]);
    }
}
