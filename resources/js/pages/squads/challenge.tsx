import { Head, Link, usePage, useForm } from '@inertiajs/react';
import { Trophy, Calendar, TrendingUp } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import type { Flash } from '@/types';
import { useState } from 'react';
import { updateProgress } from '@/actions/App/Http/Controllers/SquadChallengeController';

type ChallengeDetail = {
    id: number;
    name: string;
    target_amount: number;
    starts_at: string;
    ends_at: string;
    is_active: boolean;
};

type LeaderboardEntry = {
    user_name: string;
    percentage: number;
};

type UserProgress = {
    current_amount: number;
    percentage: number;
} | null;

type Props = {
    squad: { id: number; name: string };
    challenge: ChallengeDetail;
    leaderboard: LeaderboardEntry[];
    userProgress: UserProgress;
};

function UpdateProgressForm({ squadId, challengeId }: { squadId: number; challengeId: number }) {
    const { data, setData, post, processing, errors } = useForm({
        amount: '',
    });
    const [open, setOpen] = useState(false);

    if (!open) {
        return (
            <Button size="sm" onClick={() => setOpen(true)}>
                <TrendingUp className="mr-1 h-4 w-4" />
                Update Progress
            </Button>
        );
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(updateProgress({ squad: squadId, challenge: challengeId }).url, {
            onSuccess: () => setOpen(false),
        });
    }

    return (
        <form onSubmit={handleSubmit} className="flex items-center gap-2">
            <div>
                <Input
                    type="number"
                    step="0.01"
                    min="0.01"
                    placeholder="Amount saved (K)"
                    className="h-9 w-40"
                    value={data.amount}
                    onChange={(e) => setData('amount', e.target.value)}
                />
                {errors.amount && <p className="mt-1 text-xs text-red-500">{errors.amount}</p>}
            </div>
            <Button size="sm" type="submit" disabled={processing}>Save</Button>
            <Button size="sm" variant="ghost" type="button" onClick={() => setOpen(false)}>Cancel</Button>
        </form>
    );
}

export default function ChallengeShow({ squad, challenge, leaderboard, userProgress }: Props) {
    const { flash } = usePage<{ flash: Flash }>().props;

    return (
        <>
            <Head title={challenge.name} />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center gap-2">
                    <Link href={`/squads/${squad.id}`} className="text-sm text-muted-foreground hover:underline">
                        &larr; {squad.name}
                    </Link>
                </div>

                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-3 text-sm text-green-700">{flash.success}</div>
                )}

                {/* Challenge Header */}
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center justify-between">
                            <h1 className="text-xl font-bold">{challenge.name}</h1>
                            <Badge variant={challenge.is_active ? 'default' : 'secondary'}>
                                {challenge.is_active ? 'Active' : 'Ended'}
                            </Badge>
                        </div>
                        <div className="mt-2 flex items-center gap-2 text-sm text-muted-foreground">
                            <Calendar className="h-4 w-4" />
                            {challenge.starts_at} to {challenge.ends_at}
                        </div>
                    </CardContent>
                </Card>

                {/* Your Progress */}
                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base">Your Progress</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {userProgress ? (
                            <div className="flex flex-col gap-2">
                                <div className="flex items-center justify-between">
                                    <span className="text-2xl font-bold">{userProgress.percentage}%</span>
                                </div>
                                <Progress value={userProgress.percentage} className="h-3" />
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">You haven't logged any progress yet.</p>
                        )}
                        {challenge.is_active && (
                            <div className="mt-3">
                                <UpdateProgressForm squadId={squad.id} challengeId={challenge.id} />
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Leaderboard */}
                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base flex items-center gap-2">
                            <Trophy className="h-4 w-4 text-amber-500" />
                            Leaderboard
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {leaderboard.length === 0 ? (
                            <p className="text-sm text-muted-foreground">No progress recorded yet.</p>
                        ) : (
                            <div className="flex flex-col gap-3">
                                {leaderboard.map((entry, idx) => (
                                    <div key={idx} className="flex items-center gap-3">
                                        <span className="w-6 text-center text-sm font-bold text-muted-foreground">
                                            {idx === 0 ? '🥇' : idx === 1 ? '🥈' : idx === 2 ? '🥉' : `${idx + 1}.`}
                                        </span>
                                        <div className="flex flex-1 items-center gap-2">
                                            <span className="w-28 truncate text-sm">{entry.user_name}</span>
                                            <Progress value={entry.percentage} className="h-2 flex-1" />
                                            <span className="w-12 text-right text-sm font-medium">{entry.percentage}%</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
