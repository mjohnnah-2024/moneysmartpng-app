import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Crown, Shield, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/admin-layout';
import type { Profile, Subscription } from '@/types';

type ManualPaymentRecord = {
    id: number;
    reference_number: string;
    amount: string;
    months: number;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
};

type UserDetail = {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    created_at: string;
    profile: Profile | null;
    subscription: Subscription | null;
    manual_payments: ManualPaymentRecord[];
};

type Props = {
    user: UserDetail;
};

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-PG', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatKina(amount: number | string): string {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    return `K ${num.toLocaleString('en-PG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export default function AdminUserDetail({ user }: Props) {
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
    const [grantMonths, setGrantMonths] = useState('1');
    const isPremium = user.subscription?.status === 'active';

    const grantForm = useForm({});
    const revokeForm = useForm({});
    const deleteForm = useForm({});

    function handleGrant() {
        grantForm.post(`/admin/users/${user.id}/grant-premium`, {
            data: { months: parseInt(grantMonths) },
            preserveScroll: true,
        });
    }

    function handleRevoke() {
        revokeForm.post(`/admin/users/${user.id}/revoke-premium`, { preserveScroll: true });
    }

    function handleDelete() {
        deleteForm.delete(`/admin/users/${user.id}`);
    }

    return (
        <>
            <Head title={`Admin - ${user.name}`} />
            <div className="flex flex-col gap-4">
                <div className="flex items-center gap-3">
                    <Link href="/admin/users" className="rounded-md p-1 hover:bg-muted">
                        <ArrowLeft className="h-5 w-5" />
                    </Link>
                    <h1 className="text-2xl font-bold">{user.name}</h1>
                    {user.is_admin && <Badge className="bg-red-100 text-red-700">Admin</Badge>}
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    {/* User Info */}
                    <div className="md:col-span-2 flex flex-col gap-4">
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-base">User Information</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <dl className="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <dt className="text-xs text-muted-foreground">Email</dt>
                                        <dd className="text-sm font-medium">{user.email}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-xs text-muted-foreground">Joined</dt>
                                        <dd className="text-sm font-medium">{formatDate(user.created_at)}</dd>
                                    </div>
                                    {user.profile && (
                                        <>
                                            <div>
                                                <dt className="text-xs text-muted-foreground">Full Name</dt>
                                                <dd className="text-sm font-medium">{user.profile.full_name}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-muted-foreground">Phone</dt>
                                                <dd className="text-sm font-medium">{user.profile.phone_number || '—'}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-muted-foreground">Monthly Income</dt>
                                                <dd className="text-sm font-medium">
                                                    {user.profile.monthly_income ? formatKina(user.profile.monthly_income) : '—'}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-muted-foreground">Language</dt>
                                                <dd className="text-sm font-medium">{user.profile.preferred_language === 'tpi' ? 'Tok Pisin' : 'English'}</dd>
                                            </div>
                                        </>
                                    )}
                                </dl>
                            </CardContent>
                        </Card>

                        {/* Subscription Details */}
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-base">Subscription</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {user.subscription ? (
                                    <dl className="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <dt className="text-xs text-muted-foreground">Plan</dt>
                                            <dd>
                                                <Badge variant={isPremium ? 'default' : 'outline'}>
                                                    {isPremium ? 'Premium' : 'Free'}
                                                </Badge>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-xs text-muted-foreground">Status</dt>
                                            <dd className="text-sm font-medium capitalize">{user.subscription.status}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-xs text-muted-foreground">Payment Method</dt>
                                            <dd className="text-sm font-medium capitalize">{user.subscription.payment_method?.replace('_', ' ') || '—'}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-xs text-muted-foreground">Expires</dt>
                                            <dd className="text-sm font-medium">{user.subscription.ends_at ? formatDate(user.subscription.ends_at) : '—'}</dd>
                                        </div>
                                    </dl>
                                ) : (
                                    <p className="text-sm text-muted-foreground">No subscription record.</p>
                                )}
                            </CardContent>
                        </Card>

                        {/* Recent Payments */}
                        {user.manual_payments.length > 0 && (
                            <Card>
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-base">Recent Manual Payments</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex flex-col divide-y">
                                        {user.manual_payments.map((p) => (
                                            <div key={p.id} className="flex items-center justify-between py-2">
                                                <div>
                                                    <p className="text-sm font-medium">Ref: {p.reference_number}</p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {formatKina(p.amount)} · {p.months} month(s) · {formatDate(p.created_at)}
                                                    </p>
                                                </div>
                                                <Badge variant={p.status === 'approved' ? 'default' : p.status === 'rejected' ? 'destructive' : 'outline'}>
                                                    {p.status}
                                                </Badge>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Actions Panel */}
                    <div className="flex flex-col gap-4">
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-base">Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-3">
                                {/* Grant Premium */}
                                <div className="rounded-lg border p-3">
                                    <div className="flex items-center gap-2 mb-2">
                                        <Crown className="h-4 w-4 text-amber-600" />
                                        <span className="text-sm font-medium">Grant Premium</span>
                                    </div>
                                    <div className="flex gap-2">
                                        <Select value={grantMonths} onValueChange={setGrantMonths}>
                                            <SelectTrigger className="flex-1">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="1">1 Month</SelectItem>
                                                <SelectItem value="3">3 Months</SelectItem>
                                                <SelectItem value="6">6 Months</SelectItem>
                                                <SelectItem value="12">12 Months</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <Button size="sm" onClick={handleGrant} disabled={grantForm.processing}>
                                            Grant
                                        </Button>
                                    </div>
                                </div>

                                {/* Revoke Premium */}
                                {isPremium && (
                                    <div className="rounded-lg border p-3">
                                        <div className="flex items-center gap-2 mb-2">
                                            <Shield className="h-4 w-4 text-orange-600" />
                                            <span className="text-sm font-medium">Revoke Premium</span>
                                        </div>
                                        <Button variant="outline" size="sm" className="w-full" onClick={handleRevoke} disabled={revokeForm.processing}>
                                            Revoke Premium Access
                                        </Button>
                                    </div>
                                )}

                                {/* Delete User */}
                                {!user.is_admin && (
                                    <div className="rounded-lg border border-red-200 bg-red-50/50 p-3 dark:border-red-900 dark:bg-red-950/20">
                                        <div className="flex items-center gap-2 mb-2">
                                            <Trash2 className="h-4 w-4 text-red-600" />
                                            <span className="text-sm font-medium text-red-700 dark:text-red-400">Delete User</span>
                                        </div>
                                        {!showDeleteConfirm ? (
                                            <Button variant="destructive" size="sm" className="w-full" onClick={() => setShowDeleteConfirm(true)}>
                                                Delete User & All Data
                                            </Button>
                                        ) : (
                                            <div className="flex flex-col gap-2">
                                                <p className="text-xs text-red-600">This will permanently delete the user and all their data. This action cannot be undone.</p>
                                                <div className="flex gap-2">
                                                    <Button variant="destructive" size="sm" className="flex-1" onClick={handleDelete} disabled={deleteForm.processing}>
                                                        Confirm Delete
                                                    </Button>
                                                    <Button variant="outline" size="sm" onClick={() => setShowDeleteConfirm(false)}>
                                                        Cancel
                                                    </Button>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

AdminUserDetail.layout = (page: React.ReactNode) => (
    <AdminLayout breadcrumbs={[{ title: 'Admin', href: '/admin' }, { title: 'Users', href: '/admin/users' }, { title: 'User Detail' }]}>
        {page}
    </AdminLayout>
);
