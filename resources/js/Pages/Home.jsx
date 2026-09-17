import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import PlanTierCard from '@/Components/PlanTierCard';
import BannerSlider from '@/Components/BannerSlider';

export default function Home({ products, banners }) {
    const [search] = useState(
        () => new URLSearchParams(window.location.search).get('search') ?? ''
    );

    const tiles = useMemo(() => {
        return products.flatMap((product) => {
            const groups = new Map();
            product.plans.forEach((plan) => {
                const key = plan.type ?? plan.name;
                if (!groups.has(key)) groups.set(key, []);
                groups.get(key).push(plan);
            });

            const tierNames = [...groups.keys()];

            if (tierNames.length <= 1) {
                return [{ key: `${product.id}`, product, tier: null, plans: product.plans }];
            }

            return tierNames.map((tierName) => ({
                key: `${product.id}-${tierName}`,
                product,
                tier: tierName,
                plans: groups.get(tierName),
            }));
        });
    }, [products]);

    const visibleTiles = tiles.filter(
        ({ product }) => !search || product.name.toLowerCase().includes(search.toLowerCase())
    );

    return (
        <CustomerLayout title="Home">
            {search && (
                <div className="flex items-center justify-between gap-3 mb-5 rounded-xl glass-panel px-4 py-3">
                    <p className="text-sm text-neutral-300">
                        Search results for <span className="font-bold text-neutral-100">"{search}"</span>
                    </p>
                    <Link href={route('home')} className="text-xs font-semibold text-brand-400 hover:text-brand-300 flex-shrink-0">
                        Clear
                    </Link>
                </div>
            )}

            {!search && <BannerSlider banners={banners} />}

            <h2 className="font-extrabold tracking-tight mb-3">All Products</h2>

            <div className="flex flex-wrap gap-3">
                {visibleTiles.length === 0 ? (
                    <p className="w-full text-center text-neutral-500 py-12">
                        {search ? `No products match "${search}".` : 'No products available right now.'}
                    </p>
                ) : (
                    visibleTiles.map(({ key, product, tier, plans }) => (
                        <PlanTierCard key={key} product={product} tier={tier} plans={plans} />
                    ))
                )}
            </div>
        </CustomerLayout>
    );
}
