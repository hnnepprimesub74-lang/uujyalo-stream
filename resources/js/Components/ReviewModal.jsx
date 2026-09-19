import { useForm } from '@inertiajs/react';
import Modal from '@/Components/Modal';
import StarRating from '@/Components/StarRating';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';

export default function ReviewModal({ subscription, onClose }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        rating: subscription?.review?.rating ?? 0,
        comment: subscription?.review?.comment ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('subscriptions.review', subscription.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    };

    return (
        <Modal show={!!subscription} onClose={onClose} maxWidth="md">
            {subscription && (
                <form onSubmit={submit} className="p-6">
                    <h2 className="text-lg font-bold text-neutral-100">
                        {subscription.review ? 'Edit Your Review' : 'Write a Review'}
                    </h2>
                    <p className="mt-1 text-sm text-neutral-400">
                        {subscription.product_name} &middot; {subscription.plan_name}
                    </p>

                    <div className="mt-5">
                        <StarRating value={data.rating} onChange={(v) => setData('rating', v)} size="w-8 h-8" />
                        <InputError message={errors.rating} className="mt-2" />
                    </div>

                    <div className="mt-4">
                        <textarea
                            rows={4}
                            value={data.comment}
                            onChange={(e) => setData('comment', e.target.value)}
                            placeholder="Share your experience (optional)"
                            className="glass-input w-full"
                        />
                        <InputError message={errors.comment} className="mt-2" />
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton type="button" onClick={onClose}>
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton disabled={processing || data.rating === 0}>Submit</PrimaryButton>
                    </div>
                </form>
            )}
        </Modal>
    );
}
