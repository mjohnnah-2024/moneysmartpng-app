import { Head, router, useForm } from '@inertiajs/react';
import { Check, Crown, Phone, CreditCard, Loader2, CheckCircle, Clock } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { show as premiumShow } from '@/routes/premium';
import type { PricingPlan, Subscription } from '@/types';

type Props = {
    currentPlan: 'free' | 'premium';
    subscription: Pick<Subscription, 'plan' | 'status' | 'payment_method' | 'starts_at' | 'ends_at'> | null;
    pricing: Record<string, PricingPlan>;
    stripeKey: string | null;
};

const PREMIUM_FEATURES = [
    'Unlimited transactions',
    'Unlimited budget categories',
    'Unlimited savings goals',
    'Unlimited AI Coach messages',
    'Export transactions to CSV',
    'Priority support',
];

const FREE_FEATURES = [
    '50 transactions per month',
    '3 budget categories per month',
    '2 active savings goals',
    '20 AI Coach messages per month',
];

export default function Premium({
    currentPlan,
    subscription,
    pricing,
    stripeKey,
}: Props) {
    const [showMobileMoneyModal, setShowMobileMoneyModal] = useState(false);
    const [selectedPlan, setSelectedPlan] = useState<string>('monthly');
    const [checkoutLoading, setCheckoutLoading] = useState(false);

    const handleStripeCheckout = async () => {
        setCheckoutLoading(true);
        try {
            const response = await fetch('/premium/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ plan: selectedPlan }),
            });

            const data = await response.json();

            if (data.id && stripeKey) {
                const stripe = await import('@stripe/stripe-js').then(m => m.loadStripe(stripeKey));
                if (stripe) {
                    await stripe.redirectToCheckout({ sessionId: data.id });
                }
            }
        } finally {
            setCheckoutLoading(false);
        }
    };

    const success = new URLSearchParams(window.location.search).get('success');
    const cancelled = new URLSearchParams(window.location.search).get('cancelled');

    return (
        <>
            <Head title="Upgrade to Premium" />

            <div className="px-4 py-6">
                <Heading
                    title="Upgrade to Premium"
                    description="Unlock all features and take full control of your finances"
                />

                {success && (
                    <div className="mt-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950/30 dark:text-green-300">
                        <CheckCircle className="h-5 w-5 shrink-0" />
                        <p>Payment successful! Your premium subscription is now active.</p>
                    </div>
                )}

                {cancelled && (
                    <div className="mt-4 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-300">
                        <Clock className="h-5 w-5 shrink-0" />
                        <p>Payment was cancelled. You can try again anytime.</p>
                    </div>
                )}

                {currentPlan === 'premium' && subscription?.status === 'active' ? (
                    <div className="mt-6">
                        <Card>
                            <CardHeader>
                                <div className="flex items-center gap-2">
                                    <Crown className="h-5 w-5 text-amber-500" />
                                    <CardTitle>You're on Premium!</CardTitle>
                                    <Badge variant="secondary" className="bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                        Active
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm text-muted-foreground">
                                {subscription.payment_method === 'stripe' && (
                                    <p>Payment method: Credit/Debit Card</p>
                                )}
                                {subscription.payment_method === 'mobile_money' && (
                                    <p>Payment method: Mobile Money</p>
                                )}
                                {subscription.ends_at && (
                                    <p>
                                        Subscription ends:{' '}
                                        {new Date(subscription.ends_at).toLocaleDateString('en-PG', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                        })}
                                    </p>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                ) : (
                    <div className="mt-6 space-y-8">
                        {/* Plan comparison */}
                        <div className="grid gap-6 md:grid-cols-2">
                            {/* Free Plan */}
                            <Card className="border-muted">
                                <CardHeader>
                                    <CardTitle className="text-lg">Free Plan</CardTitle>
                                    <p className="text-2xl font-bold">K0<span className="text-sm font-normal text-muted-foreground">/forever</span></p>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    {FREE_FEATURES.map((feature) => (
                                        <div key={feature} className="flex items-center gap-2 text-sm">
                                            <Check className="h-4 w-4 shrink-0 text-muted-foreground" />
                                            <span>{feature}</span>
                                        </div>
                                    ))}
                                    {currentPlan === 'free' && (
                                        <Badge variant="outline" className="mt-2">Current Plan</Badge>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Premium Plan */}
                            <Card className="border-primary ring-2 ring-primary/20">
                                <CardHeader>
                                    <div className="flex items-center gap-2">
                                        <Crown className="h-5 w-5 text-amber-500" />
                                        <CardTitle className="text-lg">Premium Plan</CardTitle>
                                    </div>
                                    <p className="text-2xl font-bold">
                                        K{pricing[selectedPlan]?.price ?? pricing.monthly.price}
                                        <span className="text-sm font-normal text-muted-foreground">
                                            /{pricing[selectedPlan]?.label ?? pricing.monthly.label}
                                        </span>
                                    </p>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    {PREMIUM_FEATURES.map((feature) => (
                                        <div key={feature} className="flex items-center gap-2 text-sm">
                                            <Check className="h-4 w-4 shrink-0 text-green-600" />
                                            <span>{feature}</span>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        </div>

                        {/* Duration selector */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">Choose your plan duration</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    {Object.entries(pricing).map(([key, plan]) => (
                                        <button
                                            key={key}
                                            type="button"
                                            onClick={() => setSelectedPlan(key)}
                                            className={`rounded-lg border-2 p-3 text-center transition-colors ${
                                                selectedPlan === key
                                                    ? 'border-primary bg-primary/5'
                                                    : 'border-muted hover:border-muted-foreground/30'
                                            }`}
                                        >
                                            <div className="text-sm font-medium">{plan.label}</div>
                                            <div className="text-lg font-bold">K{plan.price}</div>
                                            <div className="text-xs text-muted-foreground">
                                                K{(plan.price / plan.months).toFixed(2)}/mo
                                            </div>
                                        </button>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Payment buttons */}
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Button
                                size="lg"
                                className="h-auto py-4"
                                onClick={handleStripeCheckout}
                                disabled={checkoutLoading || !stripeKey}
                            >
                                {checkoutLoading ? (
                                    <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                                ) : (
                                    <CreditCard className="mr-2 h-5 w-5" />
                                )}
                                <div className="text-left">
                                    <div className="font-semibold">Pay with Card</div>
                                    <div className="text-xs opacity-80">Visa, Mastercard via Stripe</div>
                                </div>
                            </Button>

                            <Button
                                size="lg"
                                variant="outline"
                                className="h-auto py-4"
                                onClick={() => setShowMobileMoneyModal(true)}
                            >
                                <Phone className="mr-2 h-5 w-5" />
                                <div className="text-left">
                                    <div className="font-semibold">Pay with Mobile Money</div>
                                    <div className="text-xs opacity-80">BSP Mobile Banking</div>
                                </div>
                            </Button>
                        </div>
                    </div>
                )}
            </div>

            <MobileMoneyModal
                open={showMobileMoneyModal}
                onClose={() => setShowMobileMoneyModal(false)}
                selectedPlan={selectedPlan}
                pricing={pricing}
            />
        </>
    );
}

function MobileMoneyModal({
    open,
    onClose,
    selectedPlan,
    pricing,
}: {
    open: boolean;
    onClose: () => void;
    selectedPlan: string;
    pricing: Record<string, PricingPlan>;
}) {
    const form = useForm({
        reference_number: '',
        phone: '',
        months: pricing[selectedPlan]?.months ?? 1,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post('/premium/mobile-money', {
            onSuccess: () => {
                form.reset();
                onClose();
            },
        });
    };

    const plan = pricing[selectedPlan];

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Pay with Mobile Money</DialogTitle>
                    <DialogDescription>
                        Follow the instructions below to make your payment via BSP Mobile Banking.
                    </DialogDescription>
                </DialogHeader>

                <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm dark:border-amber-800 dark:bg-amber-950/30">
                    <p className="font-semibold text-amber-800 dark:text-amber-300">Payment Instructions:</p>
                    <ol className="mt-2 list-inside list-decimal space-y-1 text-amber-700 dark:text-amber-400">
                        <li>Open BSP Mobile Banking app</li>
                        <li>Send <strong>K{plan?.price ?? '9.99'}</strong> to account <strong>1234-5678-9012</strong></li>
                        <li>Use reference: <strong>MSMART-{'{'}your name{'}'}</strong></li>
                        <li>Enter the details below after sending</li>
                    </ol>
                </div>

                <form onSubmit={handleSubmit} className="mt-4 space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="reference_number">BSP Reference Number</Label>
                        <Input
                            id="reference_number"
                            value={form.data.reference_number}
                            onChange={(e) => form.setData('reference_number', e.target.value)}
                            placeholder="e.g. MM-12345678"
                            required
                        />
                        {form.errors.reference_number && (
                            <p className="text-sm text-red-600">{form.errors.reference_number}</p>
                        )}
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="phone">Phone Number</Label>
                        <Input
                            id="phone"
                            value={form.data.phone}
                            onChange={(e) => form.setData('phone', e.target.value)}
                            placeholder="+675 7XXX XXXX"
                            required
                        />
                        {form.errors.phone && (
                            <p className="text-sm text-red-600">{form.errors.phone}</p>
                        )}
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="months">Duration</Label>
                        <Select
                            value={String(form.data.months)}
                            onValueChange={(value) => form.setData('months', Number(value))}
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {Object.entries(pricing).map(([key, p]) => (
                                    <SelectItem key={key} value={String(p.months)}>
                                        {p.label} — K{p.price}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {form.errors.months && (
                            <p className="text-sm text-red-600">{form.errors.months}</p>
                        )}
                    </div>

                    <div className="flex justify-end gap-2 pt-2">
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            ) : (
                                <Phone className="mr-2 h-4 w-4" />
                            )}
                            Submit Payment
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}

Premium.layout = {
    breadcrumbs: [
        {
            title: 'Premium',
            href: premiumShow(),
        },
    ],
};
