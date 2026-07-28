<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Project;
use App\Models\Service;
use App\Models\Slider;
use App\Models\StoneType;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'sliders' => Slider::active()->ordered()->get(),
            'services' => Service::active()->ordered()->get(),
            'featuredProjects' => Project::active()->featured()->ordered()->take(6)->get(),
            'stoneTypes' => StoneType::active()->ordered()->take(8)->get(),
            'testimonials' => Testimonial::active()->ordered()->with('project')->get(),
            'faqs' => Faq::active()->ordered()->get(),
        ]);
    }
}
