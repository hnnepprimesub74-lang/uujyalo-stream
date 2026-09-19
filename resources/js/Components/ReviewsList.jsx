import { router } from '@inertiajs/react';
import StarRating from '@/Components/StarRating';

export default function ReviewsList({ reviews, showProductName = false }) {
    const goToPage = (url) => {
        if (!url) return;
        router.get(url, {}, { preserveState: true, preserveScroll: true, only: ['reviews'] });
    };

    if (reviews.data.length === 0) {
        return (
            <div className="rounded-2xl glass-panel p-6 text-center text-sm text-neutral-500">
                No reviews yet.
            </div>
        );
    }

    return (
        <div>
            <div className="space-y-3">
                {reviews.data.map((review) => (
                    <div key={review.id} className="rounded-2xl glass-panel p-4">
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <p className="font-semibold text-sm">{review.reviewer_name}</p>
                                {showProductName && (
                                    <p className="text-xs text-neutral-500">{review.product_name}</p>
                                )}
                            </div>
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-[10px] font-bold whitespace-nowrap">
                                <svg xmlns="http://www.w3.org/2000/svg" className="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Verified Purchase
                            </span>
                        </div>

                        <div className="mt-2 flex items-center gap-2">
                            <StarRating value={review.rating} readOnly size="w-4 h-4" />
                            <span className="text-xs text-neutral-500">{review.created_at}</span>
                        </div>

                        {review.comment && (
                            <p className="mt-2 text-sm text-neutral-300 whitespace-pre-line">{review.comment}</p>
                        )}
                    </div>
                ))}
            </div>

            {reviews.last_page > 1 && (
                <div className="flex items-center justify-center gap-2 mt-4">
                    {reviews.links.map((link, i) => (
                        <button
                            key={i}
                            type="button"
                            disabled={!link.url}
                            onClick={() => goToPage(link.url)}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                            className={`min-w-[2.25rem] h-9 px-2 rounded-xl text-sm font-semibold transition ${
                                link.active
                                    ? 'bg-brand-500 text-white'
                                    : link.url
                                    ? 'glass-panel text-neutral-300 hover:bg-white/[0.07]'
                                    : 'text-neutral-700 cursor-not-allowed'
                            }`}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
