import CustomerLayout from '@/Layouts/CustomerLayout';
import ReviewsList from '@/Components/ReviewsList';

export default function Reviews({ reviews }) {
    return (
        <CustomerLayout title="Reviews">
            <div className="max-w-2xl mx-auto">
                <h1 className="text-2xl font-extrabold mb-4">Customer Reviews</h1>

                <ReviewsList reviews={reviews} showProductName />
            </div>
        </CustomerLayout>
    );
}
