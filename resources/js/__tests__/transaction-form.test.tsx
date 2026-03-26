import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import TransactionCreate from '@/pages/transactions/create';

const mockPost = vi.fn();

vi.mock('@inertiajs/react', () => ({
    Head: ({ title }: { title: string }) => <title>{title}</title>,
    Link: ({ children, href, ...props }: { children: React.ReactNode; href: string; [key: string]: unknown }) => (
        <a href={href} {...props}>{children}</a>
    ),
    usePage: () => ({
        props: {
            flash: {},
            auth: { user: { id: 1 }, profile: null },
        },
    }),
    useForm: (defaults: Record<string, unknown>) => {
        const data = { ...defaults };
        return {
            data,
            setData: vi.fn((key: string, value: unknown) => {
                (data as Record<string, unknown>)[key] = value;
            }),
            post: mockPost,
            processing: false,
            errors: {},
        };
    },
    router: {
        visit: vi.fn(),
    },
}));

const CATEGORIES = {
    expense: [
        'Food/Market',
        'Transport PMV',
        'Phone/Internet',
        'Utilities',
        'Church/Donations',
        'School Fees',
        'Health',
        'Betel Nut',
        'Entertainment',
        'Household',
        'Clothing',
        'Other',
    ],
    income: ['Salary/Wages', 'Business Sales', 'Family Support', 'Government Aid', 'Other Income'],
};

describe('TransactionCreate', () => {
    beforeEach(() => {
        mockPost.mockClear();
    });

    it('renders the form with all fields', () => {
        render(<TransactionCreate categories={CATEGORIES} />);

        expect(screen.getByText('Add Transaction')).toBeInTheDocument();
        expect(screen.getByLabelText('Amount (Kina)')).toBeInTheDocument();
        expect(screen.getByLabelText(/description/i)).toBeInTheDocument();
        expect(screen.getByLabelText('Date')).toBeInTheDocument();
    });

    it('renders expense and income type buttons', () => {
        render(<TransactionCreate categories={CATEGORIES} />);

        expect(screen.getByRole('button', { name: 'Expense' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Income' })).toBeInTheDocument();
    });

    it('has a submit button', () => {
        render(<TransactionCreate categories={CATEGORIES} />);

        expect(screen.getByRole('button', { name: /save transaction/i })).toBeInTheDocument();
    });

    it('renders the amount input with correct attributes', () => {
        render(<TransactionCreate categories={CATEGORIES} />);

        const amountInput = screen.getByLabelText('Amount (Kina)');
        expect(amountInput).toHaveAttribute('type', 'number');
        expect(amountInput).toHaveAttribute('step', '0.01');
        expect(amountInput).toHaveAttribute('min', '0.01');
    });

    it('renders the date input with today as default', () => {
        render(<TransactionCreate categories={CATEGORIES} />);

        const dateInput = screen.getByLabelText('Date');
        expect(dateInput).toHaveAttribute('type', 'date');
        expect(dateInput).toHaveAttribute('value', new Date().toISOString().split('T')[0]);
    });

    it('calls post when form is submitted', async () => {
        const user = userEvent.setup();
        render(<TransactionCreate categories={CATEGORIES} />);

        const form = screen.getByRole('button', { name: /save transaction/i }).closest('form');
        if (form) {
            await user.click(screen.getByRole('button', { name: /save transaction/i }));
        }

        expect(mockPost).toHaveBeenCalledWith('/transactions');
    });
});
