import { Head, usePage } from '@inertiajs/react';
import { Copy, Gift, Share2, Users } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

export default function Referral({
    referralCode,
    referralCount,
    premiumDaysEarned,
    referralLink,
}: {
    referralCode: string;
    referralCount: number;
    premiumDaysEarned: number;
    referralLink: string;
}) {
    const [copied, setCopied] = useState(false);

    function copyToClipboard() {
        navigator.clipboard.writeText(referralLink);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    }

    function shareVia(platform: 'whatsapp' | 'facebook') {
        const text = `Join MoneySmart PNG and take control of your finances! Use my referral code: ${referralCode}`;
        const encoded = encodeURIComponent(text + '\n' + referralLink);

        const urls: Record<string, string> = {
            whatsapp: `https://wa.me/?text=${encoded}`,
            facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(referralLink)}&quote=${encodeURIComponent(text)}`,
        };

        window.open(urls[platform], '_blank', 'noopener,noreferrer');
    }

    return (
        <>
            <Head title="Referral program" />

            <h1 className="sr-only">Referral program</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Invite friends"
                    description="Share your referral code and earn premium days"
                />

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Gift className="h-5 w-5 text-amber-600" />
                            Your Referral Code
                        </CardTitle>
                        <CardDescription>
                            When a friend signs up with your code, you earn 30 days of premium access.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex items-center gap-2">
                            <Input
                                value={referralLink}
                                readOnly
                                className="font-mono text-sm"
                            />
                            <Button
                                variant="outline"
                                size="icon"
                                onClick={copyToClipboard}
                                aria-label="Copy referral link"
                            >
                                <Copy className="h-4 w-4" />
                            </Button>
                        </div>

                        {copied && (
                            <p className="text-sm text-green-600">Copied to clipboard!</p>
                        )}

                        <div className="flex flex-wrap gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => shareVia('whatsapp')}
                            >
                                <Share2 className="mr-2 h-4 w-4" />
                                Share on WhatsApp
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => shareVia('facebook')}
                            >
                                <Share2 className="mr-2 h-4 w-4" />
                                Share on Facebook
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid grid-cols-2 gap-4">
                    <Card>
                        <CardContent className="pt-6 text-center">
                            <Users className="mx-auto mb-2 h-6 w-6 text-muted-foreground" />
                            <p className="text-2xl font-bold">{referralCount}</p>
                            <p className="text-sm text-muted-foreground">Friends joined</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6 text-center">
                            <Gift className="mx-auto mb-2 h-6 w-6 text-amber-600" />
                            <p className="text-2xl font-bold">{premiumDaysEarned}</p>
                            <p className="text-sm text-muted-foreground">Premium days earned</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
