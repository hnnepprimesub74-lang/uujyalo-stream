import { useState } from 'react';

export default function FaqAccordion({ faqs }) {
    const [openIndex, setOpenIndex] = useState(null);

    if (!faqs || faqs.length === 0) {
        return null;
    }

    return (
        <div className="space-y-2">
            {faqs.map((faq, index) => {
                const isOpen = openIndex === index;

                return (
                    <div key={index} className="rounded-xl glass-panel overflow-hidden">
                        <button
                            type="button"
                            onClick={() => setOpenIndex(isOpen ? null : index)}
                            className="w-full flex items-center justify-between gap-3 p-4 text-left"
                        >
                            <span className="font-semibold text-sm">{faq.question}</span>
                            <span className={`text-emerald-400 text-lg leading-none flex-shrink-0 transition-transform duration-200 ease-glass ${isOpen ? 'rotate-45' : ''}`}>
                                +
                            </span>
                        </button>
                        {isOpen && (
                            <p className="px-4 pb-4 text-sm text-neutral-400 whitespace-pre-line animate-glass-fade">{faq.answer}</p>
                        )}
                    </div>
                );
            })}
        </div>
    );
}
