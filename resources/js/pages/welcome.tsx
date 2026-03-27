import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';
import { Landmark, Target, BarChart3, MessageCircle, Shield, Sparkles } from 'lucide-react';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage().props;

    const features = [
        {
            icon: Landmark,
            title: 'Track Transactions',
            description: 'Log your income and expenses in Kina. See where your money goes with clear categories.',
        },
        {
            icon: Target,
            title: 'Set Savings Goals',
            description: 'Save for what matters — school fees, a bilum business, or an emergency fund. Track your progress.',
        },
        {
            icon: BarChart3,
            title: 'Budget Planner',
            description: 'Create monthly budgets by category and stay on track with visual spending breakdowns.',
        },
        {
            icon: MessageCircle,
            title: 'AI Budget Coach',
            description: 'Chat with your personal AI coach for budgeting tips tailored to life in Papua New Guinea.',
        },
        {
            icon: Sparkles,
            title: 'Smart Insights',
            description: 'Get personalised spending insights and tips to help you make smarter financial decisions.',
        },
        {
            icon: Shield,
            title: 'Safe & Secure',
            description: 'Your financial data is protected with enterprise-grade security. Your money info stays private.',
        },
    ];

    return (
        <>
            <Head title="MoneySmart PNG — Take Control of Your Finances" />
            <div className="min-h-screen bg-background text-foreground">
                {/* Header */}
                <header className="border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                        <div className="flex items-center gap-2">
                            <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary">
                                <Landmark className="h-5 w-5 text-primary-foreground" />
                            </div>
                            <span className="text-lg font-bold text-foreground">
                                MoneySmart <span className="text-primary">PNG</span>
                            </span>
                        </div>
                        <nav className="flex items-center gap-3">
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={login()}
                                        className="rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-secondary"
                                    >
                                        Log in
                                    </Link>
                                    {canRegister && (
                                        <Link
                                            href={register()}
                                            className="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                        >
                                            Get Started Free
                                        </Link>
                                    )}
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                {/* Hero */}
                <section className="mx-auto max-w-6xl px-4 py-16 text-center sm:px-6 sm:py-24 lg:px-8 lg:py-32">
                    <div className="mx-auto max-w-3xl">
                        <span className="mb-4 inline-block rounded-full bg-accent/15 px-4 py-1.5 text-sm font-medium text-accent-foreground">
                            Free for Papua New Guineans
                        </span>
                        <h1 className="mt-4 text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl lg:text-6xl">
                            Take Control of{' '}
                            <span className="text-primary">Your Finances</span>
                        </h1>
                        <p className="mt-6 text-lg leading-relaxed text-muted-foreground sm:text-xl">
                            MoneySmart PNG helps you track spending, set savings goals, and
                            build better money habits — all designed for life in Papua New
                            Guinea. Get personalised advice from your AI budget coach.
                        </p>
                        <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className="w-full rounded-lg bg-primary px-8 py-3 text-base font-semibold text-primary-foreground transition-colors hover:bg-primary/90 sm:w-auto"
                                >
                                    Go to Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={canRegister ? register() : login()}
                                        className="w-full rounded-lg bg-primary px-8 py-3 text-base font-semibold text-primary-foreground transition-colors hover:bg-primary/90 sm:w-auto"
                                    >
                                        Start for Free
                                    </Link>
                                    <Link
                                        href={login()}
                                        className="w-full rounded-lg border border-border bg-background px-8 py-3 text-base font-semibold text-foreground transition-colors hover:bg-secondary sm:w-auto"
                                    >
                                        Log in
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                </section>

                {/* Features */}
                <section className="border-t border-border bg-secondary/50 py-16 sm:py-24">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                        <div className="mb-12 text-center">
                            <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                                Everything you need to manage your money
                            </h2>
                            <p className="mt-4 text-lg text-muted-foreground">
                                Built for Papua New Guineans, by understanding what matters most.
                            </p>
                        </div>
                        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            {features.map((feature) => (
                                <div
                                    key={feature.title}
                                    className="rounded-xl border border-border bg-card p-6 shadow-sm transition-shadow hover:shadow-md"
                                >
                                    <div className="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10">
                                        <feature.icon className="h-5 w-5 text-primary" />
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold text-foreground">{feature.title}</h3>
                                    <p className="text-sm leading-relaxed text-muted-foreground">{feature.description}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* CTA */}
                <section className="border-t border-border py-16 sm:py-24">
                    <div className="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                        <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                            Ready to be smarter with your money?
                        </h2>
                        <p className="mt-4 text-lg text-muted-foreground">
                            Join MoneySmart PNG today. It&apos;s free to get started — no credit card needed.
                        </p>
                        <div className="mt-8">
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className="inline-block rounded-lg bg-primary px-8 py-3 text-base font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                >
                                    Go to Dashboard
                                </Link>
                            ) : (
                                <Link
                                    href={canRegister ? register() : login()}
                                    className="inline-block rounded-lg bg-primary px-8 py-3 text-base font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                >
                                    Create Your Free Account
                                </Link>
                            )}
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t border-border bg-secondary/30 py-8">
                    <div className="mx-auto max-w-6xl px-4 text-center text-sm text-muted-foreground sm:px-6 lg:px-8">
                        &copy; {new Date().getFullYear()} MoneySmart PNG. Built for Papua New Guinea.
                    </div>
                </footer>
            </div>
        </>
    );
}
