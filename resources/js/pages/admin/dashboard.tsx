import { Head } from '@inertiajs/react';
import { Activity, Crown, DollarSign, MessageSquare, TrendingUp, Users } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, LineChart, Line } from 'recharts';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin-layout';

type Stats = {
    totalUsers: number;
    newToday: number;
    activePremium: number;
    conversionRate: number;
    aiMessages: number;
    estimatedRevenue: number;
};

type ActivityItem = {
    type: 'signup' | 'subscription' | 'payment';
    description: string;
    timestamp: string;
};

type Props = {
    stats: Stats;
    dailySignups: Array<{ date: string; count: number }>;
    weeklyUpgrades: Array<{ week: string; count: number }>;
    recentActivity: ActivityItem[];
};

function formatKina(amount: number): string {
    return `K ${amount.toLocaleString('en-PG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function timeAgo(timestamp: string): string {
    const seconds = Math.floor((Date.now() - new Date(timestamp).getTime()) / 1000);
    if (seconds < 60) return 'just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
    return `${Math.floor(seconds / 86400)}d ago`;
}

const activityColors: Record<string, string> = {
    signup: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    subscription: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    payment: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
};

export default function AdminDashboard({ stats, dailySignups, weeklyUpgrades, recentActivity }: Props) {
    const kpiCards = [
        { label: 'Total Users', value: stats.totalUsers.toLocaleString(), icon: Users, color: 'text-blue-600' },
        { label: 'New Today', value: stats.newToday.toLocaleString(), icon: TrendingUp, color: 'text-green-600' },
        { label: 'Active Premium', value: stats.activePremium.toLocaleString(), icon: Crown, color: 'text-amber-600' },
        { label: 'Conversion %', value: `${stats.conversionRate}%`, icon: Activity, color: 'text-purple-600' },
        { label: 'AI Messages', value: stats.aiMessages.toLocaleString(), icon: MessageSquare, color: 'text-cyan-600' },
        { label: 'Est. Revenue', value: formatKina(stats.estimatedRevenue), icon: DollarSign, color: 'text-emerald-600' },
    ];

    return (
        <>
            <Head title="Admin Dashboard" />
            <div className="flex flex-col gap-6">
                <h1 className="text-2xl font-bold">Admin Dashboard</h1>

                {/* KPI Cards */}
                <div className="grid gap-4 grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
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
                            <CardTitle className="text-base">Daily Signups (Last 30 Days)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {dailySignups.length > 0 ? (
                                <ResponsiveContainer width="100%" height={250}>
                                    <BarChart data={dailySignups}>
                                        <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                                        <XAxis
                                            dataKey="date"
                                            tick={{ fontSize: 10 }}
                                            tickFormatter={(v) => new Date(v).toLocaleDateString('en', { month: 'short', day: 'numeric' })}
                                        />
                                        <YAxis allowDecimals={false} tick={{ fontSize: 11 }} />
                                        <Tooltip labelFormatter={(v) => new Date(v).toLocaleDateString()} />
                                        <Bar dataKey="count" name="Signups" fill="var(--color-chart-1)" radius={[4, 4, 0, 0]} />
                                    </BarChart>
                                </ResponsiveContainer>
                            ) : (
                                <p className="py-12 text-center text-sm text-muted-foreground">No signup data yet.</p>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">Weekly Premium Upgrades (Last 8 Weeks)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {weeklyUpgrades.length > 0 ? (
                                <ResponsiveContainer width="100%" height={250}>
                                    <LineChart data={weeklyUpgrades}>
                                        <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                                        <XAxis dataKey="week" tick={{ fontSize: 11 }} />
                                        <YAxis allowDecimals={false} tick={{ fontSize: 11 }} />
                                        <Tooltip />
                                        <Line type="monotone" dataKey="count" name="Upgrades" stroke="var(--color-chart-2)" strokeWidth={2} dot={{ r: 4 }} />
                                    </LineChart>
                                </ResponsiveContainer>
                            ) : (
                                <p className="py-12 text-center text-sm text-muted-foreground">No upgrade data yet.</p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Recent Activity */}
                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base">Recent Activity</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recentActivity.length > 0 ? (
                            <div className="flex flex-col divide-y">
                                {recentActivity.map((item, i) => (
                                    <div key={i} className="flex items-center justify-between py-2.5">
                                        <div className="flex items-center gap-3">
                                            <Badge variant="outline" className={activityColors[item.type]}>
                                                {item.type}
                                            </Badge>
                                            <span className="text-sm">{item.description}</span>
                                        </div>
                                        <span className="text-xs text-muted-foreground whitespace-nowrap">
                                            {timeAgo(item.timestamp)}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="py-8 text-center text-sm text-muted-foreground">No recent activity.</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

AdminDashboard.layout = (page: React.ReactNode) => (
    <AdminLayout breadcrumbs={[{ title: 'Admin', href: '/admin' }, { title: 'Dashboard' }]}>
        {page}
    </AdminLayout>
);
