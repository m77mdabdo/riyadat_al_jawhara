<?php

namespace App\View\Composers;

use App\Support\Navigation;
use Illuminate\View\View;

class NavigationComposer
{
    public function compose(View $view): void
    {
        $view->with('navLinks', Navigation::main());
    }
}
