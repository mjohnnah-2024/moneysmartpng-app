import { Head, Link, router } from '@inertiajs/react';
import { ArrowUpDown, ChevronLeft, ChevronRight, Search } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin-layout';
import type { PaginatedData, Profile, Subscription } from '@/types';

type AdminUser = {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    created_at: string;
    profile: Profile | null;
    subscription: Subscription | null;
};

type Filters = {
    search: string;
    sort: string;
    direction: 'asc' | 'desc';
};

type Props = {
    users: PaginatedData<AdminUser>;
    filters: Filters;
};

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-PG', { year: 'numeric', month: 'short', day: 'numeric' });
}

export default function AdminUsers({ users, filters }: Props) {
    const [search, setSearch] = useState(filters.search);

    function handleSearch(e: React.FormEvent) {
        e.preventDefault();
        router.get('/admin/users', { search, sort: filters.sort, direction: filters.direction }, { preserveState: true });
    }

    function handleSort(field: string) {
        const direction = filters.sort === field && filters.direction === 'asc' ? 'desc' : 'asc';
        router.get('/admin/users', { search: filters.search, sort: field, direction }, { preserveState: true });
    }

    return (
        <>
            <Head title="Admin - Users" />
            <div className="flex flex-col gap-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Users Management</h1>
                    <Badge variant="outline">{users.total} users</Badge>
                </div>

                {/* Search */}
                <form onSubmit={handleSearch} className="flex gap-2">
                    <div className="relative flex-1 max-w-sm">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search by name or email..."
                            className="pl-9"
                        />
                    </div>
                    <Button type="submit" variant="outline">Search</Button>
                </form>

                {/* Table */}
                <Card>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b bg-muted/50">
                                        <th className="px-4 py-3 text-left font-medium">
                                            <button onClick={() => handleSort('name')} className="flex items-center gap-1 hover:text-foreground">
                                                Name <ArrowUpDown className="h-3 w-3" />
                                            </button>
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium">
                                            <button onClick={() => handleSort('email')} className="flex items-center gap-1 hover:text-foreground">
                                                Email <ArrowUpDown className="h-3 w-3" />
                                            </button>
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium">Plan</th>
                                        <th className="px-4 py-3 text-left font-medium">
                                            <button onClick={() => handleSort('created_at')} className="flex items-center gap-1 hover:text-foreground">
                                                Joined <ArrowUpDown className="h-3 w-3" />
                                            </button>
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium">Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {users.data.map((user) => (
                                        <tr key={user.id} className="border-b transition-colors hover:bg-muted/30">
                                            <td className="px-4 py-3">
                                                <Link href={`/admin/users/${user.id}`} className="font-medium text-primary hover:underline">
                                                    {user.name}
                                                </Link>
                                            </td>
                                            <td className="px-4 py-3 text-muted-foreground">{user.email}</td>
                                            <td className="px-4 py-3">
                                                <Badge variant={user.subscription?.status === 'active' ? 'default' : 'outline'}>
                                                    {user.subscription?.status === 'active' ? 'Premium' : 'Free'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-3 text-muted-foreground">{formatDate(user.created_at)}</td>
                                            <td className="px-4 py-3">
                                                {user.is_admin && <Badge className="bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Admin</Badge>}
                                            </td>
                                        </tr>
                                    ))}
                                    {users.data.length === 0 && (
                                        <tr>
                                            <td colSpan={5} className="px-4 py-8 text-center text-muted-foreground">
                                                No users found.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {/* Pagination */}
                {users.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Showing {users.from}–{users.to} of {users.total}
                        </p>
                        <div className="flex gap-1">
                            {users.links.map((link, i) => {
                                if (!link.url) return null;
                                const label = i === 0 ? <ChevronLeft className="h-4 w-4" /> : i === users.links.length - 1 ? <ChevronRight className="h-4 w-4" /> : null;
                                return (
                                    <Link
                                        key={i}
                                        href={link.url}
                                        className={`inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm ${
                                            link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'
                                        }`}
                                        dangerouslySetInnerHTML={label ? undefined : { __html: link.label }}
                                    >
                                        {label}
                                    </Link>
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

AdminUsers.layout = (page: React.ReactNode) => (
    <AdminLayout breadcrumbs={[{ title: 'Admin', href: '/admin' }, { title: 'Users' }]}>
        {page}
    </AdminLayout>
);
