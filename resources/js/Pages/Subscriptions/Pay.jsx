import { useEffect, useState } from 'react';
import { useForm } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import { getAccentColor, contrastText } from '@/utils/theme';

function useImagePreview(file) {
    const [preview, setPreview] = useState(null);

    useEffect(() => {
        if (!file) {
            setPreview(null);
            return;
        }

        const url = URL.createObjectURL(file);
        setPreview(url);

        return () => URL.revokeObjectURL(url);
    }, [file]);

    return preview;
}

export default function Pay({ subscription, instructions, qrCodeUrl, needsInfoNote, rejectionNote }) {
    const accent = getAccentColor(subscription.plan.product);
    const accentText = contrastText(accent);

    const { data, setData, post, processing, errors } = useForm({
        payment_method: 'Bank Transfer',
        screenshot: null,
        screenshot_2: null,
        customer_note: '',
    });

    const screenshotPreview = useImagePreview(data.screenshot);
    const screenshot2Preview = useImagePreview(data.screenshot_2);

    const submit = (e) => {
        e.preventDefault();
        post(route('subscriptions.storeProof', subscription.id), {
            forceFormData: true,
        });
    };

    return (
        <CustomerLayout title="Complete Your Payment">
            <div className="max-w-xl mx-auto">
            <h1 className="text-2xl font-extrabold mb-4">Complete Your Payment</h1>

            {rejectionNote ? (
                <div className="rounded-2xl backdrop-blur-md bg-red-500/10 border border-red-500/25 text-red-300 text-sm px-4 py-3 mb-4 shadow-glass-sm">
                    <p className="font-semibold mb-1">Your previous payment was rejected:</p>
                    <p>{rejectionNote}</p>
                    <p className="mt-2 text-red-300/80">Please review and resubmit below.</p>
                </div>
            ) : needsInfoNote ? (
                <div className="rounded-2xl backdrop-blur-md bg-red-500/10 border border-red-500/25 text-red-300 text-sm px-4 py-3 mb-4 shadow-glass-sm">
                    <p className="font-semibold mb-1">Admin needs more info before approving your payment:</p>
                    <p>{needsInfoNote}</p>
                </div>
            ) : null}

            <div className="rounded-2xl glass-panel p-4 mb-4">
                <p className="font-semibold">{subscription.plan.full_name}</p>
                <p className="text-sm text-neutral-400 mt-0.5">{subscription.plan.duration_days} days access</p>
                <p className="text-green-400 font-extrabold text-xl mt-2">
                    NPR {Number(subscription.amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                </p>

                <div className="mt-4 glass-panel-strong rounded-xl p-4 text-sm text-neutral-300 whitespace-pre-line">
                    {instructions}
                </div>

                {qrCodeUrl && (
                    <div className="mt-4 flex flex-col items-center">
                        <img
                            src={qrCodeUrl}
                            alt="Payment QR Code"
                            className="max-w-[240px] w-full rounded-xl border border-white/10 bg-white p-2 shadow-glass-sm"
                        />
                        <a
                            href={qrCodeUrl}
                            download="payment-qr-code.png"
                            className="glass-btn-base mt-3 text-sm font-semibold rounded-xl glass-panel px-4 py-2 text-neutral-200 hover:bg-white/[0.07]"
                        >
                            Download QR Code
                        </a>
                    </div>
                )}
            </div>

            <div className="rounded-2xl glass-panel p-4">
                <h3 className="font-semibold mb-4">
                    {rejectionNote || needsInfoNote ? 'Resubmit Payment Proof' : 'Submit Payment Proof'}
                </h3>

                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <InputLabel htmlFor="screenshot" value="Payment Screenshot" className="mb-1" />
                        <input
                            id="screenshot"
                            type="file"
                            accept="image/*"
                            required
                            onChange={(e) => setData('screenshot', e.target.files[0])}
                            style={{ '--tw-file-bg': accent, '--tw-file-text': accentText }}
                            className="block w-full text-sm text-neutral-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-[var(--tw-file-bg)] file:text-[var(--tw-file-text)] file:font-semibold"
                        />
                        <InputError message={errors.screenshot} className="mt-1" />
                        {screenshotPreview && (
                            <img
                                src={screenshotPreview}
                                alt="Payment screenshot preview"
                                className="mt-2 max-h-64 rounded-lg border border-white/10"
                            />
                        )}
                    </div>

                    {(rejectionNote || needsInfoNote) && (
                        <div>
                            <InputLabel htmlFor="screenshot_2" value="Additional Screenshot (optional)" className="mb-1" />
                            <input
                                id="screenshot_2"
                                type="file"
                                accept="image/*"
                                onChange={(e) => setData('screenshot_2', e.target.files[0])}
                                className="block w-full text-sm text-neutral-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-white/10 file:text-neutral-100 file:font-semibold"
                            />
                            <InputError message={errors.screenshot_2} className="mt-1" />
                            {screenshot2Preview && (
                                <img
                                    src={screenshot2Preview}
                                    alt="Additional screenshot preview"
                                    className="mt-2 max-h-64 rounded-lg border border-white/10"
                                />
                            )}
                        </div>
                    )}

                    <div>
                        <InputLabel htmlFor="customer_note" value="Note to Admin (optional)" className="mb-1" />
                        <textarea
                            id="customer_note"
                            rows={3}
                            value={data.customer_note}
                            onChange={(e) => setData('customer_note', e.target.value)}
                            placeholder="Anything the admin should know about this payment?"
                            className="block w-full glass-input"
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        style={{ backgroundColor: accent, color: accentText }}
                        className="glass-btn-base w-full border border-white/20 font-bold rounded-xl py-3.5 disabled:opacity-60"
                    >
                        {rejectionNote || needsInfoNote ? 'Resubmit for Verification' : 'Submit for Verification'}
                    </button>
                </form>
            </div>
            </div>
        </CustomerLayout>
    );
}

