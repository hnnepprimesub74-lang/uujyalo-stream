import { Link, usePage } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';

function CustomerCare() {
    const { whatsappNumber, supportEmail, supportPhone } = usePage().props;

    if (!whatsappNumber && !supportEmail && !supportPhone) return null;

    return (
        <div className="p-4 sm:p-6 glass-panel rounded-2xl">
            <h2 className="text-lg font-medium text-neutral-100">Customer Care</h2>
            <p className="mt-1 text-sm text-neutral-400">Need help? Reach out to us.</p>

            <div className="mt-4 space-y-3">
                {supportEmail && (
                    <a
                        href={`mailto:${supportEmail}`}
                        className="flex items-center gap-3 text-sm text-neutral-200 hover:text-brand-400"
                    >
                        <span className="w-9 h-9 rounded-full bg-white/[0.06] flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        {supportEmail}
                    </a>
                )}
                {whatsappNumber && (
                    <a
                        href={`https://wa.me/${whatsappNumber.replace(/\D/g, '')}`}
                        target="_blank"
                        rel="noopener"
                        className="flex items-center gap-3 text-sm text-neutral-200 hover:text-brand-400"
                    >
                        <span className="w-9 h-9 rounded-full bg-white/[0.06] flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" className="w-4 h-4 fill-current"><path d="M16.001 3C9.373 3 4 8.373 4 15c0 2.386.7 4.607 1.908 6.47L4 29l7.73-1.878A11.94 11.94 0 0016 27c6.627 0 12-5.373 12-12S22.628 3 16.001 3zm0 21.6c-1.99 0-3.85-.55-5.44-1.51l-.39-.23-4.59 1.116 1.15-4.47-.25-.4A9.55 9.55 0 016.4 15c0-5.302 4.3-9.6 9.6-9.6 5.302 0 9.6 4.298 9.6 9.6 0 5.301-4.298 9.6-9.6 9.6zm5.27-7.19c-.288-.144-1.706-.842-1.97-.938-.264-.096-.457-.144-.65.144-.192.288-.746.938-.914 1.13-.168.192-.336.216-.624.072-.288-.144-1.217-.449-2.318-1.43-.857-.764-1.435-1.708-1.604-1.996-.168-.288-.018-.443.126-.587.13-.129.288-.336.432-.504.144-.168.192-.288.288-.48.096-.192.048-.36-.024-.504-.072-.144-.65-1.566-.89-2.146-.234-.564-.472-.488-.65-.497l-.554-.01c-.192 0-.504.072-.768.36-.264.288-1.008.985-1.008 2.404 0 1.42 1.032 2.79 1.176 2.982.144.192 2.03 3.1 4.92 4.347.687.297 1.223.474 1.641.606.69.22 1.317.189 1.813.115.553-.083 1.706-.697 1.946-1.371.24-.673.24-1.25.168-1.371-.072-.12-.264-.192-.552-.336z"/></svg>
                        </span>
                        WhatsApp: {whatsappNumber}
                    </a>
                )}
                {supportPhone && (
                    <a
                        href={`tel:${supportPhone}`}
                        className="flex items-center gap-3 text-sm text-neutral-200 hover:text-brand-400"
                    >
                        <span className="w-9 h-9 rounded-full bg-white/[0.06] flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3l2 5-2.5 1.5a11 11 0 005 5L14 12l5 2v3a2 2 0 01-2 2A16 16 0 013 5z"/></svg>
                        </span>
                        {supportPhone}
                    </a>
                )}
            </div>
        </div>
    );
}

export default function Edit() {
    return (
        <CustomerLayout title="My Account">
            <div className="max-w-2xl mx-auto">
                <h1 className="text-2xl font-extrabold mb-4">My Account</h1>

                <div className="space-y-4">
                    <div className="p-4 sm:p-6 glass-panel rounded-2xl">
                        <UpdateProfileInformationForm />
                    </div>

                    <div className="p-4 sm:p-6 glass-panel rounded-2xl">
                        <UpdatePasswordForm />
                    </div>

                    <CustomerCare />

                    <div className="p-4 sm:p-6 glass-panel rounded-2xl flex items-center justify-between gap-4">
                        <div>
                            <h2 className="text-lg font-medium text-neutral-100">Sign Out</h2>
                            <p className="mt-1 text-sm text-neutral-400">
                                Sign out of your account on this device.
                            </p>
                        </div>
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="glass-btn-base flex-shrink-0 rounded-xl bg-white/[0.05] border border-white/10 hover:bg-white/[0.09] px-4 py-2.5 text-sm font-semibold text-neutral-200"
                        >
                            Sign Out
                        </Link>
                    </div>
                </div>
            </div>
        </CustomerLayout>
    );
}
