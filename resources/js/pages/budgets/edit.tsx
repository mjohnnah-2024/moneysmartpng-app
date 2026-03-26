import { Head, useForm, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/input-error';
import type { Budget } from '@/types';

type Props = {
    budget: Budget;
    categories: string[];
};

export default function BudgetEdit({ budget, categories }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        category: budget.category,
        amount_limit: String(budget.amount_limit),
        month: budget.month.slice(0, 10),
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(`/budgets/${budget.id}`);
    }

    return (
        <>
            <Head title="Edit Budget" />
            <div className="mx-auto w-full max-w-lg p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Budget</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                            {/* Category */}
                            <div className="space-y-1">
                                <Label htmlFor="category">Category</Label>
                                <Select value={data.category} onValueChange={(v) => setData('category', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {categories.map((cat) => (
                                            <SelectItem key={cat} value={cat}>{cat}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.category} />
                            </div>

                            {/* Amount Limit */}
                            <div className="space-y-1">
                                <Label htmlFor="amount_limit">Budget Limit (Kina)</Label>
                                <Input
                                    id="amount_limit"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0.00"
                                    value={data.amount_limit}
                                    onChange={(e) => setData('amount_limit', e.target.value)}
                                    className="text-lg"
                                />
                                <InputError message={errors.amount_limit} />
                            </div>

                            {/* Month */}
                            <div className="space-y-1">
                                <Label htmlFor="month">Month</Label>
                                <Input
                                    id="month"
                                    type="month"
                                    value={data.month.slice(0, 7)}
                                    onChange={(e) => setData('month', e.target.value + '-01')}
                                />
                                <InputError message={errors.month} />
                            </div>

                            <div className="flex gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    className="flex-1"
                                    onClick={() => router.get('/budgets')}
                                >
                                    Cancel
                                </Button>
                                <Button type="submit" className="flex-1" disabled={processing}>
                                    Update Budget
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
