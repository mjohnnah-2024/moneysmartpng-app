import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { AlertTriangle, Home, ArrowLeft } from 'lucide-react';

const STATUS_MESSAGES: Record<number, { title: string; description: string }> = {
    403: {
        title: 'Access Denied',
        description: "You don't have permission to access this page.",
    },
    404: {
        title: 'Page Not Found',
        description: "Sorry, we couldn't find the page you're looking for.",
    },
    500: {
        title: 'Server Error',
        description: 'Something went wrong on our end. Please try again later.',
    },
    503: {
        title: 'Service Unavailable',
        description: "We're currently performing maintenance. Please check back soon.",
    },
};

export default function Error({ status }: { status: number }) {
    const { title, description } = STATUS_MESSAGES[status] ?? {
        title: 'Error',
        description: 'An unexpected error occurred.',
    };

    return (
        <>
            <Head title={title} />
            <div className="flex min-h-screen items-center justify-center bg-background px-4">
                <div className="text-center">
                    <div className="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <AlertTriangle className="h-8 w-8 text-red-600" />
                    </div>
                    <h1 className="text-6xl font-bold text-foreground">{status}</h1>
                    <h2 className="mt-2 text-xl font-semibold text-foreground">{title}</h2>
                    <p className="mt-2 text-muted-foreground">{description}</p>
                    <div className="mt-8 flex items-center justify-center gap-3">
                        <Button variant="outline" onClick={() => window.history.back()}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Go Back
                        </Button>
                        <Button asChild>
                            <Link href="/dashboard">
                                <Home className="mr-2 h-4 w-4" />
                                Dashboard
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
