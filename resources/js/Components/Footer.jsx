import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import Logo from '@/Components/Logo';

export default function Footer({ whatsappNumber }) {
    const { auth } = usePage().props;
    const [email, setEmail] = useState('');
    const [subscribed, setSubscribed] = useState(false);

    const submitNewsletter = (e) => {
        e.preventDefault();
        if (!email.trim()) return;
        setSubscribed(true);
        setEmail('');
    };

    return (
        <footer className="mt-10">
            <div className="max-w-6xl mx-auto px-4">
                <div className="glass-surface rounded-glass px-6 py-6 sm:px-8 sm:py-7 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                    <div>
                        <h2 className="font-extrabold text-lg tracking-tight">Get Exclusive Deals</h2>
                        <p className="text-sm text-neutral-400 mt-0.5">
                            New products, special offers &amp; discounts — straight to your inbox
                        </p>
                    </div>

                    {subscribed ? (
                        <p className="text-sm font-semibold text-brand-400 flex-shrink-0">
                            Thanks for subscribing! 🎉
                        </p>
                    ) : (
                        <form
                            onSubmit={submitNewsletter}
                            className="flex items-center gap-2 flex-shrink-0 w-full md:w-auto"
                        >
                            <input
                                type="email"
                                required
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                placeholder="your@email.com"
                                className="flex-1 md:w-64 rounded-full bg-white/[0.05] border border-white/10 px-4 py-2.5 text-sm text-neutral-100 placeholder-neutral-500 focus:outline-none focus:border-brand-400/50"
                            />
                            <button
                                type="submit"
                                className="glass-btn-base flex items-center gap-2 rounded-full bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 text-sm font-bold flex-shrink-0"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                                Subscribe
                            </button>
                        </form>
                    )}
                </div>
            </div>

            <div className="glass-surface mt-4 rounded-t-glass border-b-0">
                <div className="max-w-6xl mx-auto px-4 py-8">
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div className="sm:col-span-2 lg:col-span-1">
                            <div className="flex items-center gap-2 mb-3">
                                <Logo className="w-7 h-7" />
                                <span className="font-bold tracking-tight">Uujyalo Stream</span>
                            </div>
                            <p className="text-sm text-neutral-400 max-w-xs">
                                Nepal's trusted destination for premium subscriptions — instant delivery, easy
                                local payment.
                            </p>

                            <div className="flex items-center gap-2 mt-4">
                                <a
                                    href="https://www.facebook.com/profile.php?id=61576984923809"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Uujyalo Stream on Facebook"
                                    className="glass-btn-base w-9 h-9 rounded-full bg-white/[0.05] border border-white/10 flex items-center justify-center text-neutral-300 hover:text-brand-400"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06C2 17.08 5.66 21.23 10.44 22v-7.03H7.9v-2.91h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.91h-2.34V22C18.34 21.23 22 17.08 22 12.06z"/></svg>
                                </a>
                                <a
                                    href="https://www.instagram.com/uujyalo_stream/"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Uujyalo Stream on Instagram"
                                    className="glass-btn-base w-9 h-9 rounded-full bg-white/[0.05] border border-white/10 flex items-center justify-center text-neutral-300 hover:text-brand-400"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                                </a>
                                <a
                                    href="https://www.tiktok.com/@uujyalostream.digital?lang=en"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Uujyalo Stream on TikTok"
                                    className="glass-btn-base w-9 h-9 rounded-full bg-white/[0.05] border border-white/10 flex items-center justify-center text-neutral-300 hover:text-brand-400"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16.6 5.82c-.93-.9-1.5-2.15-1.5-3.55h-3.13v13.4a3.1 3.1 0 01-5.58 1.85 3.1 3.1 0 013.44-4.9V9.5a6.26 6.26 0 00-1.03-.09A6.24 6.24 0 002.56 15.6 6.24 6.24 0 008.8 21.85a6.24 6.24 0 006.24-6.24V9.27a9.32 9.32 0 005.44 1.74V7.88a5.87 5.87 0 01-3.88-2.06z"/></svg>
                                </a>
                            </div>
                        </div>

                        <div>
                            <p className="text-xs font-bold tracking-wider text-neutral-500 mb-3">QUICK LINKS</p>
                            <ul className="space-y-2 text-sm">
                                <li><Link href={route('home')} className="text-neutral-400 hover:text-brand-400">Home</Link></li>
                                <li><Link href={route('plans.index')} className="text-neutral-400 hover:text-brand-400">Products</Link></li>
                                <li><Link href={route('reviews')} className="text-neutral-400 hover:text-brand-400">Reviews</Link></li>
                                <li><Link href={route('about')} className="text-neutral-400 hover:text-brand-400">About</Link></li>
                                <li>
                                    <Link
                                        href={auth?.user ? route('dashboard') : route('login')}
                                        className="text-neutral-400 hover:text-brand-400"
                                    >
                                        My Orders
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href={auth?.user ? route('profile.edit') : route('login')}
                                        className="text-neutral-400 hover:text-brand-400"
                                    >
                                        Account
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <p className="text-xs font-bold tracking-wider text-neutral-500 mb-3">CONTACT</p>
                            <ul className="space-y-2 text-sm">
                                {whatsappNumber && (
                                    <>
                                        <li>
                                            <a
                                                href={`tel:+${whatsappNumber.replace(/\D/g, '')}`}
                                                className="text-neutral-400 hover:text-brand-400"
                                            >
                                                Call Us
                                            </a>
                                        </li>
                                        <li>
                                            <a
                                                href={`https://wa.me/${whatsappNumber.replace(/\D/g, '')}`}
                                                target="_blank"
                                                rel="noopener"
                                                className="text-neutral-400 hover:text-brand-400"
                                            >
                                                WhatsApp Support
                                            </a>
                                        </li>
                                    </>
                                )}
                                <li>
                                    <a
                                        href="https://www.facebook.com/profile.php?id=61576984923809"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-neutral-400 hover:text-brand-400"
                                    >
                                        Facebook
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="https://www.instagram.com/uujyalo_stream/"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-neutral-400 hover:text-brand-400"
                                    >
                                        Instagram
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="https://www.tiktok.com/@uujyalostream.digital?lang=en"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-neutral-400 hover:text-brand-400"
                                    >
                                        TikTok
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <p className="text-xs font-bold tracking-wider text-neutral-500 mb-3">WE ACCEPT</p>
                            <div className="flex flex-wrap gap-2">
                                <span className="px-3 py-1.5 rounded-full bg-white/[0.05] border border-white/10 text-xs font-semibold text-neutral-300">
                                    eSewa
                                </span>
                                <span className="px-3 py-1.5 rounded-full bg-white/[0.05] border border-white/10 text-xs font-semibold text-neutral-300">
                                    Khalti
                                </span>
                                <span className="px-3 py-1.5 rounded-full bg-white/[0.05] border border-white/10 text-xs font-semibold text-neutral-300">
                                    Bank Transfer
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="mt-8 pt-6 border-t border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <p className="text-xs text-neutral-600">
                            &copy; {new Date().getFullYear()} Uujyalo Stream. All rights reserved.
                        </p>
                        <p className="text-xs text-neutral-600">Made with care in Nepal.</p>
                    </div>
                </div>
            </div>
        </footer>
    );
}
