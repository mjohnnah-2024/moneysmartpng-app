import { Head, Link } from '@inertiajs/react';
import { Crown, ArrowUpRight, MessageSquare } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { show as premiumShow } from '@/routes/premium';
import type { Subscription } from '@/types';

type Props = {
    currentPlan: 'free' | 'premium';
    subscription: (Pick<Subscription, 'id' | 'plan' | 'status' | 'payment_method' | 'starts_at' | 'ends_at'>) | null;
    usage: {
        transactions: number;
        budgets: number;
        goals: number;
        ai_chat: number;
    };
    limits: {
        transactions: number;
        budgets: number;
        goals: number;
        ai_chat: number;
    };
};

const FEATURE_LABELS: Record<string, string> = {
    transactions: 'Transactions',
    budgets: 'Budget Categories',
    goals: 'Active Goals',
    ai_chat: 'AI Coach Messages',
};

export default function SubscriptionSettings({
    currentPlan,
    subscription,
    usage,
    limits,
}: Props) {
    return (
        <>
            <Head title="Subscription settings" />

            <h1 className="sr-only">Subscription settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Subscription & Plan"
                    description="Manage your subscription and view your usage"
                />

                {/* Current Plan Card */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <Crown className={`h-5 w-5 ${currentPlan === 'premium' ? 'text-amber-500' : 'text-muted-foreground'}`} />
                                <CardTitle className="text-base">
                                    {currentPlan === 'premium' ? 'Premium Plan' : 'Free Plan'}
                                </CardTitle>
                                <Badge variant={currentPlan === 'premium' ? 'default' : 'secondary'}>
                                    {currentPlan === 'premium' ? 'Active' : 'Free'}
                                </Badge>
                            </div>
                            {currentPlan === 'free' && (
                                <Button asChild size="sm">
                                    <Link href={premiumShow()}>
                                        Upgrade
                                        <ArrowUpRight className="ml-1 h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent>
                        {currentPlan === 'premium' && subscription ? (
                            <div className="space-y-2 text-sm text-muted-foreground">
                                <p>
                                    Payment method:{' '}
                                    {subscription.payment_method === 'stripe'
                                        ? 'Credit/Debit Card'
                                        : 'Mobile Money'}
                                </p>
                                {subscription.ends_at && (
                                    <p>
                                        Next billing date:{' '}
                                        {new Date(subscription.ends_at).toLocaleDateString('en-PG', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                        })}
                                    </p>
                                )}
                                {subscription.payment_method === 'mobile_money' && (
                                    <p className="mt-2">
                                        To manage your subscription, contact us via{' '}
                                        <a
                                            href="https://wa.me/6751234567"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center gap-1 font-medium text-green-600 underline underline-offset-2 hover:text-green-700"
                                        >
                                            <MessageSquare className="h-3.5 w-3.5" />
                                            WhatsApp
                                        </a>
                                    </p>
                                )}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                You're on the free plan. Upgrade to Premium for unlimited access to all features.
                            </p>
                        )}
                    </CardContent>
                </Card>

                {/* Usage Card */}
                {currentPlan === 'free' && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Monthly Usage</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {Object.entries(FEATURE_LABELS).map(([key, label]) => {
                                const used = usage[key as keyof typeof usage];
                                const limit = limits[key as keyof typeof limits];
                                const percentage = Math.min((used / limit) * 100, 100);

                                return (
                                    <div key={key} className="space-y-1.5">
                                        <div className="flex items-center justify-between text-sm">
                                            <span>{label}</span>
                                            <span className="text-muted-foreground">
                                                {used} / {limit}
                                            </span>
                                        </div>
                                        <Progress value={percentage} className="h-2" />
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}
