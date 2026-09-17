import { useRef } from 'react';

export default function TrendingList({ items }) {
    const scrollRef = useRef(null);

    if (!items || items.length === 0) {
        return null;
    }

    const scroll = (dir) => {
        scrollRef.current?.scrollBy({ left: dir * 260, behavior: 'smooth' });
    };

    return (
        <div className="relative">
            <h2 className="font-extrabold tracking-tight text-lg mb-3">Trending Now</h2>

            <div
                ref={scrollRef}
                className="flex gap-5 overflow-x-auto pb-2 pl-8 -mx-1 px-1"
                style={{ scrollbarWidth: 'none' }}
            >
                {items.map((item, index) => (
                    <div key={index} className="relative flex-shrink-0 w-24 h-36">
                        <div className="absolute right-0 top-0 w-20 h-full rounded-lg overflow-hidden bg-neutral-800">
                            {item.image_url ? (
                                <img
                                    src={item.image_url}
                                    alt={item.title || `#${index + 1}`}
                                    className="w-full h-full object-cover"
                                    loading="lazy"
                                />
                            ) : (
                                <div className="w-full h-full flex items-center justify-center text-neutral-600 text-xs" />
                            )}
                        </div>
                        <span
                            className="absolute left-0 bottom-0 z-10 text-7xl font-black leading-none select-none text-neutral-100"
                            style={{
                                WebkitTextStroke: '2px #171717',
                                paintOrder: 'stroke fill',
                                filter: 'drop-shadow(0 2px 4px rgba(0,0,0,0.6))',
                            }}
                        >
                            {index + 1}
                        </span>
                    </div>
                ))}
            </div>

            {items.length > 4 && (
                <button
                    type="button"
                    onClick={() => scroll(1)}
                    aria-label="Scroll trending list"
                    className="glass-btn-base hidden sm:flex absolute right-0 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full glass-surface text-white items-center justify-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            )}
        </div>
    );
}
