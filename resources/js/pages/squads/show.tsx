import { Head, Link, router, usePage, useForm } from '@inertiajs/react';
import { Users, Trophy, Copy, Share2, Trash2, LogOut, Plus, Clock } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import type { Flash } from '@/types';
import { useState } from 'react';
import { store as storeChallenge } from '@/actions/App/Http/Controllers/SquadChallengeController';

type SquadDetail = {
    id: number;
    name: string;
    description: string | null;
    invite_code: string;
    max_members: number;
    is_active: boolean;
    member_count: number;
    challenge_count: number;
};

type Member = {
    id: number;
    name: string;
    role: 'admin' | 'member';
    joined_at: string;
};

type ChallengeRow = {
    id: number;
    name: string;
    target_amount: number;
    starts_at: string;
    ends_at: string;
    is_active: boolean;
    progress: Array<{
        user_name: string;
        percentage: number;
    }>;
};

type Props = {
    squad: SquadDetail;
    members: Member[];
    challenges: ChallengeRow[];
    userRole: 'admin' | 'member';
};

function CreateChallengeForm({ squadId, onCancel }: { squadId: number; onCancel: () => void }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        target_amount: '',
        starts_at: new Date().toISOString().slice(0, 10),
        ends_at: '',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(storeChallenge(squadId).url);
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-lg">New Challenge</CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="flex flex-col gap-3">
                    <div>
                        <Label htmlFor="challenge-name">Challenge Name</Label>
                        <Input
                            id="challenge-name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="e.g. Save K100 this week"
                        />
                        {errors.name && <p className="mt-1 text-sm text-red-500">{errors.name}</p>}
                    </div>
                    <div>
                        <Label htmlFor="target">Target Amount (K)</Label>
                        <Input
                            id="target"
                            type="number"
                            step="0.01"
                            min="1"
                            value={data.target_amount}
                            onChange={(e) => setData('target_amount', e.target.value)}
                        />
                        {errors.target_amount && <p className="mt-1 text-sm text-red-500">{errors.target_amount}</p>}
                    </div>
                    <div className="grid grid-cols-2 gap-2">
                        <div>
                            <Label htmlFor="starts">Start Date</Label>
                            <Input
                                id="starts"
                                type="date"
                                value={data.starts_at}
                                onChange={(e) => setData('starts_at', e.target.value)}
                            />
                            {errors.starts_at && <p className="mt-1 text-sm text-red-500">{errors.starts_at}</p>}
                        </div>
                        <div>
                            <Label htmlFor="ends">End Date</Label>
                            <Input
                                id="ends"
                                type="date"
                                value={data.ends_at}
                                onChange={(e) => setData('ends_at', e.target.value)}
                            />
                            {errors.ends_at && <p className="mt-1 text-sm text-red-500">{errors.ends_at}</p>}
                        </div>
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

export default function SquadShow({ squad, members, challenges, userRole }: Props) {
    const { flash } = usePage<{ flash: Flash }>().props;
    const [showCreateChallenge, setShowCreateChallenge] = useState(false);
    const [copied, setCopied] = useState(false);

    function copyInviteCode() {
        navigator.clipboard.writeText(squad.invite_code).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    }

    function shareViaWhatsApp() {
        const message = `Join my savings squad "${squad.name}" on MoneySmart PNG! Use invite code: ${squad.invite_code}`;
        window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
    }

    function handleLeave() {
        if (confirm('Are you sure you want to leave this squad?')) {
            router.post(`/squads/${squad.id}/leave`);
        }
    }

    function handleDelete() {
        if (confirm('Are you sure you want to delete this squad? This cannot be undone.')) {
            router.delete(`/squads/${squad.id}`);
        }
    }

    return (
        <>
            <Head title={squad.name} />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center gap-2">
                    <Link href="/squads" className="text-sm text-muted-foreground hover:underline">&larr; Squads</Link>
                </div>

                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-3 text-sm text-green-700">{flash.success}</div>
                )}
                {flash?.error && (
                    <div className="rounded-md bg-red-50 p-3 text-sm text-red-700">{flash.error}</div>
                )}

                {/* Squad Header */}
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-start justify-between">
                            <div>
                                <h1 className="text-xl font-bold">{squad.name}</h1>
                                {squad.description && (
                                    <p className="mt-1 text-sm text-muted-foreground">{squad.description}</p>
                                )}
                            </div>
                            <div className="flex gap-1">
                                {userRole === 'admin' ? (
                                    <Button size="sm" variant="destructive" onClick={handleDelete}>
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                ) : (
                                    <Button size="sm" variant="outline" onClick={handleLeave}>
                                        <LogOut className="h-4 w-4" />
                                    </Button>
                                )}
                            </div>
                        </div>

                        {/* Invite Code */}
                        <div className="mt-3 flex items-center gap-2 rounded-md bg-muted p-2">
                            <span className="text-xs text-muted-foreground">Invite Code:</span>
                            <code className="font-mono font-bold">{squad.invite_code}</code>
                            <Button size="sm" variant="ghost" className="h-7 px-2" onClick={copyInviteCode}>
                                <Copy className="h-3.5 w-3.5" />
                                {copied ? 'Copied!' : 'Copy'}
                            </Button>
                            <Button size="sm" variant="ghost" className="h-7 px-2" onClick={shareViaWhatsApp}>
                                <Share2 className="h-3.5 w-3.5" />
                                WhatsApp
                            </Button>
                        </div>

                        <div className="mt-2 flex gap-4 text-xs text-muted-foreground">
                            <span><Users className="mr-1 inline h-3.5 w-3.5" />{squad.member_count}/{squad.max_members} members</span>
                            <span><Trophy className="mr-1 inline h-3.5 w-3.5" />{squad.challenge_count} challenges</span>
                        </div>
                    </CardContent>
                </Card>

                {/* Members */}
                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base">Members</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-2">
                        {members.map((member) => (
                            <div key={member.id} className="flex items-center justify-between py-1">
                                <span className="text-sm">{member.name}</span>
                                <Badge variant={member.role === 'admin' ? 'default' : 'secondary'} className="text-xs">
                                    {member.role}
                                </Badge>
                            </div>
                        ))}
                    </CardContent>
                </Card>

                {/* Challenges */}
                <div className="flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Challenges</h2>
                    {userRole === 'admin' && (
                        <Button size="sm" onClick={() => setShowCreateChallenge(true)}>
                            <Plus className="mr-1 h-4 w-4" />
                            New Challenge
                        </Button>
                    )}
                </div>

                {showCreateChallenge && (
                    <CreateChallengeForm squadId={squad.id} onCancel={() => setShowCreateChallenge(false)} />
                )}

                {challenges.length === 0 && (
                    <Card>
                        <CardContent className="flex flex-col items-center gap-2 py-6">
                            <Trophy className="h-8 w-8 text-muted-foreground" />
                            <p className="text-sm text-muted-foreground">No challenges yet.</p>
                        </CardContent>
                    </Card>
                )}

                {challenges.map((challenge) => (
                    <Link key={challenge.id} href={`/squads/${squad.id}/challenges/${challenge.id}`} className="block">
                        <Card className="transition-colors hover:bg-muted/50">
                            <CardContent className="p-4">
                                <div className="flex items-center justify-between">
                                    <h3 className="font-semibold">{challenge.name}</h3>
                                    <Badge variant={challenge.is_active ? 'default' : 'secondary'}>
                                        {challenge.is_active ? 'Active' : 'Ended'}
                                    </Badge>
                                </div>
                                <div className="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                                    <Clock className="h-3 w-3" />
                                    {challenge.starts_at} to {challenge.ends_at}
                                </div>
                                {challenge.progress.length > 0 && (
                                    <div className="mt-2 flex flex-col gap-1.5">
                                        {challenge.progress.slice(0, 3).map((p, idx) => (
                                            <div key={idx} className="flex items-center gap-2">
                                                <span className="w-24 truncate text-xs">{p.user_name}</span>
                                                <Progress value={p.percentage} className="h-2 flex-1" />
                                                <span className="w-10 text-right text-xs font-medium">{p.percentage}%</span>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </Link>
                ))}
            </div>
        </>
    );
}
