import { Head, useForm, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/input-error';
import type { Transaction } from '@/types';

type Props = {
    transaction: Transaction;
    categories: {
        expense: string[];
        income: string[];
    };
};

export default function TransactionEdit({ transaction, categories }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        amount: String(transaction.amount),
        type: transaction.type,
        category: transaction.category,
        description: transaction.description ?? '',
        date: transaction.date.split('T')[0],
    });

    const currentCategories = data.type === 'income' ? categories.income : categories.expense;

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(`/transactions/${transaction.id}`);
    }

    return (
        <>
            <Head title="Edit Transaction" />
            <div className="mx-auto w-full max-w-lg p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Transaction</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                            {/* Type */}
                            <div className="grid grid-cols-2 gap-2">
                                <Button
                                    type="button"
                                    variant={data.type === 'expense' ? 'default' : 'outline'}
                                    onClick={() => { setData('type', 'expense'); setData('category', ''); }}
                                    className="w-full"
                                >
                                    Expense
                                </Button>
                                <Button
                                    type="button"
                                    variant={data.type === 'income' ? 'default' : 'outline'}
                                    onClick={() => { setData('type', 'income'); setData('category', ''); }}
                                    className="w-full"
                                >
                                    Income
                                </Button>
                            </div>

                            {/* Amount */}
                            <div className="space-y-1">
                                <Label htmlFor="amount">Amount (Kina)</Label>
                                <Input
                                    id="amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0.00"
                                    value={data.amount}
                                    onChange={(e) => setData('amount', e.target.value)}
                                    className="text-lg"
                                />
                                <InputError message={errors.amount} />
                            </div>

                            {/* Category */}
                            <div className="space-y-1">
                                <Label htmlFor="category">Category</Label>
                                <Select value={data.category} onValueChange={(v) => setData('category', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {currentCategories.map((cat) => (
                                            <SelectItem key={cat} value={cat}>{cat}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.category} />
                            </div>

                            {/* Description */}
                            <div className="space-y-1">
                                <Label htmlFor="description">Description (optional)</Label>
                                <Input
                                    id="description"
                                    placeholder="e.g., Market shopping at Boroko"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                />
                                <InputError message={errors.description} />
                            </div>

                            {/* Date */}
                            <div className="space-y-1">
                                <Label htmlFor="date">Date</Label>
                                <Input
                                    id="date"
                                    type="date"
                                    value={data.date}
                                    onChange={(e) => setData('date', e.target.value)}
                                />
                                <InputError message={errors.date} />
                            </div>

                            <div className="flex gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    className="flex-1"
                                    onClick={() => router.get('/transactions')}
                                >
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={processing} className="flex-1">
                                    {processing ? 'Saving...' : 'Update Transaction'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

TransactionEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Transactions', href: '/transactions' },
        { title: 'Edit Transaction', href: '#' },
    ],
};
