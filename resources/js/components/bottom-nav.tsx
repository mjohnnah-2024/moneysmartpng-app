import { Link, usePage } from '@inertiajs/react';
import { Home, ArrowLeftRight, MessageSquare, Target, Settings } from 'lucide-react';
import { dashboard } from '@/routes';
import type { ComponentType } from 'react';

type NavTab = {
    title: string;
    href: string;
    icon: ComponentType<{ className?: string }>;
    match: string;
};

const tabs: NavTab[] = [
    { title: 'Home', href: '/dashboard', icon: Home, match: 'dashboard' },
    { title: 'Transactions', href: '/transactions', icon: ArrowLeftRight, match: 'transactions' },
    { title: 'AI Coach', href: '/chat', icon: MessageSquare, match: 'chat' },
    { title: 'Goals', href: '/goals', icon: Target, match: 'goals' },
    { title: 'Settings', href: '/settings/profile', icon: Settings, match: 'settings' },
];

export function BottomNav() {
    const { url } = usePage();

    return (
        <nav className="fixed bottom-0 left-0 right-0 z-50 border-t border-border bg-card safe-bottom md:hidden">
            <div className="flex items-center justify-around py-1">
                {tabs.map((tab) => {
                    const isActive = url.startsWith(`/${tab.match}`);
                    return (
                        <Link
                            key={tab.title}
                            href={tab.href}
                            prefetch
                            className={`flex flex-1 flex-col items-center gap-0.5 px-1 py-2 text-xs transition-colors ${
                                isActive
                                    ? 'text-primary font-medium'
                                    : 'text-muted-foreground'
                            }`}
                        >
                            <tab.icon className={`h-5 w-5 ${isActive ? 'text-primary' : ''}`} />
                            <span>{tab.title}</span>
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}
