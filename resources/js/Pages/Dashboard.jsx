import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import DangerButton from '@/Components/DangerButton';
import ReviewModal from '@/Components/ReviewModal';

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

function UsageRules({ sub }) {
    const rules = sub.usage_rules;
    if (!rules || rules.length === 0) return null;

    return (
        <div className="mt-3 rounded-xl bg-white/[0.04] border border-white/10 p-3">
            <p className="text-xs font-bold tracking-wider text-neutral-500 mb-1.5">USAGE RULES</p>
            <ul className="space-y-1 text-xs text-neutral-300">
                {rules.map((rule, i) => (
                    <li key={i} className="flex gap-1.5">
                        <span className="text-brand-400">•</span>
                        <span>{rule}</span>
                    </li>
                ))}
            </ul>
        </div>
    );
}

function ReviewButton({ sub, onClick }) {
    if (!sub.can_review) return null;

    return (
        <button
            type="button"
            onClick={onClick}
            className="glass-btn-base flex-shrink-0 flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl bg-amber-400/10 border border-amber-400/25 hover:bg-amber-400/20 text-amber-300"
        >
            <svg xmlns="http://www.w3.org/2000/svg" className="w-3.5 h-3.5" viewBox="0 0 24 24" fill={sub.review ? 'currentColor' : 'none'} stroke="currentColor" strokeWidth="2">
                <path strokeLinecap="round" strokeLinejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.956a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.447a1 1 0 00-.363 1.118l1.286 3.955c.3.922-.755 1.688-1.539 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.784.57-1.838-.196-1.539-1.118l1.286-3.955a1 1 0 00-.363-1.118l-3.367-2.447c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.285-3.956z" />
            </svg>
            {sub.review ? 'Edit Review' : 'Add Review'}
        </button>
    );
}

export default function Dashboard({ activeSubscriptions, subscriptions }) {
    const [confirmingDeleteId, setConfirmingDeleteId] = useState(null);
    const [deleting, setDeleting] = useState(false);
    const [reviewingSub, setReviewingSub] = useState(null);

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
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <p className="text-xs font-semibold text-brand-400 uppercase tracking-wide">{sub.product_name}</p>
                                    <p className="text-lg font-bold">{sub.plan_name}</p>
                                </div>
                                <ReviewButton sub={sub} onClick={() => setReviewingSub(sub)} />
                            </div>

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

                            <UsageRules sub={sub} />
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
                                <span className={`text-xs font-bold px-2.5 py-1 rounded-full whitespace-nowrap flex-shrink-0 ${statusStyles(sub)}`}>
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

            <ReviewModal subscription={reviewingSub} onClose={() => setReviewingSub(null)} />
        </CustomerLayout>
    );
}
