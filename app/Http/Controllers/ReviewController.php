<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(): Response
    {
        $reviews = Review::query()
            ->with(['user', 'product'])
            ->latest()
            ->paginate(5)
            ->through(fn (Review $review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'reviewer_name' => $review->user->name,
                'product_name' => $review->product->name,
                'product_slug' => $review->product->slug,
                'created_at' => $review->created_at->format('M j, Y'),
            ]);

        return Inertia::render('Reviews', compact('reviews'));
    }

    public function store(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);
        abort_unless(in_array($subscription->status, [Subscription::STATUS_ACTIVE, Subscription::STATUS_EXPIRED], true), 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        Review::updateOrCreate(
            ['subscription_id' => $subscription->id],
            [
                'user_id' => $request->user()->id,
                'product_id' => $subscription->plan->product_id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );

        return Redirect::route('dashboard')->with('status', 'Thanks for your review!');
    }
}
