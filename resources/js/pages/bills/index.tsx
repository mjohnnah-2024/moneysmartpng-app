import { Head, Link, usePage, router } from '@inertiajs/react';
import { Plus, Receipt, Check, Pencil, Trash2 } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { formatKina } from '@/lib/formatters';
import type { RecurringExpense, BillsSummary, Flash } from '@/types';

type Props = {
    bills: RecurringExpense[];
    summary: BillsSummary;
    categories: string[];
};

export default function BillsIndex({ bills, summary }: Props) {
    const { flash } = usePage<{ flash: Flash }>().props;

    function handleMarkPaid(billId: number) {
        router.post(`/bills/${billId}/mark-paid`);
    }

    function handleDelete(billId: number) {
        if (confirm('Are you sure you want to delete this bill?')) {
            router.delete(`/bills/${billId}`);
        }
    }

    return (
        <>
            <Head title="Recurring Bills" />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Recurring Bills</h1>
                    <Button asChild size="sm">
                        <Link href="/bills/create">
                            <Plus className="mr-1 h-4 w-4" />
                            Add Bill
                        </Link>
                    </Button>
                </div>

                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                        {flash.success}
                    </div>
                )}

                {/* Summary */}
                <div className="grid gap-3 grid-cols-2 sm:grid-cols-4">
                    <Card>
                        <CardContent className="p-3 text-center">
                            <p className="text-xs text-muted-foreground">Total Bills</p>
                            <p className="text-lg font-bold">{summary.totalBills}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-3 text-center">
                            <p className="text-xs text-muted-foreground">Total Amount</p>
                            <p className="text-lg font-bold">{formatKina(summary.totalAmount)}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-3 text-center">
                            <p className="text-xs text-muted-foreground">Paid</p>
                            <p className="text-lg font-bold text-emerald-600">{summary.paidCount}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-3 text-center">
                            <p className="text-xs text-muted-foreground">Unpaid</p>
                            <p className="text-lg font-bold text-red-600">{formatKina(summary.unpaidAmount)}</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Bills List */}
                {bills.length > 0 ? (
                    <div className="flex flex-col gap-3">
                        {bills.map((bill) => (
                            <Card key={bill.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-3">
                                            <div className={`rounded-full p-2 ${bill.is_paid ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-amber-100 dark:bg-amber-900/30'}`}>
                                                <Receipt className={`h-4 w-4 ${bill.is_paid ? 'text-emerald-600' : 'text-amber-600'}`} />
                                            </div>
                                            <div>
                                                <p className="font-medium">{bill.name}</p>
                                                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                                    <span>{bill.category}</span>
                                                    <span>·</span>
                                                    <span className="capitalize">{bill.frequency}</span>
                                                    <span>·</span>
                                                    <span>Due day {bill.due_day}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <p className="font-semibold">{formatKina(bill.amount)}</p>
                                            {bill.is_paid ? (
                                                <Badge variant="secondary" className="text-emerald-600">Paid</Badge>
                                            ) : (
                                                <Badge variant="secondary" className="text-amber-600">Unpaid</Badge>
                                            )}
                                        </div>
                                    </div>
                                    <div className="mt-3 flex justify-end gap-2">
                                        {!bill.is_paid && (
                                            <Button size="sm" variant="outline" onClick={() => handleMarkPaid(bill.id)}>
                                                <Check className="mr-1 h-3.5 w-3.5" />
                                                Mark Paid
                                            </Button>
                                        )}
                                        <Button size="sm" variant="ghost" asChild>
                                            <Link href={`/bills/${bill.id}/edit`}>
                                                <Pencil className="h-3.5 w-3.5" />
                                            </Link>
                                        </Button>
                                        <Button size="sm" variant="ghost" className="text-destructive" onClick={() => handleDelete(bill.id)}>
                                            <Trash2 className="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <Card>
                        <CardContent className="p-8 text-center">
                            <Receipt className="mx-auto h-10 w-10 text-muted-foreground/50 mb-3" />
                            <p className="text-muted-foreground">No recurring bills yet.</p>
                            <Button asChild size="sm" className="mt-3">
                                <Link href="/bills/create">Add your first bill</Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}

BillsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Bills', href: '/bills' },
    ],
};
