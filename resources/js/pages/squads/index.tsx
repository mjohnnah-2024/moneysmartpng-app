import { Head, Link, router, usePage, useForm } from '@inertiajs/react';
import { Users, Plus, LogIn, Crown, Shield } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import type { SquadData, Flash } from '@/types';
import { useState } from 'react';
import { store as storeSquad } from '@/actions/App/Http/Controllers/SquadController';
import { join as joinSquad } from '@/actions/App/Http/Controllers/SquadController';

type Props = {
    squads: SquadData[];
    canCreate: boolean;
};

function CreateSquadForm({ onCancel }: { onCancel: () => void }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(storeSquad().url);
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-lg">Create a Squad</CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="flex flex-col gap-3">
                    <div>
                        <Label htmlFor="name">Squad Name</Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="e.g. Family Savings"
                        />
                        {errors.name && <p className="mt-1 text-sm text-red-500">{errors.name}</p>}
                    </div>
                    <div>
                        <Label htmlFor="description">Description (optional)</Label>
                        <Input
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="What is this squad about?"
                        />
                    </div>
                    <div className="flex gap-2">
                        <Button type="submit" disabled={processing}>Create</Button>
                        <Button type="button" variant="ghost" onClick={onCancel}>Cancel</Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}

function JoinSquadForm({ onCancel }: { onCancel: () => void }) {
    const { data, setData, post, processing, errors } = useForm({
        invite_code: '',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(joinSquad().url);
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-lg">Join a Squad</CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="flex flex-col gap-3">
                    <div>
                        <Label htmlFor="invite_code">Invite Code</Label>
                        <Input
                            id="invite_code"
                            value={data.invite_code}
                            onChange={(e) => setData('invite_code', e.target.value.toUpperCase())}
                            placeholder="e.g. ABC12345"
                            maxLength={8}
                        />
                        {errors.invite_code && <p className="mt-1 text-sm text-red-500">{errors.invite_code}</p>}
                    </div>
                    <div className="flex gap-2">
                        <Button type="submit" disabled={processing}>Join</Button>
                        <Button type="button" variant="ghost" onClick={onCancel}>Cancel</Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}

export default function SquadsIndex({ squads, canCreate }: Props) {
    const { flash } = usePage<{ flash: Flash }>().props;
    const [showCreate, setShowCreate] = useState(false);
    const [showJoin, setShowJoin] = useState(false);

    return (
        <>
            <Head title="Savings Squads" />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Savings Squads</h1>
                    <div className="flex gap-2">
                        {canCreate && (
                            <Button size="sm" onClick={() => { setShowCreate(true); setShowJoin(false); }}>
                                <Plus className="mr-1 h-4 w-4" />
                                Create
                            </Button>
                        )}
                        <Button size="sm" variant="outline" onClick={() => { setShowJoin(true); setShowCreate(false); }}>
                            <LogIn className="mr-1 h-4 w-4" />
                            Join
                        </Button>
                    </div>
                </div>

                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-3 text-sm text-green-700">{flash.success}</div>
                )}
                {flash?.error && (
                    <div className="rounded-md bg-red-50 p-3 text-sm text-red-700">{flash.error}</div>
                )}

                {showCreate && <CreateSquadForm onCancel={() => setShowCreate(false)} />}
                {showJoin && <JoinSquadForm onCancel={() => setShowJoin(false)} />}

                {squads.length === 0 && !showCreate && !showJoin && (
                    <Card>
                        <CardContent className="flex flex-col items-center gap-3 py-8">
                            <Users className="h-12 w-12 text-muted-foreground" />
                            <p className="text-muted-foreground">You haven't joined any squads yet.</p>
                            <p className="text-sm text-muted-foreground">
                                Create a squad or join one with an invite code to start saving together!
                            </p>
                        </CardContent>
                    </Card>
                )}

                <div className="flex flex-col gap-3">
                    {squads.map((squad) => (
                        <Link key={squad.id} href={`/squads/${squad.id}`} className="block">
                            <Card className="transition-colors hover:bg-muted/50">
                                <CardContent className="flex items-center gap-3 p-4">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                        <Users className="h-5 w-5 text-primary" />
                                    </div>
                                    <div className="flex-1">
                                        <div className="flex items-center gap-2">
                                            <h3 className="font-semibold">{squad.name}</h3>
                                            {squad.role === 'admin' && (
                                                <Badge variant="secondary" className="text-xs">
                                                    <Crown className="mr-0.5 h-3 w-3" />
                                                    Admin
                                                </Badge>
                                            )}
                                        </div>
                                        {squad.description && (
                                            <p className="text-sm text-muted-foreground line-clamp-1">{squad.description}</p>
                                        )}
                                        <div className="mt-1 flex gap-3 text-xs text-muted-foreground">
                                            <span>{squad.member_count} members</span>
                                            <span>{squad.challenge_count} challenges</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>
                    ))}
                </div>
            </div>
        </>
    );
}
