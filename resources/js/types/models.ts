export type Profile = {
    id: number;
    user_id: number;
    full_name: string;
    phone_number: string | null;
    preferred_language: 'en' | 'tpi';
    monthly_income: number | null;
    plan: 'free' | 'premium';
    referral_code: string;
    referred_by: string | null;
    premium_days_earned: number;
    created_at: string;
    updated_at: string;
};

export type Transaction = {
    id: number;
    user_id: number;
    amount: number;
    type: 'income' | 'expense';
    category: string;
    description: string | null;
    date: string;
    created_at: string;
    updated_at: string;
};

export type Budget = {
    id: number;
    user_id: number;
    category: string;
    amount_limit: number;
    month: string;
    created_at: string;
    updated_at: string;
};

export type Goal = {
    id: number;
    user_id: number;
    name: string;
    target_amount: number;
    current_amount: number;
    deadline: string | null;
    status: 'active' | 'completed' | 'cancelled';
    created_at: string;
    updated_at: string;
};

export type SpendingCategory = {
    category: string;
    amount: number;
};

export type MonthlySummary = {
    totalIncome: number;
    totalExpense: number;
    remaining: number;
    month: string;
};

export type Subscription = {
    id: number;
    plan: 'free' | 'premium';
    status: 'active' | 'cancelled' | 'expired' | 'pending';
    payment_method: 'stripe' | 'mobile_money' | null;
    stripe_subscription_id: string | null;
    starts_at: string | null;
    ends_at: string | null;
    created_at?: string;
};

export type PricingPlan = {
    months: number;
    price: number;
    label: string;
};

export type PaginatedData<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
};

export type Flash = {
    success: string | null;
    error: string | null;
};

export type ChatMessageData = {
    id: number;
    role: 'user' | 'assistant';
    content: string;
    created_at: string;
};

export type AiUsage = {
    count: number;
    limit: number | null;
};

export type RecurringExpense = {
    id: number;
    name: string;
    amount: number;
    category: string;
    frequency: 'monthly' | 'fortnightly' | 'weekly';
    due_day: number;
    is_paid: boolean;
    last_paid_at: string | null;
};

export type SafeToSpend = {
    daily: number;
    remaining: number;
    percentage: number;
    status: 'green' | 'yellow' | 'red';
    nextBill: {
        name: string;
        amount: number;
        days_until: number;
    } | null;
    breakdown: {
        availableBalance: number;
        pendingBills: number;
        goalContributions: number;
    };
};

export type BillsSummary = {
    totalBills: number;
    totalAmount: number;
    paidCount: number;
    unpaidAmount: number;
};

export type Recommendation = {
    id: string;
    type: 'info' | 'warning' | 'success';
    message: string;
    action?: {
        label: string;
        href: string;
    };
};

export type StreakData = {
    id: number;
    type: string;
    current_count: number;
    longest_count: number;
    last_recorded_at: string | null;
};

export type AchievementData = {
    badge_key: string;
    name: string;
    description: string;
    points: number;
    earned_at: string | null;
};

export type SquadData = {
    id: number;
    name: string;
    description: string | null;
    invite_code: string;
    max_members: number;
    is_active: boolean;
    member_count: number;
    challenge_count: number;
    role: 'admin' | 'member';
};

export type SquadChallengeData = {
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

export type NotificationPreferenceData = {
    id: number;
    type: string;
    in_app: boolean;
    push: boolean;
    enabled: boolean;
};
