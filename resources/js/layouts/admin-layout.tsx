import { Link, usePage } from '@inertiajs/react';
import { BarChart3, CreditCard, LayoutDashboard, MessageSquare, Users } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { InactivityTimeout } from '@/components/inactivity-timeout';
import type { BreadcrumbItem } from '@/types';

const navItems = [
    { title: 'Dashboard', href: '/admin', icon: LayoutDashboard },
    { title: 'Users', href: '/admin/users', icon: Users },
    { title: 'Payments', href: '/admin/payments', icon: CreditCard },
    { title: 'AI Usage', href: '/admin/ai-usage', icon: MessageSquare },
];

export default function AdminLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {
    const { url } = usePage();

    return (
        <div className="flex min-h-screen bg-background">
            {/* Sidebar */}
            <aside className="hidden w-64 flex-shrink-0 border-r bg-card md:flex md:flex-col">
                <div className="flex h-16 items-center gap-2 border-b px-4">
                    <Link href="/admin">
                        <AppLogo />
                    </Link>
                </div>
                <nav className="flex-1 space-y-1 p-3">
                    {navItems.map((item) => {
                        const isActive = url === item.href || (item.href !== '/admin' && url.startsWith(item.href));
                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors ${
                                    isActive
                                        ? 'bg-primary/10 text-primary'
                                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                }`}
                            >
                                <item.icon className="h-4 w-4" />
                                {item.title}
                            </Link>
                        );
                    })}
                </nav>
                <div className="border-t p-3">
                    <Link
                        href="/dashboard"
                        className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-foreground"
                    >
                        <BarChart3 className="h-4 w-4" />
                        Back to App
                    </Link>
                </div>
            </aside>

            {/* Mobile Header */}
            <div className="flex flex-1 flex-col">
                <header className="flex h-16 items-center gap-4 border-b bg-card px-4 md:hidden">
                    <Link href="/admin">
                        <AppLogo />
                    </Link>
                    <span className="text-sm font-semibold text-primary">Admin</span>
                </header>

                {/* Mobile Nav */}
                <div className="flex gap-1 overflow-x-auto border-b bg-card px-2 py-1.5 md:hidden">
                    {navItems.map((item) => {
                        const isActive = url === item.href || (item.href !== '/admin' && url.startsWith(item.href));
                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`flex items-center gap-1.5 whitespace-nowrap rounded-md px-3 py-1.5 text-xs font-medium ${
                                    isActive
                                        ? 'bg-primary/10 text-primary'
                                        : 'text-muted-foreground'
                                }`}
                            >
                                <item.icon className="h-3.5 w-3.5" />
                                {item.title}
                            </Link>
                        );
                    })}
                </div>

                {/* Breadcrumbs */}
                {breadcrumbs.length > 0 && (
                    <div className="border-b bg-card/50 px-4 py-2">
                        <nav className="flex items-center gap-1.5 text-sm text-muted-foreground">
                            {breadcrumbs.map((crumb, i) => (
                                <span key={i} className="flex items-center gap-1.5">
                                    {i > 0 && <span>/</span>}
                                    {crumb.href ? (
                                        <Link href={crumb.href} className="hover:text-foreground">
                                            {crumb.title}
                                        </Link>
                                    ) : (
                                        <span className="text-foreground">{crumb.title}</span>
                                    )}
                                </span>
                            ))}
                        </nav>
                    </div>
                )}

                {/* Main Content */}
                <main className="flex-1 p-4 md:p-6">
                    {children}
                </main>
            </div>
            <InactivityTimeout />
        </div>
    );
}
