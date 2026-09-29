<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FallbackController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StoneController;
use App\Http\Controllers\WhatsappClickController;
use Illuminate\Support\Facades\Route;

/*
| Public site: every page lives under /{locale} (ar|en), Arabic by default.
| Slugs are shared between languages. SetLocale reads the prefix.
*/
Route::get('/', fn () => redirect('/'.config('locales.default'), 301))->name('root');

Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('locales.supported'))])
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');

        Route::get('/stones', [StoneController::class, 'index'])->name('stones.index');
        Route::get('/stones/{stoneType:slug}', [StoneController::class, 'show'])->name('stones.show');

        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');

        Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
        Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
        Route::post('/blog/{post:slug}/comments', [BlogController::class, 'storeComment'])
            ->middleware('throttle:5,1')
            ->name('blog.comments.store');

        Route::get('/faq', [FaqController::class, 'index'])->name('faq');

        Route::get('/about', [AboutController::class, 'index'])->name('about');

        Route::get('/contact', [ContactController::class, 'index'])->name('contact');
        Route::post('/contact', [ContactController::class, 'store'])
            ->middleware('throttle:contact')
            ->name('contact.store');
        Route::get('/contact/thank-you', [ContactController::class, 'thanks'])->name('contact.thanks');

        Route::get('/privacy-policy', [LegalController::class, 'privacyPolicy'])->name('legal.privacy');
        Route::get('/terms-of-use', [LegalController::class, 'termsOfUse'])->name('legal.terms');
    });

/*
| Un-prefixed routes.
*/
// Legacy session-based switcher: now just redirects to the new-style URL.
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Anonymous WhatsApp-button click beacon (see WhatsappClickController).
Route::post('/whatsapp-click', WhatsappClickController::class)
    ->middleware('throttle:beacons')
    ->name('whatsapp.click');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Legacy un-prefixed URLs (e.g. /services/x) 301 to /ar/services/x; anything
// else is a 404 rendered in the language of the URL prefix. Being a route (not
// the framework's no-match handling) keeps it inside the 'web' middleware group.
Route::fallback(FallbackController::class);
