<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/{page}', [SiteController::class, 'page'])->whereIn('page', ['about', 'markets', 'team', 'contact', 'privacy-policy', 'terms'])->name('page');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
Route::get('/robots.txt', [SeoController::class, 'robots']);

Route::redirect('/dashboard', '/admin')->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::put('/settings', [DashboardController::class, 'settings'])->name('settings');
    Route::post('/markets', [DashboardController::class, 'marketStore'])->name('markets.store');
    Route::put('/markets/{market}', [DashboardController::class, 'marketUpdate'])->name('markets.update');
    Route::delete('/markets/{market}', [DashboardController::class, 'marketDestroy'])->name('markets.destroy');
    Route::post('/team', [DashboardController::class, 'teamStore'])->name('team.store');
    Route::put('/team/{team_member}', [DashboardController::class, 'teamUpdate'])->name('team.update');
    Route::delete('/team/{team_member}', [DashboardController::class, 'teamDestroy'])->name('team.destroy');
    Route::patch('/contacts/{contact}/{status}', [DashboardController::class, 'contactStatus'])->name('contacts.status');
    Route::post('/media', [MediaController::class, 'store'])->middleware('throttle:20,1')->name('media.store');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
});

require __DIR__.'/auth.php';
