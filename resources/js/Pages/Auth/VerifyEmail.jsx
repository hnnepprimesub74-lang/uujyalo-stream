import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import PrimaryButton from '@/Components/PrimaryButton';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <GuestLayout title="Verify Email">
            <div className="mb-4 text-sm text-neutral-400">
                Thanks for signing up! Before getting started, could you verify your email address by
                clicking on the link we just emailed to you? If you didn't receive the email, we will gladly
                send you another.
            </div>

            {status === 'verification-link-sent' && (
                <div className="mb-4 font-medium text-sm text-emerald-400">
                    A new verification link has been sent to the email address you provided during
                    registration.
                </div>
            )}

            <div className="mt-4 flex items-center justify-between">
                <form onSubmit={submit}>
                    <PrimaryButton disabled={processing}>Resend Verification Email</PrimaryButton>
                </form>

                <Link
                    href={route('logout')}
                    method="post"
                    as="button"
                    className="underline text-sm text-neutral-400 hover:text-neutral-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400"
                >
                    Log Out
                </Link>
            </div>
        </GuestLayout>
    );
}
