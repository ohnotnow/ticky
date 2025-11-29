<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $conversations = Conversation::query()
            ->with(['messages' => fn ($query) => $query->oldest()])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return ConversationResource::collection($conversations);
    }
}
