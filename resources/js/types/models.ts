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
