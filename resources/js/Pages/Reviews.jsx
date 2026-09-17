import CustomerLayout from '@/Layouts/CustomerLayout';

export default function Reviews() {
    return (
        <CustomerLayout title="Reviews">
            <div className="max-w-2xl mx-auto">
                <h1 className="text-2xl font-extrabold mb-4">Customer Reviews</h1>

                <div className="rounded-2xl glass-panel p-8 text-center">
                    <div className="w-12 h-12 mx-auto rounded-full bg-neutral-800 flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6 text-neutral-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.956a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.447a1 1 0 00-.363 1.118l1.286 3.955c.3.922-.755 1.688-1.539 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.784.57-1.838-.196-1.539-1.118l1.286-3.955a1 1 0 00-.363-1.118l-3.367-2.447c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.285-3.956z"/></svg>
                    </div>
                    <p className="font-semibold">No reviews yet</p>
                    <p className="text-sm text-neutral-400 mt-1">
                        We're just getting started — check back soon to see what customers are saying.
                    </p>
                </div>
            </div>
        </CustomerLayout>
    );
}
