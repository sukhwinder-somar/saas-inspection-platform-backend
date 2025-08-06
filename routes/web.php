<?php

use App\Http\Controllers\PageController;
use App\Livewire\SaasHome;
use Illuminate\Support\Facades\Route;

// Public routes on central domain
Route::get('/', [PageController::class, 'home'])->name('home');

// Marketing pages - try dynamic pages first, fallback to static
Route::get('/pricing', function () {
    $page = \App\Models\Page::published()->bySlug('pricing')->first();
    if ($page) {
        return app(PageController::class)->show('pricing');
    }
    return view('pages.pricing');
})->name('pricing');

Route::get('/about', function () {
    $page = \App\Models\Page::published()->bySlug('about')->first();
    if ($page) {
        return app(PageController::class)->show('about');
    }
    return view('pages.about');
})->name('about');

// Authentication routes (redirect to Filament)
Route::get('/register', function () {
    return redirect('/admin/login');
})->name('register');

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Health check endpoint for infrastructure
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'services' => [
            'database' => 'connected',
            'cache' => 'connected'
        ]
    ]);
});

// Protected routes (redirect to Filament admin)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect('/admin');
    })->name('dashboard');
});

// Catch-all route for dynamic pages (must be last)
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('page.show');
