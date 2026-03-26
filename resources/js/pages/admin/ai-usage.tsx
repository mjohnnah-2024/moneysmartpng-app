import { Head } from '@inertiajs/react';
import { AlertTriangle, DollarSign, MessageSquare, TrendingUp } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, AreaChart, Area } from 'recharts';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin-layout';

type Stats = {
    totalMessages: number;
    todayMessages: number;
    estimatedCost: number;
    todayCost: number;
    dailyAlert: boolean;
    alertThreshold: number;
};

type TopUser = {
    user_id: number;
    name: string;
    email: string;
    message_count: number;
};

type Props = {
    stats: Stats;
    hourlyVolume: Array<{ hour: string; count: number }>;
    dailyTotals: Array<{ date: string; count: number }>;
    topUsers: TopUser[];
};

function formatCurrency(amount: number): string {
    return `$${amount.toFixed(2)}`;
}

export default function AdminAiUsage({ stats, hourlyVolume, dailyTotals, topUsers }: Props) {
    const kpiCards = [
        { label: 'Total Messages', value: stats.totalMessages.toLocaleString(), icon: MessageSquare, color: 'text-blue-600' },
        { label: 'Today', value: stats.todayMessages.toLocaleString(), icon: TrendingUp, color: 'text-green-600' },
        { label: 'Est. Total Cost', value: formatCurrency(stats.estimatedCost), icon: DollarSign, color: 'text-amber-600' },
        { label: 'Today Cost', value: formatCurrency(stats.todayCost), icon: DollarSign, color: 'text-purple-600' },
    ];

    return (
        <>
            <Head title="Admin - AI Usage" />
            <div className="flex flex-col gap-6">
                <h1 className="text-2xl font-bold">AI Usage Monitoring</h1>

                {/* Alert */}
                {stats.dailyAlert && (
                    <Alert variant="destructive">
                        <AlertTriangle className="h-4 w-4" />
                        <AlertTitle>High Usage Alert</AlertTitle>
                        <AlertDescription>
                            Today's AI messages ({stats.todayMessages.toLocaleString()}) have exceeded the daily threshold of {stats.alertThreshold.toLocaleString()}.
                            Consider reviewing usage patterns.
                        </AlertDescription>
                    </Alert>
                )}

                {/* KPI Cards */}
                <div className="grid gap-4 grid-cols-2 lg:grid-cols-4">
                    {kpiCards.map((kpi) => (
                        <Card key={kpi.label}>
                            <CardContent className="p-4">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-xs text-muted-foreground">{kpi.label}</p>
                                        <p className="text-lg font-bold">{kpi.value}</p>
                                    </div>
                                    <kpi.icon className={`h-5 w-5 ${kpi.color}`} />
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Charts */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">Hourly Volume (Last 24 Hours)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {hourlyVolume.length > 0 ? (
                                <ResponsiveContainer width="100%" height={250}>
                                    <AreaChart data={hourlyVolume}>
                                        <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                                        <XAxis
                                            dataKey="hour"
                                            tick={{ fontSize: 10 }}
                                            tickFormatter={(v) => {
                                                const d = new Date(v);
                                                return `${d.getHours()}:00`;
                                            }}
                                        />
                                        <YAxis allowDecimals={false} tick={{ fontSize: 11 }} />
                                        <Tooltip labelFormatter={(v) => new Date(v).toLocaleString()} />
                                        <Area type="monotone" dataKey="count" name="Messages" stroke="var(--color-chart-3)" fill="var(--color-chart-3)" fillOpacity={0.2} />
                                    </AreaChart>
                                </ResponsiveContainer>
                            ) : (
                                <p className="py-12 text-center text-sm text-muted-foreground">No data in the last 24 hours.</p>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">Daily Totals (Last 30 Days)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {dailyTotals.length > 0 ? (
                                <ResponsiveContainer width="100%" height={250}>
                                    <BarChart data={dailyTotals}>
                                        <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                                        <XAxis
                                            dataKey="date"
                                            tick={{ fontSize: 10 }}
                                            tickFormatter={(v) => new Date(v).toLocaleDateString('en', { month: 'short', day: 'numeric' })}
                                        />
                                        <YAxis allowDecimals={false} tick={{ fontSize: 11 }} />
                                        <Tooltip labelFormatter={(v) => new Date(v).toLocaleDateString()} />
                                        <Bar dataKey="count" name="Messages" fill="var(--color-chart-4)" radius={[4, 4, 0, 0]} />
                                    </BarChart>
                                </ResponsiveContainer>
                            ) : (
                                <p className="py-12 text-center text-sm text-muted-foreground">No data in the last 30 days.</p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Top Users */}
                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base">Top AI Users</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {topUsers.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b bg-muted/50">
                                            <th className="px-4 py-2 text-left font-medium">#</th>
                                            <th className="px-4 py-2 text-left font-medium">User</th>
                                            <th className="px-4 py-2 text-left font-medium">Messages</th>
                                            <th className="px-4 py-2 text-left font-medium">Est. Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {topUsers.map((user, i) => (
                                            <tr key={user.user_id} className="border-b">
                                                <td className="px-4 py-2 text-muted-foreground">{i + 1}</td>
                                                <td className="px-4 py-2">
                                                    <div>
                                                        <p className="font-medium">{user.name}</p>
                                                        <p className="text-xs text-muted-foreground">{user.email}</p>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-2 font-medium">{user.message_count.toLocaleString()}</td>
                                                <td className="px-4 py-2 text-muted-foreground">{formatCurrency(user.message_count * 0.002)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <p className="py-8 text-center text-sm text-muted-foreground">No AI usage data yet.</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

AdminAiUsage.layout = (page: React.ReactNode) => (
    <AdminLayout breadcrumbs={[{ title: 'Admin', href: '/admin' }, { title: 'AI Usage' }]}>
        {page}
    </AdminLayout>
);
