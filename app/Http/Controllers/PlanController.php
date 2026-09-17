<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
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

        return Inertia::render('Products/Show', compact('product', 'relatedProducts'));
    }
}
