import { Link, usePage } from '@inertiajs/react';
import { ArrowLeftRight, BarChart3, Crown, Home, LayoutDashboard, MessageSquare, Target, Wallet } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { Auth, NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: Home,
    },
    {
        title: 'Transactions',
        href: '/transactions',
        icon: ArrowLeftRight,
    },
    {
        title: 'AI Coach',
        href: '/chat',
        icon: MessageSquare,
    },
    {
        title: 'Goals',
        href: '/goals',
        icon: Target,
    },
    {
        title: 'Budgets',
        href: '/budgets',
        icon: Wallet,
    },
    {
        title: 'Insights',
        href: '/insights',
        icon: BarChart3,
    },
    {
        title: 'Premium',
        href: '/premium',
        icon: Crown,
    },
];

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const isAdmin = auth.user.is_admin;

    const navItems = isAdmin
        ? [...mainNavItems, { title: 'Admin', href: '/admin', icon: LayoutDashboard }]
        : mainNavItems;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
