import { Link } from '@inertiajs/react';

export default function PlanTierCard({ product, tier, plans }) {
    const cheapest = [...plans].sort((a, b) => a.price - b.price)[0];
    const href = tier
        ? `${route('products.show', product.slug)}?tier=${encodeURIComponent(tier)}`
        : route('products.show', product.slug);

    const days = plans.map((p) => p.duration_days);
    const minDays = Math.min(...days);
    const maxDays = Math.max(...days);
    const daysLabel = minDays === maxDays ? `${minDays} Days` : `${minDays}–${maxDays} Days`;

    const devicePlan = plans.find((p) => p.device_slots);
    const deviceSlots = devicePlan?.device_slots;
    const deviceSlotsMax = devicePlan?.device_slots_max;
    const deviceLabel = devicePlan?.device_label || 'Device Login';
    const usersLabel = !deviceSlots
        ? 'Flexible'
        : deviceSlotsMax && deviceSlotsMax !== deviceSlots
            ? `${deviceSlots}–${deviceSlotsMax} ${deviceLabel}`
            : `${deviceSlots} ${deviceLabel}`;

    const quality = plans.find((p) => p.quality)?.quality;
    const supportedDevices = plans.find((p) => p.supported_devices)?.supported_devices;

    return (
        <Link
            href={href}
            className="group glass-panel rounded-2xl overflow-hidden hover:border-brand-400/50 hover:-translate-y-0.5 transition duration-200 ease-glass w-[calc(50%-0.375rem)] sm:w-40 lg:w-44 flex-shrink-0"
        >
            <div className="aspect-square bg-neutral-800 flex items-center justify-center overflow-hidden relative">
                {tier && (
                    <span className="absolute top-2 left-2 z-10 px-2.5 py-1 rounded-full bg-brand-500 shadow-glass-sm text-xs font-bold tracking-wide text-white">
                        {tier}
                    </span>
                )}
                {product.image_url ? (
                    <img
                        src={product.image_url}
                        alt={`${product.name} ${tier ?? ''}`}
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
                <p className="font-semibold text-sm truncate">{product.name}</p>
                <p className="text-xs text-neutral-400 mt-0.5">
                    From{' '}
                    <span className="text-green-400 font-bold text-sm">
                        NPR {Number(cheapest.price).toLocaleString()}
                    </span>
                </p>

                <dl className="mt-2 space-y-1 text-[11px] text-neutral-400">
                    <div className="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{daysLabel}</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>{usersLabel}</span>
                    </div>
                    {quality && (
                        <div className="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M15 10l4.55-2.275A1 1 0 0121 8.618v6.764a1 1 0 01-1.45.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>{quality}</span>
                        </div>
                    )}
                    {supportedDevices && (
                        <div className="flex items-start gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-3 h-3 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>{supportedDevices}</span>
                        </div>
                    )}
                </dl>
            </div>
        </Link>
    );
}
