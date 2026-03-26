import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useTranslation } from '@/hooks/use-translation';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editLanguage } from '@/routes/language';
import { edit as editNotifications } from '@/routes/notifications';
import { edit } from '@/routes/profile';
import { index as referralIndex } from '@/routes/referral';
import { edit as editSecurity } from '@/routes/security';
import { edit as editSubscription } from '@/routes/subscription';
import type { NavItem } from '@/types';

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const { t } = useTranslation();

    const sidebarNavItems: NavItem[] = [
        { title: t('profile'), href: edit(), icon: null },
        { title: t('security'), href: editSecurity(), icon: null },
        { title: t('subscription'), href: editSubscription(), icon: null },
        { title: t('language'), href: editLanguage(), icon: null },
        { title: t('referral'), href: referralIndex(), icon: null },
        { title: t('appearance'), href: editAppearance(), icon: null },
        { title: 'Notifications', href: editNotifications(), icon: null },
    ];

    return (
        <div className="px-4 py-6">
            <Heading
                title={t('settings')}
                description={t('manage_settings')}
            />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav
                        className="flex flex-col space-y-1 space-x-0"
                        aria-label="Settings"
                    >
                        {sidebarNavItems.map((item, index) => (
                            <Button
                                key={`${toUrl(item.href)}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('w-full justify-start', {
                                    'bg-muted': isCurrentOrParentUrl(item.href),
                                })}
                            >
                                <Link href={item.href}>
                                    {item.icon && (
                                        <item.icon className="h-4 w-4" />
                                    )}
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
