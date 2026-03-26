/**
 * Currency and date formatting helpers for PNG (Kina).
 */

export function formatKina(amount: number | string): string {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    return `K ${num.toLocaleString('en-PG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export function formatKinaShort(amount: number): string {
    if (amount >= 1_000_000) return `K${(amount / 1_000_000).toFixed(1)}M`;
    if (amount >= 1000) return `K${(amount / 1000).toFixed(1)}k`;
    return `K${amount.toFixed(0)}`;
}

export function formatDate(date: string | Date): string {
    const d = typeof date === 'string' ? new Date(date) : date;
    return d.toLocaleDateString('en-PG', { year: 'numeric', month: 'short', day: 'numeric' });
}

export function formatRelativeDate(date: string | Date): string {
    const d = typeof date === 'string' ? new Date(date) : date;
    const now = new Date();
    const diffMs = now.getTime() - d.getTime();
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffSecs / 60);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffSecs < 60) return 'just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;

    return formatDate(d);
}
