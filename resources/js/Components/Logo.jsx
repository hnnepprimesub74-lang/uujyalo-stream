export default function Logo({ className = 'h-10 w-auto' }) {
    return (
        <img
            src="/images/logo-wordmark.webp"
            alt="Uujyalo Stream"
            className={`${className} object-contain`}
        />
    );
}
