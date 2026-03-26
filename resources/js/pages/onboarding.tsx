import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/input-error';
import type { User } from '@/types';

type Props = {
    user: User;
};

export default function Onboarding({ user }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        full_name: user.name ?? '',
        phone_number: '',
        preferred_language: 'en',
        monthly_income: '',
        referred_by: '',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/onboarding');
    }

    return (
        <>
            <Head title="Welcome - Set Up Your Profile" />
            <div className="flex min-h-screen items-center justify-center bg-background p-4">
                <Card className="w-full max-w-md">
                    <CardHeader className="text-center">
                        <div className="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-foreground">
                            <span className="text-2xl font-bold">K</span>
                        </div>
                        <CardTitle className="text-2xl">Welcome to MoneySmart PNG!</CardTitle>
                        <CardDescription>
                            Let's set up your profile so we can help you manage your moni better.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                            {/* Full Name */}
                            <div className="space-y-1">
                                <Label htmlFor="full_name">Full Name</Label>
                                <Input
                                    id="full_name"
                                    placeholder="John Kila"
                                    value={data.full_name}
                                    onChange={(e) => setData('full_name', e.target.value)}
                                />
                                <InputError message={errors.full_name} />
                            </div>

                            {/* Phone */}
                            <div className="space-y-1">
                                <Label htmlFor="phone_number">Phone Number (optional)</Label>
                                <Input
                                    id="phone_number"
                                    placeholder="+675 7XXX XXXX"
                                    value={data.phone_number}
                                    onChange={(e) => setData('phone_number', e.target.value)}
                                />
                                <InputError message={errors.phone_number} />
                            </div>

                            {/* Language */}
                            <div className="space-y-1">
                                <Label htmlFor="preferred_language">Preferred Language</Label>
                                <Select value={data.preferred_language} onValueChange={(v) => setData('preferred_language', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="en">English</SelectItem>
                                        <SelectItem value="tpi">Tok Pisin</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.preferred_language} />
                            </div>

                            {/* Monthly Income */}
                            <div className="space-y-1">
                                <Label htmlFor="monthly_income">Monthly Income (Kina, optional)</Label>
                                <Input
                                    id="monthly_income"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="e.g. 1500.00"
                                    value={data.monthly_income}
                                    onChange={(e) => setData('monthly_income', e.target.value)}
                                />
                                <InputError message={errors.monthly_income} />
                            </div>

                            {/* Referral Code */}
                            <div className="space-y-1">
                                <Label htmlFor="referred_by">Referral Code (optional)</Label>
                                <Input
                                    id="referred_by"
                                    placeholder="e.g. ABC123"
                                    maxLength={6}
                                    value={data.referred_by}
                                    onChange={(e) => setData('referred_by', e.target.value.toUpperCase())}
                                />
                                <InputError message={errors.referred_by} />
                            </div>

                            <Button type="submit" disabled={processing} className="mt-2 w-full">
                                {processing ? 'Setting up...' : 'Get Started'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
