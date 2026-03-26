import { Head, Link, router, usePage } from '@inertiajs/react';
import { Plus, Pencil, Trash2, ChevronLeft, ChevronRight, Wallet } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import type { Flash } from '@/types';

type BudgetRow = {
    id: number;
    category: string;
    amount_limit: number;
    month: string;
    spent: number;
    percentage: number;
};

type Props = {
    budgets: BudgetRow[];
    month: string;
    totalBudget: number;
    totalSpent: number;
    categories: string[];
};

function formatKina(amount: number): string {
    return `K ${amount.toLocaleString('en-PG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function statusColor(pct: number): string {
    if (pct >= 100) return 'bg-red-500';
    if (pct >= 75) return 'bg-amber-500';
    return 'bg-emerald-500';
}

function statusTextColor(pct: number): string {
    if (pct >= 100) return 'text-red-600';
    if (pct >= 75) return 'text-amber-600';
    return 'text-emerald-600';
}

export default function BudgetsIndex({ budgets, month, totalBudget, totalSpent }: Props) {
    const { flash } = usePage<{ flash: Flash }>().props;

    function changeMonth(offset: number) {
        const [y, m] = month.split('-').map(Number);
        const d = new Date(y, m - 1 + offset, 1);
        const newMonth = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
        router.get('/budgets', { month: newMonth }, { preserveState: true, replace: true });
    }

    function handleDelete(id: number) {
        if (confirm('Are you sure you want to delete this budget?')) {
            router.delete(`/budgets/${id}`);
        }
    }

    const totalPct = totalBudget > 0 ? Math.round((totalSpent / totalBudget) * 100) : 0;

    const monthLabel = new Date(month + '-01').toLocaleDateString('en-PG', { month: 'long', year: 'numeric' });

    return (
        <>
            <Head title="Budgets" />
            <div className="flex flex-col gap-4 p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Budgets</h1>
                    <Button asChild size="sm">
                        <Link href="/budgets/create">
                            <Plus className="mr-1 h-4 w-4" />
                            Add
                        </Link>
                    </Button>
                </div>

                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-300">
                        {flash.success}
                    </div>
                )}

                {/* Month Selector */}
                <div className="flex items-center justify-center gap-4">
                    <Button variant="ghost" size="icon" onClick={() => changeMonth(-1)}>
                        <ChevronLeft className="h-4 w-4" />
                    </Button>
                    <span className="text-sm font-medium">{monthLabel}</span>
                    <Button variant="ghost" size="icon" onClick={() => changeMonth(1)}>
                        <ChevronRight className="h-4 w-4" />
                    </Button>
                </div>

                {/* Summary Card */}
                {budgets.length > 0 && (
                    <Card>
                        <CardContent className="p-4">
                            <div className="mb-2 flex items-center justify-between text-sm">
                                <span className="text-muted-foreground">Total Budget</span>
                                <span className={`font-semibold ${statusTextColor(totalPct)}`}>{totalPct}%</span>
                            </div>
                            <div className="mb-2 h-3 overflow-hidden rounded-full bg-muted">
                                <div
                                    className={`h-full rounded-full transition-all ${statusColor(totalPct)}`}
                                    style={{ width: `${Math.min(totalPct, 100)}%` }}
                                />
                            </div>
                            <div className="flex justify-between text-xs text-muted-foreground">
                                <span>Spent: {formatKina(totalSpent)}</span>
                                <span>Limit: {formatKina(totalBudget)}</span>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Budget List */}
                {budgets.length > 0 ? (
                    <div className="flex flex-col gap-2">
                        {budgets.map((b) => (
                            <Card key={b.id}>
                                <CardContent className="p-3">
                                    <div className="mb-2 flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <Wallet className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-sm font-medium">{b.category}</span>
                                        </div>
                                        <div className="flex items-center gap-1">
                                            <span className={`text-xs font-semibold ${statusTextColor(b.percentage)}`}>
                                                {b.percentage}%
                                            </span>
                                            <Button variant="ghost" size="icon" className="h-7 w-7" asChild>
                                                <Link href={`/budgets/${b.id}/edit`}>
                                                    <Pencil className="h-3.5 w-3.5" />
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="icon" className="h-7 w-7 text-destructive" onClick={() => handleDelete(b.id)}>
                                                <Trash2 className="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </div>
                                    <div className="mb-1 h-2 overflow-hidden rounded-full bg-muted">
                                        <div
                                            className={`h-full rounded-full transition-all ${statusColor(b.percentage)}`}
                                            style={{ width: `${Math.min(b.percentage, 100)}%` }}
                                        />
                                    </div>
                                    <div className="flex justify-between text-xs text-muted-foreground">
                                        <span>{formatKina(b.spent)} spent</span>
                                        <span>{formatKina(b.amount_limit)} limit</span>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <p className="text-muted-foreground">No budgets set for this month.</p>
                            <Button asChild className="mt-4" size="sm">
                                <Link href="/budgets/create">Set your first budget</Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}
