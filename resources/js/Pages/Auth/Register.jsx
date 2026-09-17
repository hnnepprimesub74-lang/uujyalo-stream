import { useEffect, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';

export default function Register({ phone_exists: phoneExistsFlash }) {
    const [phoneExists, setPhoneExists] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        phone: phoneExistsFlash ?? '',
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
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
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout title="Register">
            {phoneExistsFlash && (
                <div className="mb-4 rounded-xl bg-brand-500/10 border border-brand-500/30 text-brand-300 text-sm px-4 py-3 text-center">
                    An account with this phone number already exists.{' '}
                    <a
                        href={`${route('password.request')}?phone=${phoneExistsFlash}`}
                        className="font-semibold underline hover:text-brand-200"
                    >
                        I don't have a password
                    </a>
                </div>
            )}

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
                        autoComplete="tel"
                        placeholder="98XXXXXXXX"
                    />
                    <InputError message={errors.phone} className="mt-2" />

                    {phoneExists && (
                        <>
                            <p className="mt-2 text-sm text-emerald-400">
                                You already have an account with this number.
                            </p>
                            <div className="mt-3 flex justify-center">
                                <a
                                    className="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-brand-400/40 text-brand-400 text-sm font-semibold hover:bg-brand-400/10 transition"
                                    href={`${route('password.request')}?phone=${data.phone}`}
                                >
                                    I don't have a password
                                </a>
                            </div>
                        </>
                    )}
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="name" value="Name" />
                    <TextInput
                        id="name"
                        className="block mt-1 w-full"
                        type="text"
                        name="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        autoComplete="name"
                    />
                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="email" value="Email (optional)" />
                    <TextInput
                        id="email"
                        className="block mt-1 w-full"
                        type="email"
                        name="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        autoComplete="username"
                    />
                    <InputError message={errors.email} className="mt-2" />
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
                        autoComplete="new-password"
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password_confirmation" value="Confirm Password" />
                    <TextInput
                        id="password_confirmation"
                        className="block mt-1 w-full"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                        autoComplete="new-password"
                    />
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                <div className="flex items-center justify-end mt-4">
                    <Link
                        href={route('login')}
                        className="underline text-sm text-neutral-400 hover:text-neutral-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400"
                    >
                        Already registered?
                    </Link>

                    <PrimaryButton className="ms-4" disabled={processing}>
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
