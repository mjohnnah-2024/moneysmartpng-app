import { Link } from '@inertiajs/react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Crown, Check } from 'lucide-react';

type Props = {
    open: boolean;
    onClose: () => void;
    feature?: string;
};

const PREMIUM_FEATURES = [
    'Unlimited transactions',
    'Unlimited budget categories',
    'Unlimited savings goals',
    'Unlimited AI Coach messages',
    'Export transactions to CSV',
    'Priority support',
];

export function UpgradeModal({ open, onClose, feature }: Props) {
    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader className="text-center">
                    <div className="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                        <Crown className="h-6 w-6 text-amber-600" />
                    </div>
                    <DialogTitle className="text-center">Upgrade to Premium</DialogTitle>
                    <DialogDescription className="text-center">
                        {feature
                            ? `You've reached the free plan limit for ${feature}. Upgrade to continue.`
                            : 'Unlock all features with MoneySmart Premium.'}
                    </DialogDescription>
                </DialogHeader>

                <div className="mt-4 space-y-3">
                    {PREMIUM_FEATURES.map((item) => (
                        <div key={item} className="flex items-center gap-2 text-sm">
                            <Check className="h-4 w-4 shrink-0 text-green-600" />
                            <span>{item}</span>
                        </div>
                    ))}
                </div>

                <div className="mt-6 space-y-2">
                    <Button className="w-full" size="lg" asChild>
                        <Link href="/premium">
                            <Crown className="mr-2 h-4 w-4" />
                            Upgrade to Premium
                        </Link>
                    </Button>
                    <p className="text-center text-xs text-muted-foreground">
                        Or invite friends to earn free premium days!
                    </p>
                </div>
            </DialogContent>
        </Dialog>
    );
}
