import { useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.confirm'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout title="Confirm Password">
            <div className="mb-4 text-sm text-neutral-400">
                This is a secure area of the application. Please confirm your password before continuing.
            </div>

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="block mt-1 w-full"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                        autoFocus
                        autoComplete="current-password"
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="flex justify-end mt-4">
                    <PrimaryButton disabled={processing}>Confirm</PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
