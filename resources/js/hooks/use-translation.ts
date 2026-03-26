import { usePage } from '@inertiajs/react';
import en from '@/lib/i18n/en.json';
import tpi from '@/lib/i18n/tpi.json';

type TranslationKey = keyof typeof en;

const translations: Record<string, Record<string, string>> = { en, tpi };

export function useTranslation() {
    const { auth } = usePage<{ auth: { profile?: { preferred_language?: string } | null } }>().props;
    const locale = auth?.profile?.preferred_language ?? 'en';

    function t(key: TranslationKey): string {
        return translations[locale]?.[key] ?? translations['en'][key] ?? key;
    }

    return { t, locale };
}
