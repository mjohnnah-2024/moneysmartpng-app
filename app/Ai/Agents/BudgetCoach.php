<?php

namespace App\Ai\Agents;

use App\Models\ChatMessage;
use App\Models\User;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;

#[Provider(Lab::OpenAI)]
#[Model('gpt-4o-mini')]
#[MaxTokens(1024)]
#[Temperature(0.7)]
class BudgetCoach implements Agent, Conversational
{
    use Promptable;

    public function __construct(public User $user) {}

    public function instructions(): string
    {
        $context = $this->buildFinancialContext();

        $language = $this->user->profile?->preferred_language === 'tpi'
            ? 'IMPORTANT: You MUST respond entirely in Tok Pisin (PNG Pidgin English). Use simple, friendly Tok Pisin throughout. Do not mix in English unless using financial terms like "budget" or "savings goal". Example greetings: "Gutpela dei!", "Orait,".'
            : 'Respond in clear, simple English.';

        $income = $context['monthly_income']
            ? 'K'.number_format($context['monthly_income'], 2)
            : 'not provided';

        return <<<PROMPT
        You are MoneySmart Coach, a friendly AI financial advisor for people in Papua New Guinea.
        You understand the PNG economy, the Kina (K) currency, and everyday financial challenges PNG people face.

        {$language}

        USER FINANCIAL SNAPSHOT (this month):
        - Monthly income: {$income}
        - Total income this month: K{$context['total_income']}
        - Total expenses this month: K{$context['total_expense']}
        - Top spending categories: {$context['top_categories']}
        - Budget status: {$context['budget_summary']}
        - Active savings goals: {$context['goal_summary']}

        GUIDELINES:
        - Give practical, actionable advice for PNG lifestyles (market shopping, PMV transport, betel nut expenses, church offerings, wantok obligations).
        - Use Kina amounts in examples (e.g., "Save K20 per week").
        - Keep responses concise (2-4 paragraphs max).
        - Be encouraging and non-judgmental about spending habits.
        - When relevant, suggest using MoneySmart features (budgets, goals, transaction tracking).
        - If the user asks something unrelated to finance, gently redirect to financial topics.
        - Never give investment advice for specific stocks or crypto.
        PROMPT;
    }

    /**
     * @return Message[]
     */
    public function messages(): iterable
    {
        return $this->user->chatMessages()
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(fn (ChatMessage $msg) => new Message($msg->role, $msg->content))
            ->all();
    }

    /**
     * @return array{monthly_income: float|null, total_expense: float, total_income: float, top_categories: string, budget_summary: string, goal_summary: string}
     */
    private function buildFinancialContext(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $transactions = $this->user->transactions()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $topCategories = $transactions
            ->where('type', 'expense')
            ->groupBy('category')
            ->map(fn ($items) => $items->sum('amount'))
            ->sortDesc()
            ->take(3)
            ->map(fn ($amount, $cat) => "{$cat}: K".number_format($amount, 2))
            ->implode(', ');

        $budgets = $this->user->budgets()
            ->where('month', $startOfMonth)
            ->get();

        $budgetSummary = $budgets->isEmpty()
            ? 'No budgets set'
            : $budgets->map(function ($b) use ($transactions) {
                $spent = $transactions->where('type', 'expense')
                    ->where('category', $b->category)
                    ->sum('amount');
                $pct = $b->amount_limit > 0 ? round(($spent / $b->amount_limit) * 100) : 0;

                return "{$b->category}: K".number_format($spent, 2).' / K'.number_format($b->amount_limit, 2)." ({$pct}%)";
            })->implode('; ');

        $goals = $this->user->goals()
            ->where('status', 'active')
            ->get();

        $goalSummary = $goals->isEmpty()
            ? 'No active goals'
            : $goals->map(function ($g) {
                $pct = $g->target_amount > 0 ? round(($g->current_amount / $g->target_amount) * 100) : 0;

                return "{$g->name}: K".number_format($g->current_amount, 2).' / K'.number_format($g->target_amount, 2)." ({$pct}%)";
            })->implode('; ');

        return [
            'monthly_income' => $this->user->profile?->monthly_income,
            'total_expense' => $transactions->where('type', 'expense')->sum('amount'),
            'total_income' => $transactions->where('type', 'income')->sum('amount'),
            'top_categories' => $topCategories ?: 'None yet',
            'budget_summary' => $budgetSummary,
            'goal_summary' => $goalSummary,
        ];
    }
}
