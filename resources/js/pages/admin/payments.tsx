import { Head, useForm } from '@inertiajs/react';
import { Check, X } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin-layout';
import type { PaginatedData, Subscription } from '@/types';

type MobilePayment = {
    id: number;
    reference_number: string;
    phone: string;
    months: number;
    amount: string;
    status: 'pending' | 'approved' | 'rejected';
    admin_notes: string | null;
    created_at: string;
    user: { id: number; name: string; email: string } | null;
};

type StripePayment = Subscription & {
    user: { id: number; name: string; email: string } | null;
};

type Props = {
    stripePayments: PaginatedData<StripePayment>;
    mobilePayments: PaginatedData<MobilePayment>;
    activeTab: 'stripe' | 'mobile_money';
    pendingCount: number;
};

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-PG', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatKina(amount: number | string): string {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    return `K ${num.toLocaleString('en-PG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

const statusColors: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    approved: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    expired: 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
};

function RejectModal({ paymentId, onClose }: { paymentId: number; onClose: () => void }) {
    const form = useForm({ admin_notes: '' });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        form.post(`/admin/payments/${paymentId}/reject`, { onSuccess: onClose });
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50" onClick={onClose}>
            <div className="mx-4 w-full max-w-md rounded-lg bg-background p-6 shadow-xl" onClick={(e) => e.stopPropagation()}>
                <h3 className="text-lg font-semibold mb-4">Reject Payment</h3>
                <form onSubmit={handleSubmit}>
                    <div className="mb-4">
                        <label className="text-sm font-medium mb-1 block">Reason for rejection</label>
                        <Input
                            value={form.data.admin_notes}
                            onChange={(e) => form.setData('admin_notes', e.target.value)}
                            placeholder="Enter reason..."
                            required
                            maxLength={500}
                        />
                        {form.errors.admin_notes && <p className="mt-1 text-xs text-red-600">{form.errors.admin_notes}</p>}
                    </div>
                    <div className="flex justify-end gap-2">
                        <Button type="button" variant="outline" onClick={onClose}>Cancel</Button>
                        <Button type="submit" variant="destructive" disabled={form.processing}>Reject</Button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default function AdminPayments({ stripePayments, mobilePayments, activeTab, pendingCount }: Props) {
    const [tab, setTab] = useState(activeTab);
    const [rejectingId, setRejectingId] = useState<number | null>(null);

    return (
        <>
            <Head title="Admin - Payments" />
            <div className="flex flex-col gap-4">
                <h1 className="text-2xl font-bold">Payments Management</h1>

                {/* Tabs */}
                <div className="flex gap-2 border-b">
                    <button
                        onClick={() => setTab('mobile_money')}
                        className={`px-4 py-2 text-sm font-medium border-b-2 transition-colors ${
                            tab === 'mobile_money'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        Mobile Money
                        {pendingCount > 0 && (
                            <Badge variant="destructive" className="ml-2 h-5 min-w-5 px-1.5">{pendingCount}</Badge>
                        )}
                    </button>
                    <button
                        onClick={() => setTab('stripe')}
                        className={`px-4 py-2 text-sm font-medium border-b-2 transition-colors ${
                            tab === 'stripe'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        Stripe Payments
                    </button>
                </div>

                {/* Mobile Money Tab */}
                {tab === 'mobile_money' && (
                    <Card>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b bg-muted/50">
                                            <th className="px-4 py-3 text-left font-medium">User</th>
                                            <th className="px-4 py-3 text-left font-medium">Reference</th>
                                            <th className="px-4 py-3 text-left font-medium">Phone</th>
                                            <th className="px-4 py-3 text-left font-medium">Amount</th>
                                            <th className="px-4 py-3 text-left font-medium">Months</th>
                                            <th className="px-4 py-3 text-left font-medium">Status</th>
                                            <th className="px-4 py-3 text-left font-medium">Date</th>
                                            <th className="px-4 py-3 text-left font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {mobilePayments.data.map((payment) => (
                                            <tr key={payment.id} className="border-b">
                                                <td className="px-4 py-3">
                                                    <div>
                                                        <p className="font-medium">{payment.user?.name ?? 'Deleted'}</p>
                                                        <p className="text-xs text-muted-foreground">{payment.user?.email}</p>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 font-mono text-xs">{payment.reference_number}</td>
                                                <td className="px-4 py-3">{payment.phone}</td>
                                                <td className="px-4 py-3 font-medium">{formatKina(payment.amount)}</td>
                                                <td className="px-4 py-3">{payment.months}</td>
                                                <td className="px-4 py-3">
                                                    <Badge variant="outline" className={statusColors[payment.status]}>
                                                        {payment.status}
                                                    </Badge>
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">{formatDate(payment.created_at)}</td>
                                                <td className="px-4 py-3">
                                                    {payment.status === 'pending' && (
                                                        <div className="flex gap-1">
                                                            <ApproveButton paymentId={payment.id} />
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                className="h-7 text-red-600 hover:text-red-700"
                                                                onClick={() => setRejectingId(payment.id)}
                                                            >
                                                                <X className="h-3 w-3" />
                                                            </Button>
                                                        </div>
                                                    )}
                                                    {payment.admin_notes && (
                                                        <p className="mt-1 text-xs text-muted-foreground italic">{payment.admin_notes}</p>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                        {mobilePayments.data.length === 0 && (
                                            <tr>
                                                <td colSpan={8} className="px-4 py-8 text-center text-muted-foreground">
                                                    No mobile money payments.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Stripe Tab */}
                {tab === 'stripe' && (
                    <Card>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b bg-muted/50">
                                            <th className="px-4 py-3 text-left font-medium">User</th>
                                            <th className="px-4 py-3 text-left font-medium">Plan</th>
                                            <th className="px-4 py-3 text-left font-medium">Status</th>
                                            <th className="px-4 py-3 text-left font-medium">Started</th>
                                            <th className="px-4 py-3 text-left font-medium">Expires</th>
                                            <th className="px-4 py-3 text-left font-medium">Stripe ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {stripePayments.data.map((payment) => (
                                            <tr key={payment.id} className="border-b">
                                                <td className="px-4 py-3">
                                                    <div>
                                                        <p className="font-medium">{payment.user?.name ?? 'Deleted'}</p>
                                                        <p className="text-xs text-muted-foreground">{payment.user?.email}</p>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 capitalize">{payment.plan}</td>
                                                <td className="px-4 py-3">
                                                    <Badge variant="outline" className={statusColors[payment.status]}>
                                                        {payment.status}
                                                    </Badge>
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">{payment.starts_at ? formatDate(payment.starts_at) : '—'}</td>
                                                <td className="px-4 py-3 text-muted-foreground">{payment.ends_at ? formatDate(payment.ends_at) : '—'}</td>
                                                <td className="px-4 py-3 font-mono text-xs text-muted-foreground">{payment.stripe_subscription_id || '—'}</td>
                                            </tr>
                                        ))}
                                        {stripePayments.data.length === 0 && (
                                            <tr>
                                                <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                                                    No Stripe payments.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>

            {rejectingId && <RejectModal paymentId={rejectingId} onClose={() => setRejectingId(null)} />}
        </>
    );
}

function ApproveButton({ paymentId }: { paymentId: number }) {
    const form = useForm({});

    return (
        <Button
            variant="outline"
            size="sm"
            className="h-7 text-green-600 hover:text-green-700"
            onClick={() => form.post(`/admin/payments/${paymentId}/approve`)}
            disabled={form.processing}
        >
            <Check className="h-3 w-3" />
        </Button>
    );
}

AdminPayments.layout = (page: React.ReactNode) => (
    <AdminLayout breadcrumbs={[{ title: 'Admin', href: '/admin' }, { title: 'Payments' }]}>
        {page}
    </AdminLayout>
);
