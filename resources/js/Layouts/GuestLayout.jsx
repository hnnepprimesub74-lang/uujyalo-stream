import { Head, Link } from '@inertiajs/react';
import Logo from '@/Components/Logo';

export default function GuestLayout({ title, children }) {
    return (
        <div className="font-sans text-neutral-100 antialiased bg-neutral-950 min-h-screen ambient-glow">
            <Head title={title} />

            <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
                <Link href={route('home')} className="flex items-center">
                    <Logo className="h-14 w-auto shadow-glass-sm rounded-xl" />
                </Link>

                <div className="w-full sm:max-w-md mt-6 px-6 py-6 glass-panel rounded-glass overflow-hidden animate-glass-in">
                    {children}
                </div>
            </div>
        </div>
    );
}
