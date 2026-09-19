<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use App\Models\Review;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with(['plans' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->get()
            ->filter(fn (Product $product) => $product->plans->isNotEmpty())
            ->values();

        $banners = Banner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Home', compact('products', 'banners'));
    }

    public function show(Product $product): Response
    {
        abort_unless($product->is_active, 404);

        $product->load(['plans' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')]);

        abort_if($product->plans->isEmpty(), 404);

        $relatedProducts = Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->with(['plans' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->get()
            ->filter(fn (Product $related) => $related->plans->isNotEmpty())
            ->sortByDesc(fn (Product $related) => $related->category === $product->category)
            ->take(4)
            ->values();

        $reviewsQuery = Review::where('product_id', $product->id);

        $reviewsCount = (clone $reviewsQuery)->count();
        $reviewsAvg = $reviewsCount > 0 ? round((clone $reviewsQuery)->avg('rating'), 1) : null;

        $reviews = $reviewsQuery
            ->with('user')
            ->latest()
            ->paginate(5)
            ->through(fn (Review $review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'reviewer_name' => $review->user->name,
                'created_at' => $review->created_at->format('M j, Y'),
            ]);

        return Inertia::render('Products/Show', compact('product', 'relatedProducts', 'reviews', 'reviewsCount', 'reviewsAvg'));
    }
}
