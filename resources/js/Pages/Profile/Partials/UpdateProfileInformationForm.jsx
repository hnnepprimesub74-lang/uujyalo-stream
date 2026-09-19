import { usePage } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';

export default function UpdateProfileInformationForm() {
    const user = usePage().props.auth.user;

    return (
        <section>
            <header>
                <h2 className="text-lg font-medium text-neutral-100">Profile Information</h2>
                <p className="mt-1 text-sm text-neutral-400">
                    Your account details. Contact support if you need to change your name, phone, or email.
                </p>
            </header>

            <div className="mt-6 space-y-6">
                <div>
                    <InputLabel htmlFor="name" value="Name" />
                    <TextInput id="name" className="mt-1 block w-full opacity-60" value={user.name} disabled />
                </div>

                <div>
                    <InputLabel htmlFor="phone" value="Phone Number" />
                    <TextInput id="phone" className="mt-1 block w-full opacity-60" value={user.phone} disabled />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Email" />
                    <TextInput id="email" className="mt-1 block w-full opacity-60" value={user.email} disabled />
                </div>
            </div>
        </section>
    );
}
