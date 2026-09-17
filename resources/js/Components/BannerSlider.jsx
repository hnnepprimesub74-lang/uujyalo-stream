import { useEffect, useRef, useState } from 'react';

export default function BannerSlider({ banners }) {
    const [active, setActive] = useState(0);
    const timerRef = useRef(null);

    const start = () => {
        clearInterval(timerRef.current);
        timerRef.current = setInterval(() => {
            setActive((current) => (current + 1) % banners.length);
        }, 4500);
    };

    useEffect(() => {
        start();

        return () => clearInterval(timerRef.current);
    }, [banners.length]);

    const goTo = (index) => {
        setActive((index + banners.length) % banners.length);
        start();
    };

    if (banners.length === 0) {
        return null;
    }

    return (
        <div className="relative mb-6 rounded-2xl overflow-hidden glass-panel group">
            <div className="aspect-[4/1] relative">
                {banners.map((banner, index) => (
                    <a
                        key={banner.id}
                        href={banner.link_url || undefined}
                        onClick={(e) => {
                            if (!banner.link_url) e.preventDefault();
                        }}
                        className="absolute inset-0 block transition-opacity duration-500"
                        style={{ opacity: active === index ? 1 : 0, pointerEvents: active === index ? 'auto' : 'none' }}
                    >
                        <img
                            src={banner.image_url}
                            alt={banner.title ?? 'Offer'}
                            className="w-full h-full object-cover"
                        />
                        {banner.title && (
                            <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-neutral-950/90 via-neutral-950/30 to-transparent p-4 pt-10">
                                <p className="text-xs font-bold tracking-wider text-brand-400 mb-1">FEATURED OFFER</p>
                                <p className="text-lg font-extrabold leading-tight">{banner.title}</p>
                            </div>
                        )}
                    </a>
                ))}
            </div>

            {banners.length > 1 && (
                <>
                    <button
                        type="button"
                        onClick={() => goTo(active - 1)}
                        aria-label="Previous slide"
                        className="glass-btn-base absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full glass-surface text-white flex items-center justify-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button
                        type="button"
                        onClick={() => goTo(active + 1)}
                        aria-label="Next slide"
                        className="glass-btn-base absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full glass-surface text-white flex items-center justify-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <div className="absolute bottom-2 left-1/2 -translate-x-1/2 flex items-center gap-1.5">
                        {banners.map((banner, index) => (
                            <button
                                key={banner.id}
                                type="button"
                                onClick={() => goTo(index)}
                                className={`h-1.5 rounded-full transition-all ${
                                    active === index ? 'bg-brand-400 w-4' : 'bg-white/40 w-1.5'
                                }`}
                            />
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}
