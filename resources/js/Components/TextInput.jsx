import { forwardRef, useEffect, useRef } from 'react';

export default forwardRef(function TextInput(
    { type = 'text', className = '', isFocused = false, ...props },
    ref
) {
    const localRef = useRef(null);
    const inputRef = ref ?? localRef;

    useEffect(() => {
        if (isFocused) {
            inputRef.current?.focus();
        }
    }, []);

    return (
        <input
            {...props}
            type={type}
            className={
                'glass-input focus:ring-1 focus:ring-brand-400/40 ' +
                className
            }
            ref={inputRef}
        />
    );
});
