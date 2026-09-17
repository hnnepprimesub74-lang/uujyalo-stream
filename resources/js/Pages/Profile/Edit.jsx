import CustomerLayout from '@/Layouts/CustomerLayout';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import DeleteUserForm from './Partials/DeleteUserForm';

export default function Edit({ mustVerifyEmail, status }) {
    return (
        <CustomerLayout title="Profile">
            <div className="max-w-2xl mx-auto">
                <h1 className="text-2xl font-extrabold mb-4">Profile</h1>

                <div className="space-y-4">
                    <div className="p-4 sm:p-6 glass-panel rounded-2xl">
                        <UpdateProfileInformationForm mustVerifyEmail={mustVerifyEmail} status={status} />
                    </div>

                    <div className="p-4 sm:p-6 glass-panel rounded-2xl">
                        <UpdatePasswordForm />
                    </div>

                    <div className="p-4 sm:p-6 glass-panel rounded-2xl">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </CustomerLayout>
    );
}
