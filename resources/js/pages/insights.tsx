import { Head, Link } from '@inertiajs/react';
import { BarChart, Bar, XAxis, YAxis, ResponsiveContainer, Tooltip, PieChart, Pie, Cell, LineChart, Line } from 'recharts';
import { formatKina, formatKinaShort } from '@/lib/formatters';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { AlertTriangle, AlertCircle, TrendingUp, TrendingDown, ArrowUpRight, ArrowDownLeft, Lightbulb, Info, CheckCircle } from 'lucide-react';
import type { Recommendation } from '@/types';

type CategoryData = {
    category: string;
    amount: number;
    count: number;
};

type MonthlyTrend = {
    month: string;
    income: number;
    expense: number;
};

type DailySpending = {
    day: number;
    amount: number;
};

type Alert = {
    type: 'danger' | 'warning';
    category: string;
    message: string;
    percentage: number;
    spent: number;
    limit: number;
};

type Comparison = {
    currentExpense: number;
    prevExpense: number;
    expenseChange: number;
    currentIncome: number;
    prevIncome: number;
    incomeChange: number;
    currentSavings: number;
    prevSavings: number;
};

type Props = {
    spendingByCategory: CategoryData[];
    monthlyTrends: MonthlyTrend[];
    dailySpending: DailySpending[];
    alerts: Alert[];
    comparison: Comparison;
    recommendations: Recommendation[];
};

const COLORS = [
    'var(--color-chart-1)',
    'var(--color-chart-2)',
    'var(--color-chart-3)',
    'var(--color-chart-4)',
    'var(--color-chart-5)',
];

