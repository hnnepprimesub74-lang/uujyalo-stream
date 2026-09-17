const icons = [
    <svg key="tv" xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16v10H4V6zM9 20h6M12 16v4"/></svg>,
    <svg key="download" xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>,
    <svg key="devices" xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M9 4h11a1 1 0 011 1v11a1 1 0 01-1 1H9a1 1 0 01-1-1V5a1 1 0 011-1zM3 8h4v12H3a1 1 0 01-1-1V9a1 1 0 011-1z"/></svg>,
    <svg key="profiles" xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>,
];

export default function PerksGrid({ perks, accent }) {
    return (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            {perks.map((perk, index) => (
                <div key={index} className="rounded-2xl glass-panel p-5 transition duration-200 ease-glass hover:bg-white/[0.06]">
                    <div className="flex items-center gap-3">
                        <span
                            className="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
                            style={{ backgroundColor: `${accent}26`, color: accent }}
                        >
                            {icons[index % icons.length]}
                        </span>
                        <p className="font-bold">{perk.title}</p>
                    </div>
                    {perk.description && (
                        <p className="text-sm text-neutral-400 mt-3">{perk.description}</p>
                    )}
                </div>
            ))}
        </div>
    );
}
