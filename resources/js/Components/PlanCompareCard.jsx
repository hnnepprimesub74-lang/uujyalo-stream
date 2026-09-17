export default function PlanCompareCard({ plan, selected, onSelect }) {
    return (
        <button
            type="button"
            onClick={onSelect}
            className={`relative text-left rounded-2xl p-4 backdrop-blur-xl backdrop-saturate-150 shadow-glass transition duration-200 ease-glass ${
                selected ? 'border border-emerald-400/60 bg-emerald-400/[0.07]' : 'border border-white/10 bg-white/[0.04] hover:bg-white/[0.06]'
            }`}
        >
            {plan.badge_label && (
                <span className="absolute -top-2.5 left-4 px-2.5 py-0.5 rounded-full bg-emerald-500 text-neutral-950 text-[10px] font-bold tracking-wide">
                    {plan.badge_label}
                </span>
            )}

            <p className="text-xl font-extrabold">
                NPR {Number(plan.price).toLocaleString()}
                <span className="text-sm font-medium text-neutral-500"> / {plan.duration_days} days</span>
            </p>
            <p className="font-bold mt-2">{plan.full_name}</p>
            {plan.description && (
                <p className="text-sm text-neutral-400 mt-1">{plan.description}</p>
            )}

            {plan.features?.length > 0 && (
                <ul className="mt-3 space-y-2">
                    {plan.features.map((feature, i) => (
                        <li key={i} className="flex items-start gap-2 text-sm text-neutral-300 border-t border-neutral-800 pt-2 first:border-t-0 first:pt-0">
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>{feature}</span>
                        </li>
                    ))}
                </ul>
            )}
        </button>
    );
}
