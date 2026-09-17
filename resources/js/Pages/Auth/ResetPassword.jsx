import { useForm, usePage } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';

export default function ResetPassword({ phone }) {
    const { flash } = usePage().props;

    const { data, setData, post, processing, errors } = useForm({
        phone: phone ?? '',
        otp: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.store'));
    };

    return (
        <GuestLayout title="Reset Password">
            <div className="mb-4 text-sm text-neutral-400">
                Enter the 6-digit code we texted to your phone, then choose a new password.
            </div>

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
                        autoComplete="username"
                    />
                    <InputError message={errors.phone} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="otp" value="Verification Code" />
                    <TextInput
                        id="otp"
                        className="block mt-1 w-full"
                        type="text"
                        inputMode="numeric"
                        pattern="[0-9]*"
                        maxLength={6}
                        name="otp"
                        value={data.otp}
                        onChange={(e) => setData('otp', e.target.value)}
                        required
                        autoFocus
                    />
                    <InputError message={errors.otp} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="New Password" />
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
                    <InputLabel htmlFor="password_confirmation" value="Confirm New Password" />
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

                <div className="flex items-center justify-between mt-4">
                    <a
                        className="underline text-sm text-neutral-400 hover:text-neutral-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400"
                        href={`${route('password.request')}?phone=${data.phone}`}
                    >
                        Didn't get a code? Resend
                    </a>

                    <PrimaryButton className="ms-3" disabled={processing}>
                        Set Password
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
