export default function SecondaryButton({
    type = 'button',
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            type={type}
            disabled={disabled}
            className={
                `glass-btn-base inline-flex items-center px-4 py-2.5 bg-white/[0.05] border border-white/10 rounded-xl font-semibold text-xs text-neutral-200 uppercase tracking-widest hover:bg-white/[0.09] focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 focus:ring-offset-neutral-900 ${
                    disabled && 'opacity-25'
                } ` + className
            }
        >
            {children}
        </button>
    );
}
