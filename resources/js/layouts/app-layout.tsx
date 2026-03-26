import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { BottomNav } from '@/components/bottom-nav';
import { InactivityTimeout } from '@/components/inactivity-timeout';
import type { BreadcrumbItem } from '@/types';

export default function AppLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {
    return (
        <>
            <AppLayoutTemplate breadcrumbs={breadcrumbs}>
                <div className="pb-16 md:pb-0">
                    {children}
                </div>
            </AppLayoutTemplate>
            <BottomNav />
            <InactivityTimeout />
        </>
    );
}
