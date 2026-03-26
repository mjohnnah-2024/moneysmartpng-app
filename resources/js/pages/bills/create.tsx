import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/input-error';

type Props = {
    categories: string[];
};

export default function BillCreate({ categories }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        amount: '',
        category: '',
        frequency: 'monthly',
        due_day: '',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/bills');
    }

    return (
        <>
            <Head title="Add Recurring Bill" />
            <div className="mx-auto w-full max-w-lg p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Add Recurring Bill</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                            <div className="space-y-1">
                                <Label htmlFor="name">Bill Name</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="e.g. Rent, School Fees"
                                />
                                <InputError message={errors.name} />
                            </div>

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

                            <div className="space-y-1">
                                <Label htmlFor="frequency">Frequency</Label>
                                <Select value={data.frequency} onValueChange={(v) => setData('frequency', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="monthly">Monthly</SelectItem>
                                        <SelectItem value="fortnightly">Fortnightly</SelectItem>
                                        <SelectItem value="weekly">Weekly</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.frequency} />
                            </div>

                            <div className="space-y-1">
                                <Label htmlFor="due_day">Due Day (1-31)</Label>
                                <Input
                                    id="due_day"
                                    type="number"
                                    min="1"
                                    max="31"
                                    value={data.due_day}
                                    onChange={(e) => setData('due_day', e.target.value)}
                                />
                                <InputError message={errors.due_day} />
                            </div>

                            <Button type="submit" disabled={processing} className="w-full">
                                {processing ? 'Saving...' : 'Add Bill'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

BillCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Bills', href: '/bills' },
        { title: 'Add Bill', href: '/bills/create' },
    ],
};
