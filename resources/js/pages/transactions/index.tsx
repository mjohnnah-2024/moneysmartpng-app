import { Head, Link, router, usePage } from '@inertiajs/react';
import { Plus, Search, ArrowDownLeft, ArrowUpRight, Pencil, Trash2 } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Transaction, PaginatedData, Flash } from '@/types';
import { useState } from 'react';

type Props = {
    transactions: PaginatedData<Transaction>;
    filters: {
        search?: string;
        type?: string;
        category?: string;
    };
    categories: string[];
};

function formatKina(amount: number): string {
    return `K ${Number(amount).toLocaleString('en-PG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export default function TransactionsIndex({ transactions, filters, categories }: Props) {
    const { flash } = usePage<{ flash: Flash }>().props;
    const [search, setSearch] = useState(filters.search ?? '');

    function handleSearch(e: React.FormEvent) {
        e.preventDefault();
        router.get('/transactions', { search, type: filters.type, category: filters.category }, {
            preserveState: true,
            replace: true,
        });
    }

    function handleFilterChange(key: string, value: string) {
        const params: Record<string, string> = { ...filters, [key]: value };
        if (value === 'all') {
            delete params[key];
        }
        router.get('/transactions', params, { preserveState: true, replace: true });
    }

    function handleDelete(id: number) {
        if (confirm('Are you sure you want to delete this transaction?')) {
            router.delete(`/transactions/${id}`);
        }
    }

    return (
        <>
            <Head title="Transactions" />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Transactions</h1>
                    <Button asChild size="sm">
                        <Link href="/transactions/create">
                            <Plus className="mr-1 h-4 w-4" />
                            Add
                        </Link>
                    </Button>
                </div>

                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-300">
                        {flash.success}
                    </div>
                )}

                {/* Filters */}
                <div className="flex flex-col gap-2 sm:flex-row">
                    <form onSubmit={handleSearch} className="flex flex-1 gap-2">
                        <div className="relative flex-1">
                            <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                placeholder="Search transactions..."
                                className="pl-9"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                    </form>
                    <Select value={filters.type ?? 'all'} onValueChange={(v) => handleFilterChange('type', v)}>
                        <SelectTrigger className="w-full sm:w-32">
                            <SelectValue placeholder="Type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Types</SelectItem>
                            <SelectItem value="income">Income</SelectItem>
                            <SelectItem value="expense">Expense</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                {/* Transaction List */}
                {transactions.data.length > 0 ? (
                    <div className="flex flex-col gap-2">
                        {transactions.data.map((t) => (
                            <Card key={t.id}>
                                <CardContent className="flex items-center justify-between p-3">
                                    <div className="flex items-center gap-3">
                                        <div className={`rounded-full p-2 ${t.type === 'income' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30'}`}>
                                            {t.type === 'income' ? (
                                                <ArrowDownLeft className="h-4 w-4 text-green-600" />
                                            ) : (
                                                <ArrowUpRight className="h-4 w-4 text-red-600" />
                                            )}
                                        </div>
                                        <div>
                                            <p className="text-sm font-medium">{t.category}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {t.description ? `${t.description} · ` : ''}
                                                {new Date(t.date).toLocaleDateString()}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className={`text-sm font-semibold ${t.type === 'income' ? 'text-green-600' : 'text-red-600'}`}>
                                            {t.type === 'income' ? '+' : '-'}{formatKina(t.amount)}
                                        </span>
                                        <div className="flex gap-1">
                                            <Button variant="ghost" size="icon" className="h-7 w-7" asChild>
                                                <Link href={`/transactions/${t.id}/edit`}>
                                                    <Pencil className="h-3.5 w-3.5" />
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="icon" className="h-7 w-7 text-destructive" onClick={() => handleDelete(t.id)}>
                                                <Trash2 className="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <p className="text-muted-foreground">No transactions found.</p>
                            <Button asChild className="mt-4" size="sm">
                                <Link href="/transactions/create">Add your first transaction</Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}

                {/* Pagination */}
                {transactions.last_page > 1 && (
                    <div className="flex justify-center gap-1">
                        {transactions.links.map((link, i) => (
                            <Button
                                key={i}
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                disabled={!link.url}
                                onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                className="text-xs"
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

TransactionsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Transactions', href: '/transactions' },
    ],
};
