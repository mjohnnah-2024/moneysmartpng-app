import { Head, Link } from '@inertiajs/react';
import { ArrowDownLeft, ArrowUpRight, Plus, TrendingUp, Shield, Receipt, Lightbulb, AlertTriangle, Info, CheckCircle, Flame } from 'lucide-react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from 'recharts';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { formatKina } from '@/lib/formatters';
import { dashboard } from '@/routes';
import type { Profile, Transaction, SpendingCategory, MonthlySummary, SafeToSpend, Recommendation } from '@/types';

type Props = {
    profile: Profile | null;
    summary: MonthlySummary;
    spendingByCategory: SpendingCategory[];
    recentTransactions: Transaction[];
    safeToSpend: SafeToSpend;
    recommendations: Recommendation[];
    streak: number;
};

const CHART_COLORS = [
    'var(--color-chart-1)',
    'var(--color-chart-2)',
    'var(--color-chart-3)',
    'var(--color-chart-4)',
    'var(--color-chart-5)',
];

export default function Dashboard({ profile, summary, spendingByCategory, recentTransactions, safeToSpend, recommendations, streak }: Props) {
    const statusColor = {
        green: 'text-emerald-600',
        yellow: 'text-amber-500',
        red: 'text-red-600',
    }[safeToSpend.status];

    const statusBg = {
        green: 'bg-emerald-100 dark:bg-emerald-900/30',
        yellow: 'bg-amber-100 dark:bg-amber-900/30',
        red: 'bg-red-100 dark:bg-red-900/30',
    }[safeToSpend.status];

    const progressValue = Math.min(100, Math.max(0, safeToSpend.percentage));

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-col gap-4 p-4">
                {/* Greeting */}
                <div className="animate-fade-in">
                    <h1 className="text-2xl font-bold text-foreground">
                        Gutpela dei, {profile?.full_name?.split(' ')[0] ?? 'there'}!
                    </h1>
                    <p className="text-sm text-muted-foreground">{summary.month} Summary</p>
                </div>

                {/* Safe-to-Spend Hero Card */}
                <Card className={`animate-slide-up border-2 ${safeToSpend.status === 'green' ? 'border-emerald-200 dark:border-emerald-800' : safeToSpend.status === 'yellow' ? 'border-amber-200 dark:border-amber-800' : 'border-red-200 dark:border-red-800'}`} role="region" aria-label="Safe to spend today">
                    <CardContent className="p-4">
                        <div className="flex items-center gap-2 mb-2">
                            <div className={`rounded-full p-1.5 ${statusBg}`}>
                                <Shield className={`h-4 w-4 ${statusColor}`} />
                            </div>
                            <p className="text-sm text-muted-foreground">Today, you can safely spend:</p>
                        </div>
                        <p className={`text-3xl font-bold ${statusColor}`}>{formatKina(safeToSpend.daily)}</p>
                        <div className="mt-3">
                            <div className="flex items-center justify-between text-xs text-muted-foreground mb-1">
                                <span>Budget used</span>
                                <span>{Math.round(100 - progressValue)}% of daily</span>
                            </div>
                            <Progress value={100 - progressValue} className="h-2" />
                        </div>
                        <div className="mt-3 flex items-center justify-between text-sm">
                            <span className="text-muted-foreground">Remaining this cycle: <span className="font-semibold text-foreground">{formatKina(safeToSpend.remaining)}</span></span>
                        </div>
                        {safeToSpend.nextBill && (
                            <div className="mt-2 flex items-center gap-1.5 text-xs text-muted-foreground">
                                <Receipt className="h-3 w-3" />
                                <span>Next bill: {safeToSpend.nextBill.name} ({formatKina(safeToSpend.nextBill.amount)}) in {safeToSpend.nextBill.days_until} day{safeToSpend.nextBill.days_until !== 1 ? 's' : ''}</span>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Top Recommendations */}
                {recommendations.length > 0 && (
                    <div className="animate-slide-up space-y-2" role="region" aria-label="Recommendations">
                        {recommendations.slice(0, 2).map((rec) => (
                            <div key={rec.id} className={`flex items-start gap-3 rounded-lg p-3 ${
                                rec.type === 'warning' ? 'bg-amber-50 dark:bg-amber-900/20' :
                                rec.type === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20' :
                                'bg-blue-50 dark:bg-blue-900/20'
                            }`}>
                                {rec.type === 'warning' ? <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0 text-amber-600" /> :
                                 rec.type === 'success' ? <CheckCircle className="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" /> :
                                 <Info className="mt-0.5 h-4 w-4 shrink-0 text-blue-600" />}
                                <div className="flex-1">
                                    <p className="text-sm">{rec.message}</p>
                                    {rec.action && (
                                        <Button variant="link" size="sm" className="h-auto p-0 text-xs" asChild>
                                            <Link href={rec.action.href}>{rec.action.label} &rarr;</Link>
                                        </Button>
                                    )}
                                </div>
                            </div>
                        ))}
                        {recommendations.length > 2 && (
                            <Button variant="link" size="sm" className="h-auto p-0 text-xs" asChild>
                                <Link href="/insights">See all insights &rarr;</Link>
                            </Button>
                        )}
                    </div>
                )}

                {/* Streak Widget */}
                {streak > 0 && (
                    <Link href="/achievements" className="flex items-center gap-2 rounded-lg bg-orange-50 p-3 dark:bg-orange-900/20">
                        <Flame className="h-5 w-5 text-orange-500" />
                        <span className="text-sm font-medium">{streak}-day streak! Keep going!</span>
                        <span className="ml-auto text-xs text-muted-foreground">View achievements &rarr;</span>
                    </Link>
                )}

                {/* Summary Cards */}
                <div className="animate-slide-up grid gap-3 grid-cols-1 sm:grid-cols-3" role="region" aria-label="Monthly summary">
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
                                    <Link href="/transactions/create" className="block mt-2 text-primary hover:underline">
                                        Add your first expense &rarr;
                                    </Link>
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
                                <div className="py-6 text-center">
                                    <p className="text-sm text-muted-foreground">No transactions yet.</p>
                                    <Link href="/transactions/create" className="mt-1 inline-block text-sm text-primary hover:underline">
                                        Start tracking your spending &rarr;
                                    </Link>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Quick Add Button (mobile) */}
                <Link
                    href="/transactions/create"
                    className="fixed bottom-20 right-4 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-accent text-accent-foreground shadow-lg md:hidden"
                    aria-label="Add new transaction"
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
