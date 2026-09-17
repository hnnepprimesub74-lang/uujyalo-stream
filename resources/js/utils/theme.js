const DEFAULT_ACCENT = '#e50914';

export function getAccentColor(product) {
    return product?.accent_color || DEFAULT_ACCENT;
}

export function contrastText(hex) {
    if (!hex) return '#0a0a0a';
    const c = hex.replace('#', '');
    const r = parseInt(c.substring(0, 2), 16);
    const g = parseInt(c.substring(2, 4), 16);
    const b = parseInt(c.substring(4, 6), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.6 ? '#0a0a0a' : '#ffffff';
}
