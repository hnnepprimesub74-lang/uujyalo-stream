<?php

use App\Http\Controllers\CouponController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketingUnsubscribeController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [PlanController::class, 'index'])->name('home');
Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
Route::get('/products/{product:slug}', [PlanController::class, 'show'])->name('products.show');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
Route::get('/about', fn () => Inertia::render('About'))->name('about');

Route::get('/marketing/unsubscribe/{user}', [MarketingUnsubscribeController::class, 'unsubscribe'])
    ->middleware('signed')
    ->name('marketing.unsubscribe');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/plans/{plan}/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::get('/subscriptions/{subscription}/details', [SubscriptionController::class, 'details'])->name('subscriptions.details');
    Route::post('/subscriptions/{subscription}/details', [SubscriptionController::class, 'storeDetails'])->name('subscriptions.storeDetails');
    Route::get('/subscriptions/{subscription}/pay', [SubscriptionController::class, 'pay'])->name('subscriptions.pay');
    Route::post('/subscriptions/{subscription}/proof', [SubscriptionController::class, 'storeProof'])->name('subscriptions.storeProof');
    Route::post('/subscriptions/{subscription}/coupon', [CouponController::class, 'store'])->name('subscriptions.coupon.store');
    Route::delete('/subscriptions/{subscription}/coupon', [CouponController::class, 'destroy'])->name('subscriptions.coupon.destroy');
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    Route::post('/subscriptions/{subscription}/review', [ReviewController::class, 'store'])->name('subscriptions.review');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

require __DIR__.'/auth.php';
