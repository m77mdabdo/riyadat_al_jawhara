<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StoneController;
use Illuminate\Support\Facades\Route;

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

Route::get('/about', [AboutController::class, 'index'])->name('about');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/privacy-policy', [LegalController::class, 'privacyPolicy'])->name('legal.privacy');
Route::get('/terms-of-use', [LegalController::class, 'termsOfUse'])->name('legal.terms');

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// A fallback route (rather than relying on Laravel's default no-match handling)
// ensures unmatched URLs still pass through the 'web' middleware group -- most
// importantly SetLocale -- so the 404 page respects the visitor's chosen locale.
Route::fallback(function () {
    abort(404);
});
