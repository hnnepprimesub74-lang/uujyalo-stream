import { Link } from '@inertiajs/react';

export default function ProductCard({ product, tier, plans }) {
    const cheapest = [...(plans ?? product.plans)].sort((a, b) => a.price - b.price)[0];
    const displayName = tier ? `${product.name} ${tier}` : product.name;
    const href = tier
        ? `${route('products.show', product.slug)}?tier=${encodeURIComponent(tier)}`
        : route('products.show', product.slug);

    return (
        <Link
            href={href}
            className="group glass-panel rounded-2xl overflow-hidden hover:border-brand-400/50 hover:-translate-y-0.5 transition duration-200 ease-glass"
        >
            <div className="aspect-square bg-neutral-800 flex items-center justify-center overflow-hidden relative">
                {product.category && (
                    <span className="absolute top-2 left-2 z-10 px-2 py-0.5 rounded-full bg-neutral-950/70 text-[10px] font-bold tracking-wide text-neutral-200">
                        {product.category}
                    </span>
                )}
                {product.image_url ? (
                    <img
                        src={product.image_url}
                        alt={displayName}
                        className="w-full h-full object-cover"
                        loading="lazy"
                    />
                ) : (
                    <span className="text-4xl font-extrabold text-neutral-600 group-hover:text-brand-400/80 transition">
                        {product.name.substring(0, 1)}
                    </span>
                )}
            </div>
            <div className="p-3">
                <p className="font-semibold text-sm truncate">{displayName}</p>
                <div className="flex items-center justify-between mt-1">
                    {cheapest ? (
                        <p className="text-xs text-neutral-400">
                            From{' '}
                            <span className="text-green-400 font-bold">
                                NPR {Number(cheapest.price).toLocaleString()}
                            </span>
                        </p>
                    ) : <span />}
                    <span className="w-6 h-6 rounded-full bg-neutral-800 group-hover:bg-brand-400 flex items-center justify-center text-neutral-400 group-hover:text-neutral-950 transition flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </div>
        </Link>
    );
}
