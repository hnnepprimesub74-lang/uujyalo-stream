import CustomerLayout from '@/Layouts/CustomerLayout';

const features = [
    {
        title: 'One of Nepal\'s Oldest Providers',
        description: 'We\'ve been serving Nepali customers with premium digital subscriptions since the early days of local subscription resale — long enough to know how to do it right.',
        icon: (
            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        ),
    },
    {
        title: 'Fully Automated System',
        description: 'Orders, payment verification, and account delivery all run through our own automated platform — less waiting, fewer manual mistakes.',
        icon: (
            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        ),
    },
    {
        title: 'Instant Subscription',
        description: 'The moment your payment is confirmed, your account is activated — no 24-hour waiting games.',
        icon: (
            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7"/></svg>
        ),
    },
    {
        title: 'Netflix Gift Card Accounts',
        description: 'Netflix accounts are created and topped up using genuine Netflix Gift Cards — not shared logins or hacked accounts — so your subscription stays safe and secure.',
        icon: (
            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        ),
    },
];

export default function About() {
    return (
        <CustomerLayout title="About Us">
            <div className="max-w-2xl mx-auto">
                <div className="text-center mb-6">
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-400/10 border border-brand-400/25 text-brand-400 text-xs font-bold tracking-wide mb-3">
                        <span className="w-1.5 h-1.5 rounded-full bg-brand-400" />
                        TRUSTED IN NEPAL
                    </span>
                    <h1 className="text-3xl font-extrabold tracking-tight">About Uujyalo Stream</h1>
                    <p className="text-sm text-neutral-400 mt-2 max-w-md mx-auto">
                        One of Nepal's oldest subscription providers — fully automated, instantly delivered,
                        and built on genuine accounts you can trust.
                    </p>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {features.map((feature) => (
                        <div key={feature.title} className="rounded-2xl glass-panel p-5">
                            <div className="w-10 h-10 rounded-xl bg-brand-400/10 border border-brand-400/20 flex items-center justify-center mb-3 text-brand-400">
                                {feature.icon}
                            </div>
                            <p className="font-bold">{feature.title}</p>
                            <p className="text-sm text-neutral-400 mt-1.5">{feature.description}</p>
                        </div>
                    ))}
                </div>

                <div className="rounded-2xl glass-panel-strong border-l-4 border-amber-400 p-5 mt-4">
                    <div className="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-amber-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        <p className="font-bold text-amber-400">A Word of Caution</p>
                    </div>
                    <p className="text-sm text-neutral-300">
                        If you see a "Netflix subscription" for an unbelievably cheap price, or a
                        "lifetime" CapCut Pro or ChatGPT Plus deal, be careful — genuine subscriptions have
                        real costs behind them. Offers priced far below that are usually built on shared,
                        stolen, or soon-to-be-banned accounts, and can stop working without warning. We'd
                        rather be upfront about honest pricing than sell you something that won't last.
                    </p>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
                    <div className="rounded-xl glass-panel p-4 text-center">
                        <div className="w-10 h-10 mx-auto rounded-xl bg-neutral-800 flex items-center justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 6a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V8a2 2 0 00-2-2M4 6l2-4h12l2 4"/></svg>
                        </div>
                        <p className="text-xs font-bold">Browse</p>
                        <p className="text-[11px] text-neutral-500 mt-0.5">Pick your plan</p>
                    </div>
                    <div className="rounded-xl glass-panel p-4 text-center">
                        <div className="w-10 h-10 mx-auto rounded-xl bg-neutral-800 flex items-center justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3 10h18M7 15h2m4 0h2M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                        </div>
                        <p className="text-xs font-bold">Pay</p>
                        <p className="text-[11px] text-neutral-500 mt-0.5">eSewa, Khalti or Bank</p>
                    </div>
                    <div className="rounded-xl glass-panel p-4 text-center">
                        <div className="w-10 h-10 mx-auto rounded-xl bg-neutral-800 flex items-center justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <p className="text-xs font-bold">Receive</p>
                        <p className="text-[11px] text-neutral-500 mt-0.5">On website &amp; email</p>
                    </div>
                </div>
            </div>
        </CustomerLayout>
    );
}
