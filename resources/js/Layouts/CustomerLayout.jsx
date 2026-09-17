import { Head, Link, usePage } from '@inertiajs/react';
import Footer from '@/Components/Footer';
import Navbar from '@/Components/Navbar';

export default function CustomerLayout({ title, children }) {
    const { props, url } = usePage();
    const { auth, flash, whatsappNumber, errors } = props;

    const isActive = (path) => url === path || url.startsWith(path + '/');

    return (
        <div className="font-sans antialiased bg-neutral-950 text-neutral-100 min-h-screen flex flex-col ambient-glow">
            <Head title={title} />

            <Navbar />

            {/* Page content */}
            <main className="flex-1 max-w-6xl mx-auto w-full px-4 pb-6 pt-4">
                {flash?.status && (
                    <div className="mb-4 rounded-xl backdrop-blur-md bg-emerald-500/10 border border-emerald-500/25 text-emerald-300 text-sm px-4 py-3 shadow-glass-sm">
                        {flash.status}
                    </div>
                )}

                {errors && Object.keys(errors).length > 0 && (
                    <div className="mb-4 rounded-xl backdrop-blur-md bg-red-500/10 border border-red-500/25 text-red-300 text-sm px-4 py-3 space-y-1 shadow-glass-sm">
                        {Object.values(errors).map((error, i) => (
                            <p key={i}>{error}</p>
                        ))}
                    </div>
                )}

                {children}
            </main>

            <Footer whatsappNumber={whatsappNumber} />

            <div className="pb-24" />

            {/* WhatsApp float */}
            {whatsappNumber && (
                <a
                    href={`https://wa.me/${whatsappNumber.replace(/\D/g, '')}`}
                    target="_blank"
                    rel="noopener"
                    className="glass-btn-base fixed right-4 bottom-24 z-40 w-14 h-14 rounded-full bg-emerald-500/90 border border-white/20 flex items-center justify-center hover:bg-emerald-400"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" className="w-7 h-7 fill-white"><path d="M16.001 3C9.373 3 4 8.373 4 15c0 2.386.7 4.607 1.908 6.47L4 29l7.73-1.878A11.94 11.94 0 0016 27c6.627 0 12-5.373 12-12S22.628 3 16.001 3zm0 21.6c-1.99 0-3.85-.55-5.44-1.51l-.39-.23-4.59 1.116 1.15-4.47-.25-.4A9.55 9.55 0 016.4 15c0-5.302 4.3-9.6 9.6-9.6 5.302 0 9.6 4.298 9.6 9.6 0 5.301-4.298 9.6-9.6 9.6zm5.27-7.19c-.288-.144-1.706-.842-1.97-.938-.264-.096-.457-.144-.65.144-.192.288-.746.938-.914 1.13-.168.192-.336.216-.624.072-.288-.144-1.217-.449-2.318-1.43-.857-.764-1.435-1.708-1.604-1.996-.168-.288-.018-.443.126-.587.13-.129.288-.336.432-.504.144-.168.192-.288.288-.48.096-.192.048-.36-.024-.504-.072-.144-.65-1.566-.89-2.146-.234-.564-.472-.488-.65-.497l-.554-.01c-.192 0-.504.072-.768.36-.264.288-1.008.985-1.008 2.404 0 1.42 1.032 2.79 1.176 2.982.144.192 2.03 3.1 4.92 4.347.687.297 1.223.474 1.641.606.69.22 1.317.189 1.813.115.553-.083 1.706-.697 1.946-1.371.24-.673.24-1.25.168-1.371-.072-.12-.264-.192-.552-.336z"/></svg>
                </a>
            )}

            {/* Bottom nav */}
            <nav className="fixed bottom-0 left-0 right-0 z-40 flex justify-center px-3 pb-3 [padding-bottom:calc(0.75rem+env(safe-area-inset-bottom))]">
                <div className="inline-flex items-center h-14 rounded-glass glass-surface px-2 gap-2">
                    <Link
                        href={route('home')}
                        className={`flex items-center gap-2 h-10 px-6 rounded-2xl transition duration-200 ease-glass ${url === '/' ? 'bg-white/[0.07] text-brand-400' : 'text-neutral-400 hover:text-neutral-200'}`}
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10"/></svg>
                        <span className="text-sm font-medium">Home</span>
                    </Link>
                    <Link
                        href={auth?.user ? route('dashboard') : route('login')}
                        className={`flex items-center gap-2 h-10 px-6 rounded-2xl transition duration-200 ease-glass ${isActive('/dashboard') ? 'bg-white/[0.07] text-brand-400' : 'text-neutral-400 hover:text-neutral-200'}`}
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M9 3v18M4 3h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1V4a1 1 0 011-1z"/></svg>
                        <span className="text-sm font-medium">My Orders</span>
                    </Link>
                </div>
            </nav>
        </div>
    );
}
