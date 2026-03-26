import { describe, expect, it } from 'vitest';
import { formatKina, formatKinaShort, formatDate, formatRelativeDate } from '@/lib/formatters';

describe('formatKina', () => {
    it('formats a number with 2 decimal places', () => {
        expect(formatKina(1234.5)).toMatch(/K\s*1,?234\.50/);
    });

    it('formats zero', () => {
        expect(formatKina(0)).toMatch(/K\s*0\.00/);
    });

    it('formats a string amount', () => {
        expect(formatKina('99.9')).toMatch(/K\s*99\.90/);
    });

    it('formats large amounts', () => {
        expect(formatKina(100000)).toMatch(/K\s*100,?000\.00/);
    });
});

describe('formatKinaShort', () => {
    it('formats amounts under 1000 as whole numbers', () => {
        expect(formatKinaShort(500)).toBe('K500');
    });

    it('formats thousands with k suffix', () => {
        expect(formatKinaShort(2500)).toBe('K2.5k');
    });

    it('formats millions with M suffix', () => {
        expect(formatKinaShort(1500000)).toBe('K1.5M');
    });
});

describe('formatDate', () => {
    it('formats an ISO date string', () => {
        const result = formatDate('2026-03-15T00:00:00.000Z');
        expect(result).toContain('2026');
        expect(result).toMatch(/Mar|03/);
    });

    it('formats a Date object', () => {
        const result = formatDate(new Date(2026, 0, 1));
        expect(result).toContain('2026');
        expect(result).toMatch(/Jan|01/);
    });
});

describe('formatRelativeDate', () => {
    it('returns "just now" for recent dates', () => {
        const now = new Date();
        expect(formatRelativeDate(now)).toBe('just now');
    });

    it('returns minutes ago for dates within the hour', () => {
        const fiveMinAgo = new Date(Date.now() - 5 * 60 * 1000);
        expect(formatRelativeDate(fiveMinAgo)).toBe('5m ago');
    });

    it('returns hours ago for dates within the day', () => {
        const twoHoursAgo = new Date(Date.now() - 2 * 60 * 60 * 1000);
        expect(formatRelativeDate(twoHoursAgo)).toBe('2h ago');
    });

    it('returns days ago for dates within a week', () => {
        const threeDaysAgo = new Date(Date.now() - 3 * 24 * 60 * 60 * 1000);
        expect(formatRelativeDate(threeDaysAgo)).toBe('3d ago');
    });

    it('returns a formatted date for older dates', () => {
        const oldDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
        const result = formatRelativeDate(oldDate);
        expect(result).toContain('2026');
    });
});
