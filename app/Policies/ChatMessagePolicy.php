<?php

namespace App\Policies;

use App\Models\ChatMessage;
use App\Models\User;

class ChatMessagePolicy
{
    public function view(User $user, ChatMessage $chatMessage): bool
    {
        return $user->id === $chatMessage->user_id;
    }

    public function delete(User $user, ChatMessage $chatMessage): bool
    {
        return $user->id === $chatMessage->user_id;
    }
}
