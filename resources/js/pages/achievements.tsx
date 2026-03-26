import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Trophy, Lock, Flame } from 'lucide-react';
import type { AchievementData, StreakData } from '@/types';

type Props = {
    badges: AchievementData[];
    totalPoints: number;
    streaks: StreakData[];
};

export default function Achievements({ badges, totalPoints, streaks }: Props) {
    const earnedCount = badges.filter((b) => b.earned_at).length;
    const underBudgetStreak = streaks.find((s) => s.type === 'under_budget');

    return (
        <>
            <Head title="Achievements" />
            <div className="flex flex-col gap-4 p-4">
                <h1 className="text-2xl font-bold">Achievements</h1>

                {/* Stats Row */}
                <div className="grid grid-cols-3 gap-2">
                    <Card>
                        <CardContent className="p-3 text-center">
                            <Trophy className="mx-auto h-5 w-5 text-amber-500" />
                            <p className="mt-1 text-lg font-bold">{totalPoints}</p>
                            <p className="text-xs text-muted-foreground">Total Points</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-3 text-center">
                            <p className="text-lg font-bold">{earnedCount}/{badges.length}</p>
                            <p className="text-xs text-muted-foreground">Badges Earned</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-3 text-center">
                            <Flame className="mx-auto h-5 w-5 text-orange-500" />
                            <p className="mt-1 text-lg font-bold">{underBudgetStreak?.current_count ?? 0}</p>
                            <p className="text-xs text-muted-foreground">Day Streak</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Streaks */}
                {streaks.length > 0 && (
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">Active Streaks</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {streaks.map((streak) => (
                                <div key={streak.type} className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <Flame className="h-4 w-4 text-orange-500" />
                                        <span className="text-sm capitalize">{streak.type.replace(/_/g, ' ')}</span>
                                    </div>
                                    <div className="text-right">
                                        <span className="text-sm font-bold">{streak.current_count} days</span>
                                        <span className="ml-2 text-xs text-muted-foreground">Best: {streak.longest_count}</span>
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                )}

                {/* Badge Grid */}
                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base">Badges</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            {badges.map((badge) => {
                                const isEarned = !!badge.earned_at;
                                return (
                                    <div
                                        key={badge.badge_key}
                                        className={`rounded-lg border p-3 text-center transition-colors ${
                                            isEarned ? 'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20' : 'opacity-50'
                                        }`}
                                    >
                                        <div className="mx-auto mb-1">
                                            {isEarned ? (
                                                <Trophy className="mx-auto h-8 w-8 text-amber-500" />
                                            ) : (
                                                <Lock className="mx-auto h-8 w-8 text-muted-foreground" />
                                            )}
                                        </div>
                                        <p className="text-xs font-semibold">{badge.name}</p>
                                        <p className="mt-0.5 text-[10px] text-muted-foreground">{badge.description}</p>
                                        <p className="mt-1 text-xs font-medium text-amber-600">{badge.points} pts</p>
                                        {badge.earned_at && (
                                            <p className="mt-0.5 text-[10px] text-muted-foreground">
                                                {new Date(badge.earned_at).toLocaleDateString()}
                                            </p>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
