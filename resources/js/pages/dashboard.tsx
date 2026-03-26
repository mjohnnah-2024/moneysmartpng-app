import { Head, Link } from '@inertiajs/react';
import { ArrowDownLeft, ArrowUpRight, Plus, TrendingUp } from 'lucide-react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from 'recharts';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';
import type { Profile, Transaction, SpendingCategory, MonthlySummary } from '@/types';

type Props = {
    profile: Profile | null;
    summary: MonthlySummary;
    spendingByCategory: SpendingCategory[];
    recentTransactions: Transaction[];
};

const CHART_COLORS = [
    'var(--color-chart-1)',
    'var(--color-chart-2)',
    'var(--color-chart-3)',
    'var(--color-chart-4)',
    'var(--color-chart-5)',
];

function formatKina(amount: number): string {
    return `K ${amount.toLocaleString('en-PG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export default function Dashboard({ profile, summary, spendingByCategory, recentTransactions }: Props) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-col gap-4 p-4">
                {/* Greeting */}
                <div>
                    <h1 className="text-2xl font-bold text-foreground">
                        Gutpela dei, {profile?.full_name?.split(' ')[0] ?? 'there'}!
                    </h1>
                    <p className="text-sm text-muted-foreground">{summary.month} Summary</p>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-3 grid-cols-1 sm:grid-cols-3">
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-xs text-muted-foreground">Income</p>
                                    <p className="text-xl font-bold text-green-600">{formatKina(summary.totalIncome)}</p>
                                </div>
                                <div className="rounded-full bg-green-100 p-2 dark:bg-green-900/30">
                                    <ArrowDownLeft className="h-4 w-4 text-green-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-xs text-muted-foreground">Expenses</p>
                                    <p className="text-xl font-bold text-red-600">{formatKina(summary.totalExpense)}</p>
                                </div>
                                <div className="rounded-full bg-red-100 p-2 dark:bg-red-900/30">
                                    <ArrowUpRight className="h-4 w-4 text-red-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-xs text-muted-foreground">Remaining</p>
                                    <p className={`text-xl font-bold ${summary.remaining >= 0 ? 'text-primary' : 'text-red-600'}`}>
                                        {formatKina(summary.remaining)}
                                    </p>
                                </div>
                                <div className="rounded-full bg-primary/10 p-2">
                                    <TrendingUp className="h-4 w-4 text-primary" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Spending Chart + Quick Add */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">Spending by Category</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {spendingByCategory.length > 0 ? (
                                <div className="flex items-center gap-4">
                                    <ResponsiveContainer width={120} height={120}>
                                        <PieChart>
                                            <Pie
                                                data={spendingByCategory}
                                                dataKey="amount"
                                                nameKey="category"
                                                cx="50%"
                                                cy="50%"
                                                outerRadius={55}
                                                innerRadius={30}
                                            >
                                                {spendingByCategory.map((_, i) => (
                                                    <Cell key={i} fill={CHART_COLORS[i % CHART_COLORS.length]} />
                                                ))}
                                            </Pie>
                                            <Tooltip formatter={(value: number) => formatKina(value)} />
                                        </PieChart>
                                    </ResponsiveContainer>
                                    <div className="flex flex-col gap-1.5 text-sm">
                                        {spendingByCategory.slice(0, 5).map((item, i) => (
                                            <div key={item.category} className="flex items-center gap-2">
                                                <div
                                                    className="h-2.5 w-2.5 rounded-full"
                                                    style={{ backgroundColor: CHART_COLORS[i % CHART_COLORS.length] }}
                                                />
                                                <span className="text-muted-foreground">{item.category}</span>
                                                <span className="ml-auto font-medium">{formatKina(item.amount)}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground py-6 text-center">
                                    No expenses recorded this month yet.
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-base">Recent Transactions</CardTitle>
                                <Button variant="ghost" size="sm" asChild>
                                    <Link href="/transactions">View all</Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {recentTransactions.length > 0 ? (
                                <div className="flex flex-col divide-y">
                                    {recentTransactions.map((t) => (
                                        <div key={t.id} className="flex items-center justify-between py-2.5">
                                            <div>
                                                <p className="text-sm font-medium">{t.category}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    {t.description || new Date(t.date).toLocaleDateString()}
                                                </p>
                                            </div>
                                            <span className={`text-sm font-semibold ${t.type === 'income' ? 'text-green-600' : 'text-red-600'}`}>
                                                {t.type === 'income' ? '+' : '-'}{formatKina(t.amount)}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground py-6 text-center">
                                    No transactions yet. Add your first one!
                                </p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Quick Add Button (mobile) */}
                <Link
                    href="/transactions/create"
                    className="fixed bottom-20 right-4 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-accent text-accent-foreground shadow-lg md:hidden"
                >
                    <Plus className="h-6 w-6" />
                </Link>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
