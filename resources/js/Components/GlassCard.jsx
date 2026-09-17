const ROUNDED = {
    xl: 'rounded-xl',
    '2xl': 'rounded-2xl',
    '3xl': 'rounded-3xl',
    glass: 'rounded-glass',
};

export default function GlassCard({ as: Tag = 'div', strong = false, rounded = '2xl', className = '', children, ...props }) {
    return (
        <Tag
            className={`${strong ? 'glass-panel-strong' : 'glass-panel'} ${ROUNDED[rounded] ?? ROUNDED['2xl']} ${className}`}
            {...props}
        >
            {children}
        </Tag>
    );
}
