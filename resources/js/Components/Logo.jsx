export default function Logo({ className = 'w-10 h-10' }) {
    return (
        <svg
            viewBox="0 0 40 40"
            className={className}
            xmlns="http://www.w3.org/2000/svg"
            role="img"
            aria-label="Uujyalo Stream"
        >
            <defs>
                <linearGradient id="uj-logo-bg" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stopColor="#e94848" />
                    <stop offset="100%" stopColor="#e50914" />
                </linearGradient>
            </defs>

            <rect width="40" height="40" rx="11" fill="url(#uj-logo-bg)" />

            {/* Cradle arc — suggests the "U" of Uujyalo */}
            <path
                d="M11 17 v6 a9 9 0 0018 0 v-6"
                fill="none"
                stroke="#0a0a0a"
                strokeOpacity="0.35"
                strokeWidth="3"
                strokeLinecap="round"
            />

            {/* Play mark — the "Stream" */}
            <path d="M17 13.5 L27 20 L17 26.5 Z" fill="#0a0a0a" />
        </svg>
    );
}
