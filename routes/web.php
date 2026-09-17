<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [PlanController::class, 'index'])->name('home');
Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
Route::get('/products/{product:slug}', [PlanController::class, 'show'])->name('products.show');
Route::get('/reviews', fn () => Inertia::render('Reviews'))->name('reviews');
Route::get('/about', fn () => Inertia::render('About'))->name('about');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/plans/{plan}/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::get('/subscriptions/{subscription}/details', [SubscriptionController::class, 'details'])->name('subscriptions.details');
    Route::post('/subscriptions/{subscription}/details', [SubscriptionController::class, 'storeDetails'])->name('subscriptions.storeDetails');
    Route::get('/subscriptions/{subscription}/pay', [SubscriptionController::class, 'pay'])->name('subscriptions.pay');
    Route::post('/subscriptions/{subscription}/proof', [SubscriptionController::class, 'storeProof'])->name('subscriptions.storeProof');
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
