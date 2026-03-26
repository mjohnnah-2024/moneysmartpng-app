import { usePage } from '@inertiajs/react';
import en from '@/lib/i18n/en.json';
import tpi from '@/lib/i18n/tpi.json';

export type TranslationKey = keyof typeof en;
export type Locale = 'en' | 'tpi';

const translations: Record<string, Record<string, string>> = { en, tpi };

export function useTranslation() {
    const { auth } = usePage<{ auth: { profile?: { preferred_language?: string } | null } }>().props;
    const locale: Locale = (auth?.profile?.preferred_language as Locale) ?? 'en';
    const isTokPisin = locale === 'tpi';

    function t(key: TranslationKey, replacements?: Record<string, string | number>): string {
        let value = translations[locale]?.[key] ?? translations['en'][key] ?? key;
        if (replacements) {
            Object.entries(replacements).forEach(([k, v]) => {
                value = value.replace(`:${k}`, String(v));
            });
        }
        return value;
    }

    return { t, locale, isTokPisin };
}
