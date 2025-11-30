<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TriageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\LlmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TriageController extends Controller
{
    public function __construct(private readonly LlmService $llmService) {}

    public function store(TriageRequest $request): JsonResponse
    {
        $tickets = collect($request->validated('tickets'));

        $data = $tickets->map(fn (string $ticket): array => $this->handleTicket($ticket))->values();

        return response()->json([
            'data' => $data,
        ]);
    }

    private function handleTicket(string $ticket): array
    {
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
        ]);

        $conversation->messages()->create([
            'user_id' => Auth::id(),
            'content' => $ticket,
        ]);

        $conversation->load(['messages' => fn ($query) => $query->oldest()]);

        $rawResponse = $this->llmService->generateResponse($conversation);

        $conversation->messages()->create([
            'content' => $rawResponse,
        ]);

        return [
            'conversation_id' => $conversation->id,
            'ticket' => $ticket,
            'recommendations' => Message::recommendationsFromContent($rawResponse),
            'raw_response' => $rawResponse,
        ];
    }
}
