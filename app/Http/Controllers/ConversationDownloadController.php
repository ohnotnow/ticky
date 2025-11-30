<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConversationDownloadController extends Controller
{
    public function json(Conversation $conversation): StreamedResponse
    {
        $this->ensureConversationOwner($conversation);

        $conversation->loadMissing(['messages' => fn ($query) => $query->oldest()]);

        $payload = $this->conversationPayload($conversation);

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $this->fileName($conversation, 'json'), [
            'Content-Type' => 'application/json',
        ]);
    }

    public function markdown(Conversation $conversation): StreamedResponse
    {
        $this->ensureConversationOwner($conversation);

        $conversation->loadMissing(['messages' => fn ($query) => $query->oldest()]);

        $payload = $this->conversationPayload($conversation);
        $content = $this->renderMarkdown($payload);

        return response()->streamDownload(function () use ($content): void {
            echo $content;
        }, $this->fileName($conversation, 'md'), [
            'Content-Type' => 'text/markdown',
        ]);
    }

    private function ensureConversationOwner(Conversation $conversation): void
    {
        abort_if($conversation->user_id !== Auth::id(), 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function conversationPayload(Conversation $conversation): array
    {
        $messages = $conversation->messages;
        $ticketMessage = $messages->first(fn ($message) => $message->isFromUser());
        $assistantMessage = $messages->last(fn ($message) => $message->isFromAssistant());

        return [
            'conversation_id' => $conversation->id,
            'created_at' => $conversation->created_at?->toIso8601String(),
            'ticket' => $ticketMessage?->content,
            'recommendations' => $assistantMessage?->recommendationsForView() ?? [],
            'raw_response' => $assistantMessage?->content,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function renderMarkdown(array $payload): string
    {
        $lines = [
            '# Conversation #'.$payload['conversation_id'].' recommendations',
        ];

        if ($payload['created_at']) {
            $lines[] = '*Created at:* '.$payload['created_at'];
        }

        $lines[] = '';
        $lines[] = '## Ticket';
        $lines[] = $payload['ticket'] ?? 'No ticket message.';
        $lines[] = '';
        $lines[] = '## Recommendations';

        $recommendations = $payload['recommendations'];

        if (count($recommendations) === 0) {
            $lines[] = 'No structured recommendations returned.';

            if ($payload['raw_response']) {
                $lines[] = '';
                $lines[] = '### Raw response';
                $lines[] = '```';
                $lines[] = (string) $payload['raw_response'];
                $lines[] = '```';
            }
        } else {
            foreach ($recommendations as $index => $recommendation) {
                $position = $index + 1;
                $lines[] = $position.'. **Team:** '.($recommendation['team'] ?? 'Unknown');
                $lines[] = '   - Person: '.($recommendation['person'] ?? 'Unspecified');
                $lines[] = '   - Confidence: '.($recommendation['confidence'] ?? 'N/A').'/10';
                $lines[] = '   - Reasoning: '.($recommendation['reasoning'] ?? 'No reasoning provided.');
                $lines[] = '';
            }
        }

        return implode(PHP_EOL, $lines);
    }

    private function fileName(Conversation $conversation, string $extension): string
    {
        return 'conversation-'.$conversation->id.'-recommendations.'.$extension;
    }
}
