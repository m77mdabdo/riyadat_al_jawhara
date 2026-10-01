<?php

namespace App\View\Composers;

use App\Support\Navigation;
use Illuminate\View\View;

class NavigationComposer
{
    public function compose(View $view): void
    {
        if ($view->name() === 'partials.footer') {
            $view->with('footerColumns', Navigation::footerColumns());

            return;
        }

        $view->with([
            'navLinks' => Navigation::main(),
            'mobileLinks' => Navigation::mobile(),
            'headerOptions' => Navigation::header(),
        ]);
    }
}
