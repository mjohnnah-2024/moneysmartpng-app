import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';
import { UpgradeModal } from '@/components/upgrade-modal';

// Mock @inertiajs/react Link
vi.mock('@inertiajs/react', () => ({
    Link: ({ children, href, ...props }: { children: React.ReactNode; href: string; [key: string]: unknown }) => (
        <a href={href} {...props}>{children}</a>
    ),
}));

describe('UpgradeModal', () => {
    it('renders premium features when open', () => {
        render(<UpgradeModal open={true} onClose={() => {}} />);

        expect(screen.getAllByText('Upgrade to Premium')).toHaveLength(2); // heading + button
        expect(screen.getByText('Unlimited transactions')).toBeInTheDocument();
        expect(screen.getByText('Unlimited budget categories')).toBeInTheDocument();
        expect(screen.getByText('Unlimited savings goals')).toBeInTheDocument();
        expect(screen.getByText('Unlimited AI Coach messages')).toBeInTheDocument();
        expect(screen.getByText('Export transactions to CSV')).toBeInTheDocument();
        expect(screen.getByText('Priority support')).toBeInTheDocument();
    });

    it('shows feature-specific message when feature prop is provided', () => {
        render(<UpgradeModal open={true} onClose={() => {}} feature="transactions" />);

        expect(
            screen.getByText("You've reached the free plan limit for transactions. Upgrade to continue."),
        ).toBeInTheDocument();
    });

    it('shows generic message when no feature prop', () => {
        render(<UpgradeModal open={true} onClose={() => {}} />);

        expect(screen.getByText('Unlock all features with MoneySmart Premium.')).toBeInTheDocument();
    });

    it('contains a link to the premium page', () => {
        render(<UpgradeModal open={true} onClose={() => {}} />);

        const link = screen.getByRole('link', { name: /upgrade to premium/i });
        expect(link).toHaveAttribute('href', '/premium');
    });

    it('shows referral invite text', () => {
        render(<UpgradeModal open={true} onClose={() => {}} />);

        expect(screen.getByText(/invite friends to earn free premium days/i)).toBeInTheDocument();
    });
});
