export default function PrimaryButton({ className = '', disabled, children, ...props }) {
    return (
        <button
            {...props}
            type={props.type ?? 'submit'}
            disabled={disabled}
            className={
                `glass-btn-base inline-flex items-center px-4 py-2.5 bg-brand-400/90 border border-white/20 rounded-xl font-bold text-xs text-neutral-950 uppercase tracking-widest hover:bg-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 focus:ring-offset-neutral-900 ${
                    disabled && 'opacity-25'
                } ` + className
            }
        >
            {children}
        </button>
    );
}
