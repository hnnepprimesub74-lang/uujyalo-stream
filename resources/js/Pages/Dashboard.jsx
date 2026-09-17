import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import DangerButton from '@/Components/DangerButton';

function statusStyles(sub) {
    if (sub.awaiting_activation) return 'bg-brand-400/10 border border-brand-400/25 text-brand-400';
    if (sub.needs_info || sub.is_rejected) return 'bg-red-500/10 border border-red-500/25 text-red-400';
    if (sub.awaiting_review) return 'bg-brand-400/10 border border-brand-400/25 text-brand-400';
    if (sub.status === 'pending') return 'bg-brand-400/10 border border-brand-400/25 text-brand-400';
    if (sub.status === 'active') return 'bg-emerald-500/10 border border-emerald-500/25 text-emerald-400';
    if (sub.status === 'expired') return 'bg-neutral-500/10 border border-neutral-500/25 text-neutral-400';
    return 'bg-red-500/10 border border-red-500/25 text-red-400';
}

function statusLabel(sub) {
    if (sub.awaiting_activation) return 'Processing';
    if (sub.needs_info) return 'Action Needed';
    if (sub.awaiting_review) return 'Under Review';
    return sub.status.charAt(0).toUpperCase() + sub.status.slice(1);
}

export default function Dashboard({ activeSubscriptions, subscriptions }) {
    const [confirmingDeleteId, setConfirmingDeleteId] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const confirmDelete = (id) => setConfirmingDeleteId(id);
    const closeModal = () => setConfirmingDeleteId(null);

    const handleDelete = () => {
        setDeleting(true);
        router.delete(route('subscriptions.destroy', confirmingDeleteId), {
            onFinish: () => {
                setDeleting(false);
                closeModal();
            },
        });
    };

    return (
        <CustomerLayout title="My Orders">
            <div className="max-w-3xl mx-auto">
            <h1 className="text-2xl font-extrabold mb-4">My Orders</h1>

            <h3 className="font-semibold text-sm text-neutral-400 mb-2">Active Subscriptions</h3>

            {activeSubscriptions.length === 0 ? (
                <div className="rounded-2xl glass-panel p-4 mb-6">
                    <p className="text-neutral-500 text-sm">You don't have an active subscription.</p>
                    <Link
                        href={route('home')}
                        className="glass-btn-base inline-flex items-center gap-1 mt-3 bg-brand-400/90 border border-white/20 hover:bg-brand-300 text-neutral-950 rounded-xl px-4 py-2 text-sm font-bold"
                    >
                        Browse Products
                    </Link>
                </div>
            ) : (
                <div className="space-y-3 mb-6">
                    {activeSubscriptions.map((sub) => (
                        <div key={sub.id} className="rounded-2xl glass-panel-strong p-4">
                            <p className="text-xs font-semibold text-brand-400 uppercase tracking-wide">{sub.product_name}</p>
                            <p className="text-lg font-bold">{sub.plan_name}</p>

                            <dl className="grid grid-cols-2 gap-x-4 gap-y-2 mt-3 text-sm">
                                <div>
                                    <dt className="text-neutral-500">Account Email</dt>
                                    <dd className="font-medium break-all">{sub.account_email ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-neutral-500">Account Password</dt>
                                    <dd className="font-medium break-all">{sub.account_password ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-neutral-500">Total Days</dt>
                                    <dd className="font-medium">{sub.total_days}</dd>
                                </div>
                                <div>
                                    <dt className="text-neutral-500">Remaining Days</dt>
                                    <dd className="font-medium">{sub.remaining_days}</dd>
                                </div>
                                <div>
                                    <dt className="text-neutral-500">Amount Paid</dt>
                                    <dd className="font-medium">NPR {Number(sub.amount).toLocaleString()}</dd>
                                </div>
                                <div>
                                    <dt className="text-neutral-500">Expires At</dt>
                                    <dd className="font-semibold text-emerald-400">{sub.expires_at}</dd>
                                </div>
                            </dl>
                        </div>
                    ))}
                </div>
            )}

            <h3 className="font-semibold text-sm text-neutral-400 mb-2">Order History</h3>

            {subscriptions.length === 0 ? (
                <p className="text-neutral-500 text-sm rounded-2xl glass-panel p-6 text-center">
                    No orders yet.
                </p>
            ) : (
                <div className="space-y-3">
                    {subscriptions.map((sub) => (
                        <div key={sub.id} className="rounded-2xl glass-panel p-4">
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <p className="text-xs font-semibold text-brand-400 uppercase tracking-wide">{sub.product_name}</p>
                                    <p className="font-semibold">{sub.plan_name}</p>
                                    {sub.awaiting_activation ? (
                                        <p className="text-xs font-semibold text-brand-400 mt-1">
                                            Payment approved — account will be activated soon.
                                        </p>
                                    ) : sub.needs_info ? (
                                        <p className="text-xs font-semibold text-red-400 mt-1">
                                            Admin needs more info: {sub.latest_proof_note}
                                        </p>
                                    ) : sub.is_rejected ? (
                                        <p className="text-xs font-semibold text-red-400 mt-1">
                                            Payment rejected{sub.latest_proof_note ? `: ${sub.latest_proof_note}` : '.'}
                                        </p>
                                    ) : sub.awaiting_review ? (
                                        <p className="text-xs font-semibold text-brand-400 mt-1">
                                            Payment submitted — waiting for admin approval.
                                        </p>
                                    ) : (
                                        <p className="text-xs text-neutral-500 mt-1">
                                            NPR {Number(sub.amount).toLocaleString()} &middot; {sub.expires_at ?? 'Not activated yet'}
                                        </p>
                                    )}
                                </div>
                                <span className={`text-xs font-bold px-2.5 py-1 rounded-full whitespace-nowrap ${statusStyles(sub)}`}>
                                    {statusLabel(sub)}
                                </span>
                            </div>

                            {(sub.can_pay || sub.can_delete) && (
                                <div className="flex items-center justify-between gap-4 mt-3">
                                    {sub.can_pay && (
                                        <Link
                                            href={route('subscriptions.pay', sub.id)}
                                            className="inline-flex items-center gap-1 text-sm font-semibold text-brand-400 hover:text-brand-300"
                                        >
                                            {sub.is_rejected ? 'Reapply' : sub.needs_info ? 'Resubmit Payment' : 'Complete Payment'} →
                                        </Link>
                                    )}
                                    {sub.can_delete && (
                                        <button
                                            type="button"
                                            onClick={() => confirmDelete(sub.id)}
                                            className="glass-btn-base text-xs font-bold px-3 py-1.5 rounded-xl bg-red-500/85 border border-white/10 hover:bg-red-500 text-white"
                                        >
                                            Delete
                                        </button>
                                    )}
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            )}
            </div>

            <Modal show={confirmingDeleteId !== null} onClose={closeModal} maxWidth="sm">
                <div className="p-6">
                    <h2 className="text-lg font-bold text-neutral-100">Delete this order?</h2>
                    <p className="mt-2 text-sm text-neutral-400">
                        Once deleted, you'll need to place a new order to subscribe to this plan.
                    </p>

                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={closeModal}>Cancel</SecondaryButton>
                        <DangerButton onClick={handleDelete} disabled={deleting}>
                            Delete
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </CustomerLayout>
    );
}
