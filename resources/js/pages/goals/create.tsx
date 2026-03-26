import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import { UpgradeModal } from '@/components/upgrade-modal';
import type { Flash } from '@/types';

export default function GoalCreate() {
    const { flash } = usePage<{ flash: Flash }>().props;
    const [showUpgrade, setShowUpgrade] = useState(false);

    useEffect(() => {
        if (flash?.error?.includes('Upgrade to premium')) {
            setShowUpgrade(true);
        }
    }, [flash?.error]);

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        target_amount: '',
        deadline: '',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/goals');
    }

    return (
        <>
            <Head title="New Goal" />
            <div className="mx-auto w-full max-w-lg p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>New Goal</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                            {/* Name */}
                            <div className="space-y-1">
                                <Label htmlFor="name">Goal Name</Label>
                                <Input
                                    id="name"
                                    placeholder="e.g., Emergency Fund, School Fees"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                />
                                <InputError message={errors.name} />
                            </div>

                            {/* Target Amount */}
                            <div className="space-y-1">
                                <Label htmlFor="target_amount">Target Amount (Kina)</Label>
                                <Input
                                    id="target_amount"
                                    type="number"
                                    step="0.01"
                                    min="1"
                                    placeholder="0.00"
                                    value={data.target_amount}
                                    onChange={(e) => setData('target_amount', e.target.value)}
                                    className="text-lg"
                                />
                                <InputError message={errors.target_amount} />
                            </div>

                            {/* Deadline */}
                            <div className="space-y-1">
                                <Label htmlFor="deadline">Deadline (optional)</Label>
                                <Input
                                    id="deadline"
                                    type="date"
                                    value={data.deadline}
                                    onChange={(e) => setData('deadline', e.target.value)}
                                />
                                <InputError message={errors.deadline} />
                            </div>

                            <div className="flex gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    className="flex-1"
                                    onClick={() => router.get('/goals')}
                                >
                                    Cancel
                                </Button>
                                <Button type="submit" className="flex-1" disabled={processing}>
                                    Create Goal
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <UpgradeModal
                open={showUpgrade}
                onClose={() => setShowUpgrade(false)}
                feature="active goals"
            />
        </>
    );
}
