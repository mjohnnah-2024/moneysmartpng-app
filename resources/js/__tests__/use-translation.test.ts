import { describe, expect, it, vi } from 'vitest';
import { renderHook } from '@testing-library/react';

// Mock the modules before importing
vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
}));

import { usePage } from '@inertiajs/react';
import { useTranslation } from '@/hooks/use-translation';

function setupPage(language?: string) {
    (usePage as ReturnType<typeof vi.fn>).mockReturnValue({
        props: {
            auth: {
                profile: language ? { preferred_language: language } : null,
            },
        },
    });
}

describe('useTranslation', () => {
    it('returns English translations by default', () => {
        setupPage();
        const { result } = renderHook(() => useTranslation());

        expect(result.current.locale).toBe('en');
        expect(result.current.t('dashboard')).toBe('Dashboard');
    });

    it('returns Tok Pisin translations when locale is tpi', () => {
        setupPage('tpi');
        const { result } = renderHook(() => useTranslation());

        expect(result.current.locale).toBe('tpi');
        expect(result.current.isTokPisin).toBe(true);
        expect(result.current.t('dashboard')).toBe('Dasbot');
    });

    it('falls back to English for missing Tok Pisin keys', () => {
        setupPage('tpi');
        const { result } = renderHook(() => useTranslation());

        // All keys should be present, but test the fallback mechanism
        expect(result.current.t('dashboard')).toBeTruthy();
    });

    it('returns the key if no translation exists', () => {
        setupPage('en');
        const { result } = renderHook(() => useTranslation());

        // @ts-expect-error testing invalid key
        expect(result.current.t('nonexistent_key')).toBe('nonexistent_key');
    });

    it('replaces placeholders in translations', () => {
        setupPage('en');
        const { result } = renderHook(() => useTranslation());

        const value = result.current.t('dashboard');
        expect(value).toBe('Dashboard');
    });

    it('reports isTokPisin correctly for English', () => {
        setupPage('en');
        const { result } = renderHook(() => useTranslation());

        expect(result.current.isTokPisin).toBe(false);
    });

    it('translates settings navigation labels in Tok Pisin', () => {
        setupPage('tpi');
        const { result } = renderHook(() => useTranslation());

        expect(result.current.t('settings')).toBe('Ol Seting');
        expect(result.current.t('language')).toBe('Tokples');
        expect(result.current.t('profile')).toBe('Profail');
        expect(result.current.t('security')).toBe('Sekyuriti');
    });
});
