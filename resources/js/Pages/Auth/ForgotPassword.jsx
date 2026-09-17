import { useForm, usePage } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';

export default function ForgotPassword({ phone }) {
    const { flash } = usePage().props;

    const { data, setData, post, processing, errors } = useForm({
        phone: phone ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <GuestLayout title="Forgot Password">
            <div className="mb-4 text-sm text-neutral-400">
                Enter your phone number and we'll text you a one-time code to set up or reset your password.
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
                        autoFocus
                        placeholder="98XXXXXXXX"
                    />
                    <InputError message={errors.phone} className="mt-2" />
                </div>

                <div className="flex items-center justify-end mt-4">
                    <PrimaryButton disabled={processing}>Send Code</PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
