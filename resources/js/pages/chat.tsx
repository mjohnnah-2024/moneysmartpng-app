import { Head } from '@inertiajs/react';
import { useRef, useState, useEffect, useCallback, type FormEvent } from 'react';
import { Send, Trash2, Bot, User, Loader2, AlertCircle, Mic, MicOff } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { UpgradeModal } from '@/components/upgrade-modal';
import type { ChatMessageData, AiUsage } from '@/types';

type Props = {
    messages: ChatMessageData[];
    usage: AiUsage;
};

export default function Chat({ messages: initialMessages, usage }: Props) {
    const [messages, setMessages] = useState<ChatMessageData[]>(initialMessages);
    const [input, setInput] = useState('');
    const [sending, setSending] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [listening, setListening] = useState(false);
    const [showUpgrade, setShowUpgrade] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLTextAreaElement>(null);
    const recognitionRef = useRef<SpeechRecognition | null>(null);

    const isLimitReached = usage.limit !== null && usage.count >= usage.limit;
    const speechSupported = typeof window !== 'undefined' && ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window);

    const scrollToBottom = useCallback(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, []);

    useEffect(() => {
        scrollToBottom();
    }, [messages, scrollToBottom]);

    useEffect(() => {
        if (!sending) {
            inputRef.current?.focus();
        }
    }, [sending]);

    function sendMessage(text: string) {
        const trimmed = text.trim();
        if (!trimmed || sending || isLimitReached) return;

        setError(null);
        setSending(true);

        const optimisticUserMsg: ChatMessageData = {
            id: Date.now(),
            role: 'user',
            content: trimmed,
            created_at: new Date().toISOString(),
        };
        setMessages((prev) => [...prev, optimisticUserMsg]);
        setInput('');

        fetch('/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ message: trimmed }),
        })
            .then(async (res) => {
                if (!res.ok) {
                    const data = await res.json().catch(() => ({}));
                    throw new Error(data.error || data.message || `Request failed (${res.status})`);
                }
                return res.json();
            })
            .then((data) => {
                setMessages((prev) => {
                    const withoutOptimistic = prev.filter((m) => m.id !== optimisticUserMsg.id);
                    return [...withoutOptimistic, data.userMessage, data.assistantMessage];
                });
            })
            .catch((err) => {
                setMessages((prev) => prev.filter((m) => m.id !== optimisticUserMsg.id));
                if (err.message.includes('Upgrade to premium')) {
                    setShowUpgrade(true);
                }
                setError(err.message);
                setInput(trimmed);
            })
            .finally(() => {
                setSending(false);
            });
    }

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        sendMessage(input);
    }

    function handleClear() {
        if (!confirm('Clear all chat history?')) return;

        fetch('/chat', {
            method: 'DELETE',
            headers: {
                'X-XSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        })
            .then(() => setMessages([]))
            .catch(() => setError('Failed to clear chat history.'));
    }

    function handleKeyDown(e: React.KeyboardEvent<HTMLTextAreaElement>) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(input);
        }
    }

    function toggleVoice() {
        if (listening) {
            recognitionRef.current?.stop();
            setListening(false);
            return;
        }

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) return;

        const recognition = new SpeechRecognition();
        recognition.lang = 'en-US';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        recognition.onresult = (event: SpeechRecognitionEvent) => {
            const transcript = event.results[0]?.[0]?.transcript;
            if (transcript) {
                sendMessage(transcript);
            }
        };

        recognition.onerror = () => setListening(false);
        recognition.onend = () => setListening(false);

        recognitionRef.current = recognition;
        recognition.start();
        setListening(true);
    }

    return (
        <>
            <Head title="AI Coach" />
            <div className="flex h-[calc(100vh-4rem)] flex-col md:h-[calc(100vh-2rem)]">
                {/* Header */}
                <div className="flex items-center justify-between border-b border-border px-4 py-3">
                    <div className="flex items-center gap-2">
                        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground">
                            <Bot className="h-4 w-4" />
                        </div>
                        <div>
                            <h1 className="text-sm font-semibold">MoneySmart Coach</h1>
                            <p className="text-xs text-muted-foreground">Your personal finance advisor</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        {usage.limit !== null && (
                            <Badge variant={isLimitReached ? 'destructive' : 'secondary'} className="text-xs">
                                {usage.count}/{usage.limit} messages
                            </Badge>
                        )}
                        {messages.length > 0 && (
                            <Button variant="ghost" size="icon" onClick={handleClear} title="Clear chat">
                                <Trash2 className="h-4 w-4" />
                            </Button>
                        )}
                    </div>
                </div>

                {/* Messages */}
                <div className="flex-1 overflow-y-auto px-4 py-4">
                    {messages.length === 0 && (
                        <div className="flex h-full flex-col items-center justify-center text-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                                <Bot className="h-8 w-8 text-primary" />
                            </div>
                            <h2 className="text-lg font-semibold">MoneySmart Coach</h2>
                            <p className="mt-1 max-w-sm text-sm text-muted-foreground">
                                Ask me anything about managing your money in PNG. I can help with budgeting, saving tips, and financial planning.
                            </p>
                            <div className="mt-6 flex max-w-lg gap-2 overflow-x-auto px-2 pb-2">
                                {SUGGESTED_PROMPTS.map((prompt) => (
                                    <button
                                        key={prompt}
                                        onClick={() => sendMessage(prompt)}
                                        className="shrink-0 rounded-full border border-border px-3 py-1.5 text-xs text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                    >
                                        {prompt}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    <div className="mx-auto max-w-2xl space-y-4">
                        {messages.map((msg) => (
                            <div
                                key={msg.id}
                                className={`flex gap-3 ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}
                            >
                                {msg.role === 'assistant' && (
                                    <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                        <Bot className="h-3.5 w-3.5" />
                                    </div>
                                )}
                                <div
                                    className={`max-w-[80%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed ${
                                        msg.role === 'user'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'bg-muted text-foreground'
                                    }`}
                                >
                                    <MessageContent content={msg.content} />
                                </div>
                                {msg.role === 'user' && (
                                    <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-muted">
                                        <User className="h-3.5 w-3.5 text-muted-foreground" />
                                    </div>
                                )}
                            </div>
                        ))}

                        {sending && (
                            <div className="flex gap-3">
                                <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                    <Bot className="h-3.5 w-3.5" />
                                </div>
                                <div className="rounded-2xl bg-muted px-4 py-2.5">
                                    <div className="flex items-center gap-1.5">
                                        <div className="h-2 w-2 animate-bounce rounded-full bg-muted-foreground/50 [animation-delay:0ms]" />
                                        <div className="h-2 w-2 animate-bounce rounded-full bg-muted-foreground/50 [animation-delay:150ms]" />
                                        <div className="h-2 w-2 animate-bounce rounded-full bg-muted-foreground/50 [animation-delay:300ms]" />
                                    </div>
                                </div>
                            </div>
                        )}

                        <div ref={messagesEndRef} />
                    </div>
                </div>

                {/* Error */}
                {error && (
                    <div className="mx-4 mb-2 flex items-center gap-2 rounded-lg bg-destructive/10 px-3 py-2 text-sm text-destructive">
                        <AlertCircle className="h-4 w-4 shrink-0" />
                        {error}
                    </div>
                )}

                {/* Limit reached banner */}
                {isLimitReached && (
                    <div className="mx-4 mb-2 rounded-lg bg-amber-50 px-3 py-2 text-center text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                        You&apos;ve used all {usage.limit} free messages this month. Upgrade to Premium for unlimited access.
                    </div>
                )}

                {/* Input */}
                <div className="border-t border-border px-4 py-3 safe-bottom">
                    <form onSubmit={handleSubmit} className="mx-auto flex max-w-2xl items-end gap-2">
                        <textarea
                            ref={inputRef}
                            value={input}
                            onChange={(e) => setInput(e.target.value)}
                            onKeyDown={handleKeyDown}
                            placeholder={isLimitReached ? 'Message limit reached' : 'Ask about your finances...'}
                            disabled={sending || isLimitReached}
                            rows={1}
                            className="flex-1 resize-none rounded-xl border border-input bg-background px-4 py-2.5 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:opacity-50"
                            style={{ maxHeight: '120px' }}
                            onInput={(e) => {
                                const el = e.currentTarget;
                                el.style.height = 'auto';
                                el.style.height = Math.min(el.scrollHeight, 120) + 'px';
                            }}
                        />
                        {speechSupported && (
                            <Button
                                type="button"
                                size="icon"
                                variant={listening ? 'destructive' : 'outline'}
                                onClick={toggleVoice}
                                disabled={sending || isLimitReached}
                                className="h-10 w-10 shrink-0 rounded-xl"
                                title={listening ? 'Stop listening' : 'Voice input'}
                            >
                                {listening ? <MicOff className="h-4 w-4" /> : <Mic className="h-4 w-4" />}
                            </Button>
                        )}
                        <Button
                            type="submit"
                            size="icon"
                            disabled={!input.trim() || sending || isLimitReached}
                            className="h-10 w-10 shrink-0 rounded-xl"
                        >
                            {sending ? (
                                <Loader2 className="h-4 w-4 animate-spin" />
                            ) : (
                                <Send className="h-4 w-4" />
                            )}
                        </Button>
                    </form>
                </div>
            </div>

            <UpgradeModal
                open={showUpgrade}
                onClose={() => setShowUpgrade(false)}
                feature="AI messages"
            />
        </>
    );
}

function MessageContent({ content }: { content: string }) {
    const paragraphs = content.split('\n\n').filter(Boolean);
    return (
        <div className="space-y-2">
            {paragraphs.map((p, i) => (
                <p key={i}>
                    {p.split('\n').map((line, j) => (
                        <span key={j}>
                            {j > 0 && <br />}
                            {formatInline(line)}
                        </span>
                    ))}
                </p>
            ))}
        </div>
    );
}

function formatInline(text: string) {
    // Handle bold (**text**) and italic (*text*)
    const parts = text.split(/(\*\*.*?\*\*|\*.*?\*)/g);
    return parts.map((part, i) => {
        if (part.startsWith('**') && part.endsWith('**')) {
            return <strong key={i}>{part.slice(2, -2)}</strong>;
        }
        if (part.startsWith('*') && part.endsWith('*')) {
            return <em key={i}>{part.slice(1, -1)}</em>;
        }
        return part;
    });
}

function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

const SUGGESTED_PROMPTS = [
    'How am I doing this month?',
    'Help me make a budget',
    'How can I save more money?',
    'Tips for reducing expenses',
    'How to reach my savings goal?',
    'Explain wantok money pressure',
    'Best way to track spending?',
];
