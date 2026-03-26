<?php

namespace App\Http\Controllers;

use App\Ai\Agents\BudgetCoach;
use App\Http\Requests\StoreChatMessageRequest;
use App\Models\ChatMessage;
use App\Models\UsageTracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(Request $request): Response
    {
        $messages = $request->user()
            ->chatMessages()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(fn (ChatMessage $msg) => [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'created_at' => $msg->created_at->toISOString(),
            ]);

        $usage = UsageTracking::where('user_id', $request->user()->id)
            ->where('feature', 'ai_chat')
            ->where('period', now()->format('Y-m'))
            ->first();

        $plan = $request->user()->profile?->plan ?? 'free';
        $limit = $plan === 'premium' ? null : 20;

        return Inertia::render('chat', [
            'messages' => $messages,
            'usage' => [
                'count' => $usage?->count ?? 0,
                'limit' => $limit,
            ],
        ]);
    }

    public function store(StoreChatMessageRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Prompt the agent (messages() loads existing history from DB)
        $response = (new BudgetCoach($user))
            ->prompt($validated['message']);

        $tokensUsed = ($response->usage->promptTokens ?? 0)
            + ($response->usage->completionTokens ?? 0);

        // Save user message
        $userMessage = $user->chatMessages()->create([
            'role' => 'user',
            'content' => $validated['message'],
            'tokens_used' => 0,
        ]);

        // Save assistant response
        $assistantMessage = $user->chatMessages()->create([
            'role' => 'assistant',
            'content' => $response->text,
            'tokens_used' => $tokensUsed,
        ]);

        // Track usage
        UsageTracking::updateOrCreate(
            [
                'user_id' => $user->id,
                'feature' => 'ai_chat',
                'period' => now()->format('Y-m'),
            ],
            ['count' => 0],
        )->increment('count');

        return response()->json([
            'userMessage' => [
                'id' => $userMessage->id,
                'role' => 'user',
                'content' => $userMessage->content,
                'created_at' => $userMessage->created_at->toISOString(),
            ],
            'assistantMessage' => [
                'id' => $assistantMessage->id,
                'role' => 'assistant',
                'content' => $response->text,
                'created_at' => $assistantMessage->created_at->toISOString(),
            ],
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $request->user()->chatMessages()->delete();

        return response()->json(['success' => true]);
    }
}
