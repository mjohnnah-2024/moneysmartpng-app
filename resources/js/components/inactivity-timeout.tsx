import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { logout } from '@/routes';
import { Clock } from 'lucide-react';

const IDLE_TIMEOUT = 30 * 60 * 1000; // 30 minutes
const WARNING_DURATION = 60 * 1000; // 1 minute warning before logout

export function InactivityTimeout() {
    const [showWarning, setShowWarning] = useState(false);
    const [countdown, setCountdown] = useState(60);
    const idleTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
    const countdownTimer = useRef<ReturnType<typeof setInterval> | null>(null);

    const resetTimer = useCallback(() => {
        if (idleTimer.current) {
            clearTimeout(idleTimer.current);
        }
        if (countdownTimer.current) {
            clearInterval(countdownTimer.current);
        }

        setShowWarning(false);
        setCountdown(60);

        idleTimer.current = setTimeout(() => {
            setShowWarning(true);
            setCountdown(60);

            countdownTimer.current = setInterval(() => {
                setCountdown((prev) => {
                    if (prev <= 1) {
                        if (countdownTimer.current) {
                            clearInterval(countdownTimer.current);
                        }
                        router.post(logout.url());
                        return 0;
                    }
                    return prev - 1;
                });
            }, 1000);
        }, IDLE_TIMEOUT);
    }, []);

    const handleStayActive = () => {
        resetTimer();
    };

    useEffect(() => {
        const events = ['mousedown', 'keydown', 'touchstart', 'scroll'] as const;

        const handleActivity = () => {
            if (!showWarning) {
                resetTimer();
            }
        };

        events.forEach((event) => {
            document.addEventListener(event, handleActivity, { passive: true });
        });

        resetTimer();

        return () => {
            events.forEach((event) => {
                document.removeEventListener(event, handleActivity);
            });
            if (idleTimer.current) {
                clearTimeout(idleTimer.current);
            }
            if (countdownTimer.current) {
                clearInterval(countdownTimer.current);
            }
        };
    }, [resetTimer, showWarning]);

    if (!showWarning) {
        return null;
    }

    return (
        <Dialog open={showWarning} onOpenChange={() => {}}>
            <DialogContent className="sm:max-w-md" onPointerDownOutside={(e) => e.preventDefault()}>
                <DialogHeader className="text-center">
                    <div className="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                        <Clock className="h-6 w-6 text-amber-600" />
                    </div>
                    <DialogTitle className="text-center">Session Timeout Warning</DialogTitle>
                    <DialogDescription className="text-center">
                        You've been inactive for 30 minutes. For your security, you'll be logged out in{' '}
                        <span className="font-semibold text-foreground">{countdown} seconds</span>.
                    </DialogDescription>
                </DialogHeader>

                <div className="mt-4 flex justify-center">
                    <Button onClick={handleStayActive} size="lg">
                        Stay Logged In
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
