import { useEffect, useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';

export default function Login() {
    const { flash } = usePage().props;
    const [phoneExists, setPhoneExists] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        phone: '',
        password: '',
        remember: false,
    });

    useEffect(() => {
        const handle = setTimeout(() => {
            if (data.phone.trim().length < 6) {
                setPhoneExists(false);
                return;
            }

            fetch(`${route('phone.lookup')}?phone=${encodeURIComponent(data.phone)}`, {
                headers: { Accept: 'application/json' },
            })
                .then((r) => r.json())
                .then((res) => setPhoneExists(!!res.exists))
                .catch(() => setPhoneExists(false));
        }, 500);

        return () => clearTimeout(handle);
    }, [data.phone]);

    const submit = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <GuestLayout title="Sign In">
            {flash?.status && <div className="mb-4 font-medium text-sm text-emerald-400">{flash.status}</div>}

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="phone" value="Phone Number" />
                    <TextInput
                        id="phone"
                        className="block mt-1 w-full"
                        type="tel"
                        name="phone"
                        value={data.phone}
                        onChange={(e) => setData('phone', e.target.value)}
                        required
                        autoFocus
                        autoComplete="username"
                        placeholder="98XXXXXXXX"
                    />
                    <InputError message={errors.phone} className="mt-2" />

                    {phoneExists && (
                        <p className="mt-2 text-sm text-emerald-400">
                            You already have an account with this number.
                        </p>
                    )}
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        className="block mt-1 w-full"
                        type="password"
                        name="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                        autoComplete="current-password"
                    />

                    <InputError message={errors.password} className="mt-2" />

                    {phoneExists && (
                        <div className="mt-3 flex justify-center">
                            <a
                                className="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-brand-400/40 text-brand-400 text-sm font-semibold hover:bg-brand-400/10 transition"
                                href={`${route('password.request')}?phone=${data.phone}`}
                            >
                                I don't have a password
                            </a>
                        </div>
                    )}
                </div>

                <div className="block mt-4">
                    <label className="inline-flex items-center">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                            className="rounded border-white/15 bg-white/[0.05] text-brand-400 focus:ring-brand-400"
                        />
                        <span className="ms-2 text-sm text-neutral-400">Remember me</span>
                    </label>
                </div>

                <div className="flex items-center justify-between mt-4">
                    <Link
                        href={route('password.request')}
                        className="underline text-sm text-neutral-400 hover:text-neutral-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400"
                    >
                        Forgot your password?
                    </Link>

                    <PrimaryButton className="ms-3" disabled={processing}>
                        Log in
                    </PrimaryButton>
                </div>
            </form>

            <p className="mt-6 text-center text-sm text-neutral-400">
                Don't have an account?{' '}
                <Link href={route('register')} className="font-semibold text-brand-400 hover:text-brand-300">
                    Sign up
                </Link>
            </p>
        </GuestLayout>
    );
}