export default function Insights({ spendingByCategory, monthlyTrends, dailySpending, alerts, comparison, recommendations }: Props) {
    const recommendationIcon = (type: string) => {
        switch (type) {
            case 'warning': return <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />;
            case 'success': return <CheckCircle className="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" />;
            default: return <Info className="mt-0.5 h-4 w-4 shrink-0 text-blue-600" />;
        }
    };

    const recommendationBg = (type: string) => {
        switch (type) {
            case 'warning': return 'bg-amber-50 dark:bg-amber-900/20';
            case 'success': return 'bg-emerald-50 dark:bg-emerald-900/20';
            default: return 'bg-blue-50 dark:bg-blue-900/20';
        }
    };

    return (
        <>
            <Head title="Spending Insights" />
            <div className="flex flex-col gap-4 p-4">
                <h1 className="text-2xl font-bold">Spending Insights</h1>

                {/* Recommendations */}
                {recommendations.length > 0 && (
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Lightbulb className="h-4 w-4 text-amber-500" />
                                Recommendations
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            {recommendations.map((rec) => (
                                <div key={rec.id} className={`flex items-start gap-3 rounded-lg p-3 ${recommendationBg(rec.type)}`}>
                                    {recommendationIcon(rec.type)}
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
                        </CardContent>
                    </Card>
                )}

                {/* Smart Alerts */}
                {alerts.length > 0 && (
                    <div className="flex flex-col gap-2">
                        {alerts.map((alert, i) => (
                            <div
                                key={i}
                                className={`flex items-start gap-3 rounded-lg p-3 ${
                                    alert.type === 'danger'
                                        ? 'bg-red-50 dark:bg-red-900/20'
                                        : 'bg-amber-50 dark:bg-amber-900/20'
                                }`}
                            >
                                {alert.type === 'danger' ? (
                                    <AlertCircle className="mt-0.5 h-4 w-4 shrink-0 text-red-600" />
                                ) : (
                                    <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                                )}
                                <div className="flex-1">
                                    <p className={`text-sm font-medium ${
                                        alert.type === 'danger' ? 'text-red-800 dark:text-red-300' : 'text-amber-800 dark:text-amber-300'
                                    }`}>
                                        {alert.message}
                                    </p>
                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                        {formatKina(alert.spent)} of {formatKina(alert.limit)}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* Month Comparison Cards */}
                <div className="grid grid-cols-3 gap-2">
                    <Card>
                        <CardContent className="p-3 text-center">
                            <div className="flex items-center justify-center gap-1">
                                <ArrowDownLeft className="h-3.5 w-3.5 text-green-600" />
                                <span className="text-xs text-muted-foreground">Income</span>
                            </div>
                            <p className="mt-1 text-sm font-bold">{formatKinaShort(comparison.currentIncome)}</p>
                            {comparison.incomeChange !== 0 && (
                                <p className={`text-xs ${comparison.incomeChange > 0 ? 'text-green-600' : 'text-red-600'}`}>
                                    {comparison.incomeChange > 0 ? '+' : ''}{comparison.incomeChange}%
                                </p>
                            )}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-3 text-center">
                            <div className="flex items-center justify-center gap-1">
                                <ArrowUpRight className="h-3.5 w-3.5 text-red-600" />
                                <span className="text-xs text-muted-foreground">Expenses</span>
                            </div>
                            <p className="mt-1 text-sm font-bold">{formatKinaShort(comparison.currentExpense)}</p>
                            {comparison.expenseChange !== 0 && (
                                <p className={`text-xs ${comparison.expenseChange < 0 ? 'text-green-600' : 'text-red-600'}`}>
                                    {comparison.expenseChange > 0 ? '+' : ''}{comparison.expenseChange}%
                                </p>
                            )}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-3 text-center">
                            <div className="flex items-center justify-center gap-1">
                                {comparison.currentSavings >= 0 ? (
                                    <TrendingUp className="h-3.5 w-3.5 text-green-600" />
                                ) : (
                                    <TrendingDown className="h-3.5 w-3.5 text-red-600" />
                                )}
                                <span className="text-xs text-muted-foreground">Savings</span>
                            </div>
                            <p className={`mt-1 text-sm font-bold ${comparison.currentSavings >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                {formatKinaShort(Math.abs(comparison.currentSavings))}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Spending by Category Pie Chart */}
                {spendingByCategory.length > 0 && (
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">Spending by Category</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="h-48">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={spendingByCategory}
                                            dataKey="amount"
                                            nameKey="category"
                                            cx="50%"
                                            cy="50%"
                                            outerRadius={70}
                                            innerRadius={40}
                                        >
                                            {spendingByCategory.map((_, i) => (
                                                <Cell key={i} fill={COLORS[i % COLORS.length]} />
                                            ))}
                                        </Pie>
                                        <Tooltip
                                            formatter={(value: number) => formatKina(value)}
                                        />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>
                            <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1">
                                {spendingByCategory.map((cat, i) => (
                                    <div key={cat.category} className="flex items-center gap-1.5 text-xs">
                                        <div className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: COLORS[i % COLORS.length] }} />
                                        <span className="text-muted-foreground">{cat.category}</span>
                                        <span className="font-medium">{formatKina(cat.amount)}</span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Monthly Trends Bar Chart */}
                {monthlyTrends.length > 0 && (
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">Monthly Trends</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="h-48">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart data={monthlyTrends}>
                                        <XAxis dataKey="month" tick={{ fontSize: 12 }} />
                                        <YAxis tick={{ fontSize: 10 }} tickFormatter={formatKinaShort} />
                                        <Tooltip formatter={(value: number) => formatKina(value)} />
                                        <Bar dataKey="income" fill="var(--color-chart-2)" radius={[2, 2, 0, 0]} />
                                        <Bar dataKey="expense" fill="var(--color-chart-1)" radius={[2, 2, 0, 0]} />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                            <div className="mt-2 flex justify-center gap-4 text-xs">
                                <div className="flex items-center gap-1.5">
                                    <div className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: 'var(--color-chart-2)' }} />
                                    <span className="text-muted-foreground">Income</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <div className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: 'var(--color-chart-1)' }} />
                                    <span className="text-muted-foreground">Expense</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Daily Spending Line Chart */}
                {dailySpending.length > 0 && (
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">Daily Spending This Month</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="h-40">
                                <ResponsiveContainer width="100%" height="100%">
                                    <LineChart data={dailySpending}>
                                        <XAxis dataKey="day" tick={{ fontSize: 10 }} />
                                        <YAxis tick={{ fontSize: 10 }} tickFormatter={formatKinaShort} />
                                        <Tooltip formatter={(value: number) => formatKina(value)} />
                                        <Line
                                            type="monotone"
                                            dataKey="amount"
                                            stroke="var(--color-chart-1)"
                                            strokeWidth={2}
                                            dot={false}
                                        />
                                    </LineChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Empty state */}
                {spendingByCategory.length === 0 && monthlyTrends.every((m) => m.income === 0 && m.expense === 0) && (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <p className="text-muted-foreground">Start adding transactions to see your spending insights.</p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}
