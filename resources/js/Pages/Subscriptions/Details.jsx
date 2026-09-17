import { useForm } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import { getAccentColor, contrastText } from '@/utils/theme';

export default function Details({ subscription }) {
    const productName = (subscription.plan.product?.name ?? 'the').split(' - ')[0];
    const accent = getAccentColor(subscription.plan.product);
    const accentText = contrastText(accent);

    const { data, setData, post, processing, errors } = useForm({
        email: subscription.account_email ?? '',
        password: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('subscriptions.storeDetails', subscription.id));
    };

    return (
        <CustomerLayout title="Account Details">
            <div className="max-w-xl mx-auto">
            <h1 className="text-2xl font-extrabold mb-4">Account Details</h1>

            <div className="rounded-2xl glass-panel p-4 mb-4">
                <p className="font-semibold">{subscription.plan.full_name}</p>
                <p className="text-sm text-neutral-400 mt-0.5">{subscription.plan.duration_days} days access</p>
                <p className="text-green-400 font-extrabold text-xl mt-2">
                    NPR {Number(subscription.amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                </p>
            </div>

            <form onSubmit={submit}>
                <div className="rounded-2xl glass-panel p-4">
                    <div className="space-y-4">
                        <div>
                            <InputLabel htmlFor="email" value="Email" />
                            <TextInput
                                id="email"
                                type="email"
                                name="email"
                                required
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="you@example.com"
                                className="block mt-1 w-full"
                            />
                            <p className="text-xs font-semibold mt-1" style={{ color: accent }}>
                                This email will be used to create your <span className="text-sky-400">{productName}</span> account.
                            </p>
                            <InputError message={errors.email} className="mt-1" />
                        </div>

                        <div>
                            <InputLabel htmlFor="password" value={`${productName} Password`} />
                            <TextInput
                                id="password"
                                type="text"
                                name="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder={`Only if ${productName} is already registered with this email`}
                                className="block mt-1 w-full"
                            />
                            <p className="text-xs font-semibold mt-1" style={{ color: accent }}>
                                Note: we don't need your email's password — just the <span className="text-sky-400">{productName}</span> account password.
                            </p>
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            style={{ backgroundColor: accent, color: accentText }}
                            className="glass-btn-base w-full border border-white/20 font-bold rounded-xl py-3.5 disabled:opacity-60"
                        >
                            Continue to Payment
                        </button>
                    </div>
                </div>
            </form>
            </div>
        </CustomerLayout>
    );
}
