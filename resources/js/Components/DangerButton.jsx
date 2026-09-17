export default function DangerButton({ className = '', disabled, children, ...props }) {
    return (
        <button
            {...props}
            type={props.type ?? 'submit'}
            disabled={disabled}
            className={
                `glass-btn-base inline-flex items-center px-4 py-2.5 bg-red-500/85 border border-white/10 rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 ${
                    disabled && 'opacity-25'
                } ` + className
            }
        >
            {children}
        </button>
    );
}
