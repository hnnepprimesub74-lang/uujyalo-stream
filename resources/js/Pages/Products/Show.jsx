import { useMemo, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import ProductCard from '@/Components/ProductCard';
import PlanCompareCard from '@/Components/PlanCompareCard';
import FaqAccordion from '@/Components/FaqAccordion';
import TrendingList from '@/Components/TrendingList';
import PerksGrid from '@/Components/PerksGrid';
import { getAccentColor, contrastText } from '@/utils/theme';

export default function Show({ product, relatedProducts }) {
    const { auth } = usePage().props;

    const accent = getAccentColor(product);
    const accentText = contrastText(accent);

    const tiers = useMemo(() => {
        const groups = new Map();
        product.plans.forEach((plan) => {
            const key = plan.type ?? plan.name;
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(plan);
        });
        return groups;
    }, [product.plans]);

    const tierNames = [...tiers.keys()];
    const firstTier = tierNames[0];

    const requestedTier = useMemo(() => {
        const param = new URLSearchParams(window.location.search).get('tier');
        return param && tiers.has(param) ? param : null;
    }, [tiers]);

    const initialTier = requestedTier ?? firstTier;
    const firstPlan = tiers.get(initialTier)[0];

    const [selectedTier, setSelectedTier] = useState(initialTier);
    const [selectedPlan, setSelectedPlan] = useState(firstPlan.id);
    const [submitting, setSubmitting] = useState(false);

    const plansInSelectedTier = [...(tiers.get(selectedTier) ?? [])].sort(
        (a, b) => a.duration_days - b.duration_days
    );

    const cheapestPrice = [...product.plans].sort((a, b) => a.price - b.price)[0].price;

    const handleOrder = (e) => {
        e.preventDefault();
        setSubmitting(true);
        router.post(route('subscriptions.subscribe', selectedPlan), {}, {
            onFinish: () => setSubmitting(false),
        });
    };

    return (
        <CustomerLayout title={product.name}>
            <div className="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-start">
                <div className="lg:sticky lg:top-20">
                    <div className="aspect-square rounded-2xl bg-neutral-800 flex items-center justify-center mb-4 lg:mb-0 overflow-hidden">
                        {product.image_url ? (
                            <img src={product.image_url} alt={product.name} className="w-full h-full object-cover" />
                        ) : (
                            <span className="text-6xl font-extrabold text-neutral-600">{product.name.substring(0, 1)}</span>
                        )}
                    </div>
                </div>

                <div>
                    <h1 className="text-2xl font-extrabold">{product.name}</h1>
                    <p className="text-emerald-400 text-sm font-semibold mt-1">In Stock</p>

                    <div className="mt-6">
                        {tierNames.length > 1 && (
                            <>
                                <p className="text-xs font-bold tracking-wider text-neutral-400 mb-2">SELECT TIER</p>
                                <div className="flex flex-wrap gap-2 mb-5">
                                    {tierNames.map((tierName) => (
                                        <button
                                            key={tierName}
                                            type="button"
                                            onClick={() => {
                                                setSelectedTier(tierName);
                                                setSelectedPlan(tiers.get(tierName)[0].id);
                                            }}
                                            style={selectedTier === tierName ? { backgroundColor: accent, color: accentText } : undefined}
                                            className={`glass-btn-base px-4 py-2 rounded-full text-sm font-bold ${
                                                selectedTier === tierName
                                                    ? 'border border-white/20'
                                                    : 'glass-panel text-neutral-300'
                                            }`}
                                        >
                                            {tierName}
                                        </button>
                                    ))}
                                </div>
                            </>
                        )}

                        <form onSubmit={handleOrder}>
                            <p className="text-xs font-bold tracking-wider text-neutral-400 mb-2">SELECT DURATION</p>

                            <div className="space-y-2">
                                {plansInSelectedTier.map((plan) => (
                                    <label
                                        key={plan.id}
                                        style={selectedPlan === plan.id ? { borderColor: accent } : undefined}
                                        className={`flex items-center justify-between gap-3 rounded-xl p-4 cursor-pointer backdrop-blur-xl backdrop-saturate-150 shadow-glass-sm transition duration-200 ease-glass ${
                                            selectedPlan === plan.id
                                                ? 'border bg-white/[0.06]'
                                                : 'border border-white/10 bg-white/[0.04] hover:bg-white/[0.06]'
                                        }`}
                                    >
                                        <span className="flex items-center gap-3">
                                            <input
                                                type="radio"
                                                name="plan_display"
                                                checked={selectedPlan === plan.id}
                                                onChange={() => setSelectedPlan(plan.id)}
                                                style={{ accentColor: accent }}
                                                className="w-5 h-5 bg-neutral-800 border-neutral-600 focus:ring-offset-0"
                                            />
                                            <span className="font-semibold text-sm">{plan.duration_days} Days</span>
                                        </span>
                                        <span className="text-green-400 font-bold">
                                            NPR {Number(plan.price).toLocaleString()}
                                        </span>
                                    </label>
                                ))}
                            </div>

                            {auth?.user ? (
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    style={{ backgroundColor: accent, color: accentText }}
                                    className="glass-btn-base w-full mt-6 border border-white/20 font-bold rounded-xl py-3.5 flex items-center justify-center gap-2 disabled:opacity-60"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Order Now
                                </button>
                            ) : (
                                <Link
                                    href={route('login')}
                                    style={{ backgroundColor: accent, color: accentText }}
                                    className="glass-btn-base w-full mt-6 border border-white/20 font-bold rounded-xl py-3.5 flex items-center justify-center gap-2"
                                >
                                    Sign In to Order
                                </Link>
                            )}

                            <p className="text-xs text-neutral-500 text-center mt-3">
                                By ordering, you agree that payment will be reviewed and your account activated within a short time.
                            </p>
                        </form>
                    </div>

                    {tierNames.length > 0 && (
                        <div className="mt-8">
                            <h2 className="font-extrabold tracking-tight text-lg mb-3">Choose Your Plan</h2>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {plansInSelectedTier.map((plan) => (
                                    <PlanCompareCard
                                        key={plan.id}
                                        plan={plan}
                                        selected={selectedPlan === plan.id}
                                        onSelect={() => setSelectedPlan(plan.id)}
                                    />
                                ))}
                            </div>

                            {product.plan_guidance && (
                                <div className="mt-4">
                                    <h2 className="font-extrabold tracking-tight text-lg mb-3">Which Plan Should You Choose?</h2>
                                    <div className="rounded-xl border-l-4 border-emerald-400 bg-emerald-400/5 p-4 text-sm text-neutral-300 whitespace-pre-line">
                                        {product.plan_guidance}
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {product.trending_items?.length > 0 && (
                        <div className="mt-8">
                            <TrendingList items={product.trending_items} />
                        </div>
                    )}

                    {product.perks?.length > 0 && (
                        <div className="mt-8">
                            <h2 className="font-extrabold tracking-tight text-lg mb-3">More Reasons To Join</h2>
                            <PerksGrid perks={product.perks} accent={accent} />
                        </div>
                    )}

                    {(product.description || product.category) && (
                        <div className="mt-8">
                            <h2 className="font-extrabold tracking-tight text-lg mb-3">Description</h2>
                            <div className="rounded-xl glass-panel p-4">
                                {product.category && (
                                    <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-[11px] font-bold tracking-wide">
                                        <span className="w-1.5 h-1.5 rounded-full bg-emerald-400" />
                                        {product.category.toUpperCase()}
                                    </span>
                                )}

                                <p className="text-lg font-extrabold mt-3">
                                    {product.name} in Nepal — From NPR {Number(cheapestPrice).toLocaleString()}
                                </p>

                                <div className="grid grid-cols-3 gap-3 mt-4 pt-4 border-t border-white/10 text-xs">
                                    <div>
                                        <p className="text-neutral-500 font-semibold tracking-wide">PLANS</p>
                                        <p className="font-bold mt-0.5">{product.plans.length}</p>
                                    </div>
                                    <div>
                                        <p className="text-neutral-500 font-semibold tracking-wide">STARTING PRICE</p>
                                        <p className="font-bold mt-0.5">NPR {Number(cheapestPrice).toLocaleString()}</p>
                                    </div>
                                    <div>
                                        <p className="text-neutral-500 font-semibold tracking-wide">PAYMENT</p>
                                        <p className="font-bold mt-0.5">eSewa / Khalti</p>
                                    </div>
                                </div>

                                {product.description && (
                                    <p className="text-sm text-neutral-300 mt-4 pt-4 border-t border-white/10 whitespace-pre-line">
                                        {product.description}
                                    </p>
                                )}
                            </div>

                            {product.highlight_note && (
                                <div className="mt-3 rounded-xl border-l-4 border-emerald-400 bg-emerald-400/5 p-4 text-sm text-neutral-300">
                                    {product.highlight_note}
                                </div>
                            )}
                        </div>
                    )}

                    {product.external_link_url && (
                        <a
                            href={product.external_link_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="glass-btn-base mt-4 flex items-center justify-center gap-2 rounded-xl glass-panel hover:bg-white/[0.07] text-sm font-semibold text-neutral-200 py-3"
                        >
                            {product.external_link_label || 'Visit Website'}
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    )}

                    {product.faqs?.length > 0 && (
                        <div className="mt-8">
                            <h2 className="font-extrabold tracking-tight text-lg mb-3">Frequently Asked Questions</h2>
                            <FaqAccordion faqs={product.faqs} />
                        </div>
                    )}
                </div>
            </div>

            {/* Trust badges */}
            <div className="mt-8 grid grid-cols-2 lg:grid-cols-4 gap-2">
                <div className="flex items-center gap-2 rounded-xl glass-panel p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-emerald-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span className="text-xs font-semibold text-neutral-300">Secure Payment</span>
                </div>
                <div className="flex items-center gap-2 rounded-xl glass-panel p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-brand-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span className="text-xs font-semibold text-neutral-300">Fast Delivery</span>
                </div>
                <div className="flex items-center gap-2 rounded-xl glass-panel p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-emerald-400 flex-shrink-0" viewBox="0 0 32 32" fill="currentColor"><path d="M16.001 3C9.373 3 4 8.373 4 15c0 2.386.7 4.607 1.908 6.47L4 29l7.73-1.878A11.94 11.94 0 0016 27c6.627 0 12-5.373 12-12S22.628 3 16.001 3z"/></svg>
                    <span className="text-xs font-semibold text-neutral-300">WhatsApp Support</span>
                </div>
                <div className="flex items-center gap-2 rounded-xl glass-panel p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-sky-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M2 10h20M6 15h2m2 0h2M4 6h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                    <span className="text-xs font-semibold text-neutral-300">eSewa &amp; Khalti</span>
                </div>
            </div>

            {relatedProducts.length > 0 && (
                <div className="mt-8">
                    <h2 className="font-extrabold tracking-tight text-lg mb-3">You Might Also Like</h2>
                    <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        {relatedProducts.map((related) => (
                            <ProductCard key={related.id} product={related} />
                        ))}
                    </div>
                </div>
            )}
        </CustomerLayout>
    );
}
