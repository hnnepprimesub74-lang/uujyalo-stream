import { useEffect } from 'react';
import { createPortal } from 'react-dom';

const maxWidthClasses = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
};

export default function Modal({ children, show = false, maxWidth = '2xl', onClose = () => {} }) {
    useEffect(() => {
        document.body.classList.toggle('overflow-y-hidden', show);

        return () => document.body.classList.remove('overflow-y-hidden');
    }, [show]);

    useEffect(() => {
        const handleKeydown = (e) => {
            if (e.key === 'Escape' && show) {
                onClose();
            }
        };

        document.addEventListener('keydown', handleKeydown);

        return () => document.removeEventListener('keydown', handleKeydown);
    }, [show, onClose]);

    if (!show) {
        return null;
    }

    return createPortal(
        <div className="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50 flex items-center justify-center">
            <div
                className="fixed inset-0 bg-neutral-950/70 backdrop-blur-md animate-glass-fade"
                onClick={onClose}
            />

            <div
                className={`relative glass-panel-strong rounded-glass overflow-hidden shadow-glass-lg w-full sm:mx-auto animate-glass-in ${maxWidthClasses[maxWidth]}`}
            >
                {children}
            </div>
        </div>,
        document.body
    );
}
