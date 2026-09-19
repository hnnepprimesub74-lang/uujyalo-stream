export default function StarRating({ value = 0, onChange, size = 'w-5 h-5', readOnly = false }) {
    const stars = [1, 2, 3, 4, 5];

    return (
        <div className="flex items-center gap-1">
            {stars.map((star) => (
                <button
                    key={star}
                    type="button"
                    disabled={readOnly}
                    onClick={() => onChange?.(star)}
                    className={readOnly ? 'cursor-default' : 'cursor-pointer'}
                    aria-label={`${star} star`}
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        className={`${size} ${star <= value ? 'text-amber-400 fill-amber-400' : 'text-neutral-600 fill-transparent'}`}
                        stroke="currentColor"
                        strokeWidth="1.5"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.956a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.447a1 1 0 00-.363 1.118l1.286 3.955c.3.922-.755 1.688-1.539 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.784.57-1.838-.196-1.539-1.118l1.286-3.955a1 1 0 00-.363-1.118l-3.367-2.447c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.285-3.956z"
                        />
                    </svg>
                </button>
            ))}
        </div>
    );
}
