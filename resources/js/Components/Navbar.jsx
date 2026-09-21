import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import Logo from '@/Components/Logo';

export default function Navbar() {
    const { props, url } = usePage();
    const { auth } = props;
    const [searchOpen, setSearchOpen] = useState(false);
    const [query, setQuery] = useState('');

    const isActive = (path) => url === path || url.startsWith(path + '/');

    const submitSearch = (e) => {
        e.preventDefault();
        router.visit(route('home', query.trim() ? { search: query.trim() } : {}));
        setSearchOpen(false);
    };

    const navLinkClass = (active) =>
        `px-3.5 py-1.5 rounded-full text-sm font-semibold transition duration-200 ease-glass ${
            active
                ? 'text-white bg-brand-500 border border-white/10 shadow-glass-sm'
                : 'text-neutral-300 border border-transparent hover:text-neutral-100 hover:bg-white/[0.06] hover:border-white/10 hover:backdrop-blur-xl hover:shadow-glass-sm'
        }`;

    return (
        <header className="sticky top-0 z-40 px-3 pt-3 pb-1">
            <div className="max-w-6xl mx-auto rounded-full glass-surface pl-2 pr-2 py-2 flex items-center justify-between gap-4">
                <Link href={route('home')} className="flex items-center flex-shrink-0">
                    <Logo className="h-9 w-auto rounded-lg" />
                </Link>

                <nav className="hidden md:flex items-center gap-1">
                    <Link href={route('home')} className={navLinkClass(url === '/')}>
                        Home
                    </Link>
                    <Link href={route('plans.index')} className={navLinkClass(isActive('/plans'))}>
                        Products
                    </Link>
                    <Link href={route('reviews')} className={navLinkClass(isActive('/reviews'))}>
                        Reviews
                    </Link>
                    <Link href={route('about')} className={navLinkClass(isActive('/about'))}>
                        About
                    </Link>
                </nav>

                <div className="flex items-center gap-2 ml-auto">
                    {searchOpen ? (
                        <form onSubmit={submitSearch} className="flex items-center">
                            <input
                                type="text"
                                autoFocus
                                value={query}
                                onChange={(e) => setQuery(e.target.value)}
                                onBlur={() => !query && setSearchOpen(false)}
                                placeholder="Search products…"
                                className="w-40 sm:w-56 rounded-full glass-input text-sm py-1.5"
                            />
                        </form>
                    ) : (
                        <button
                            type="button"
                            onClick={() => setSearchOpen(true)}
                            aria-label="Search"
                            className="w-9 h-9 rounded-full flex items-center justify-center text-neutral-300 hover:text-neutral-100 hover:bg-neutral-800 transition flex-shrink-0"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="11" cy="11" r="7"/><path strokeLinecap="round" d="M21 21l-4.3-4.3"/></svg>
                        </button>
                    )}

                    {auth?.user ? (
                        <Link
                            href={route('profile.edit')}
                            className="glass-btn-base flex items-center gap-2 rounded-full bg-brand-500 border border-white/20 hover:bg-brand-600 text-white pl-3 pr-1.5 py-1.5 text-sm font-bold flex-shrink-0"
                        >
                            <span className="hidden sm:inline">My Account</span>
                            <span className="w-6 h-6 rounded-full bg-white/15 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                        </Link>
                    ) : (
                        <Link
                            href={route('login')}
                            className="glass-btn-base rounded-full bg-brand-500 border border-white/20 hover:bg-brand-600 text-white px-5 py-2 text-sm font-bold flex-shrink-0"
                        >
                            Sign In
                        </Link>
                    )}
                </div>
            </div>
        </header>
    );
}
