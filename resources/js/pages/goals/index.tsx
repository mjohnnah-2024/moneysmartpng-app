import { Head, Link, router, usePage, useForm } from '@inertiajs/react';
import { Plus, Pencil, Trash2, Target, TrendingUp, Trophy } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Flash } from '@/types';
import { useState } from 'react';

type GoalRow = {
    id: number;
    name: string;
    target_amount: number;
    current_amount: number;
    deadline: string | null;
    status: 'active' | 'completed' | 'cancelled';
    percentage: number;
    created_at: string;
};

type GoalSummary = {
    activeCount: number;
    completedCount: number;
    totalSaved: number;
    totalTarget: number;
};

type Props = {
    goals: GoalRow[];
    filter: string;
    summary: GoalSummary;
};

function formatKina(amount: number): string {
    return `K ${amount.toLocaleString('en-PG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function ContributeForm({ goalId }: { goalId: number }) {
    const { data, setData, post, processing, errors, reset } = useForm({ amount: '' });
    const [open, setOpen] = useState(false);

    if (!open) {
        return (
            <Button size="sm" variant="outline" onClick={() => setOpen(true)}>
                <TrendingUp className="mr-1 h-3.5 w-3.5" />
                Add
            </Button>
        );
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(`/goals/${goalId}/contribute`, {
            onSuccess: () => { reset(); setOpen(false); },
        });
    }

    return (
        <form onSubmit={handleSubmit} className="flex items-center gap-1">
            <Input
                type="number"
                step="0.01"
                min="0.01"
                placeholder="K"
                className="h-8 w-24 text-sm"
                value={data.amount}
                onChange={(e) => setData('amount', e.target.value)}
            />
            <Button size="sm" type="submit" disabled={processing}>Save</Button>
            <Button size="sm" variant="ghost" type="button" onClick={() => setOpen(false)}>×</Button>
        </form>
    );
}

export default function GoalsIndex({ goals, filter, summary }: Props) {
    const { flash } = usePage<{ flash: Flash }>().props;

    function handleFilterChange(value: string) {
        router.get('/goals', { status: value }, { preserveState: true, replace: true });
    }

    function handleDelete(id: number) {
        if (confirm('Are you sure you want to delete this goal?')) {
            router.delete(`/goals/${id}`);
        }
    }

    const overallPct = summary.totalTarget > 0 ? Math.round((summary.totalSaved / summary.totalTarget) * 100) : 0;

    return (
        <>
            <Head title="Goals" />
            <div className="flex flex-col gap-4 p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Goals</h1>
                    <Button asChild size="sm">
                        <Link href="/goals/create">
                            <Plus className="mr-1 h-4 w-4" />
                            New Goal
                        </Link>
                    </Button>
                </div>

                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-300">
                        {flash.success}
                    </div>
                )}

                {/* Summary Cards */}
                <div className="grid grid-cols-2 gap-2">
                    <Card>
                        <CardContent className="p-3 text-center">
                            <p className="text-2xl font-bold">{summary.activeCount}</p>
                            <p className="text-xs text-muted-foreground">Active</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-3 text-center">
                            <p className="text-2xl font-bold">{summary.completedCount}</p>
                            <p className="text-xs text-muted-foreground">Completed</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Overall Progress */}
                {summary.activeCount > 0 && (
                    <Card>
                        <CardContent className="p-4">
                            <div className="mb-2 flex items-center justify-between text-sm">
                                <span className="text-muted-foreground">Total Saved</span>
                                <span className="font-semibold text-emerald-600">{overallPct}%</span>
                            </div>
                            <div className="mb-2 h-3 overflow-hidden rounded-full bg-muted">
                                <div
                                    className="h-full rounded-full bg-emerald-500 transition-all"
                                    style={{ width: `${Math.min(overallPct, 100)}%` }}
                                />
                            </div>
                            <div className="flex justify-between text-xs text-muted-foreground">
                                <span>{formatKina(summary.totalSaved)}</span>
                                <span>{formatKina(summary.totalTarget)}</span>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Filter */}
                <Select value={filter} onValueChange={handleFilterChange}>
                    <SelectTrigger className="w-full sm:w-40">
                        <SelectValue placeholder="Filter" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="completed">Completed</SelectItem>
                        <SelectItem value="all">All Goals</SelectItem>
                    </SelectContent>
                </Select>

                {/* Goal List */}
                {goals.length > 0 ? (
                    <div className="flex flex-col gap-2">
                        {goals.map((g) => (
                            <Card key={g.id}>
                                <CardContent className="p-3">
                                    <div className="mb-2 flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            {g.status === 'completed' ? (
                                                <Trophy className="h-4 w-4 text-amber-500" />
                                            ) : (
                                                <Target className="h-4 w-4 text-muted-foreground" />
                                            )}
                                            <span className="text-sm font-medium">{g.name}</span>
                                            {g.status === 'completed' && (
                                                <Badge variant="secondary" className="text-xs">Done</Badge>
                                            )}
                                        </div>
                                        <div className="flex items-center gap-1">
                                            <Button variant="ghost" size="icon" className="h-7 w-7" asChild>
                                                <Link href={`/goals/${g.id}/edit`}>
                                                    <Pencil className="h-3.5 w-3.5" />
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="icon" className="h-7 w-7 text-destructive" onClick={() => handleDelete(g.id)}>
                                                <Trash2 className="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </div>

                                    <div className="mb-1 h-2 overflow-hidden rounded-full bg-muted">
                                        <div
                                            className="h-full rounded-full bg-emerald-500 transition-all"
                                            style={{ width: `${Math.min(g.percentage, 100)}%` }}
                                        />
                                    </div>
                                    <div className="mb-2 flex justify-between text-xs text-muted-foreground">
                                        <span>{formatKina(g.current_amount)}</span>
                                        <span>{formatKina(g.target_amount)}</span>
                                    </div>

                                    <div className="flex items-center justify-between">
                                        {g.deadline && (
                                            <span className="text-xs text-muted-foreground">
                                                Deadline: {new Date(g.deadline).toLocaleDateString()}
                                            </span>
                                        )}
                                        {g.status === 'active' && (
                                            <ContributeForm goalId={g.id} />
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <p className="text-muted-foreground">No goals found.</p>
                            <Button asChild className="mt-4" size="sm">
                                <Link href="/goals/create">Create your first goal</Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}
