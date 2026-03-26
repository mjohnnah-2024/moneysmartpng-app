import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/input-error';
import { UpgradeModal } from '@/components/upgrade-modal';
import type { Flash } from '@/types';

type Props = {
    categories: {
        expense: string[];
        income: string[];
    };
};

export default function TransactionCreate({ categories }: Props) {
    const { flash } = usePage<{ flash: Flash }>().props;
    const [showUpgrade, setShowUpgrade] = useState(false);

    useEffect(() => {
        if (flash?.error?.includes('Upgrade to premium')) {
            setShowUpgrade(true);
        }
    }, [flash?.error]);

    const { data, setData, post, processing, errors } = useForm({
        amount: '',
        type: 'expense' as 'income' | 'expense',
        category: '',
        description: '',
        date: new Date().toISOString().split('T')[0],
    });

    const currentCategories = data.type === 'income' ? categories.income : categories.expense;

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/transactions');
    }

    return (
        <>
            <Head title="Add Transaction" />
            <div className="mx-auto w-full max-w-lg p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Add Transaction</CardTitle>
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
                                    {processing ? 'Saving...' : 'Save Transaction'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <UpgradeModal
                open={showUpgrade}
                onClose={() => setShowUpgrade(false)}
                feature="transactions"
            />
        </>
    );
}

TransactionCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Transactions', href: '/transactions' },
        { title: 'Add Transaction', href: '/transactions/create' },
    ],
};
