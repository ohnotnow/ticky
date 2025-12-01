<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Message extends Model
{
    /** @use HasFactory<\Database\Factories\MessageFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'content',
        'model',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFromUser(): bool
    {
        return $this->user_id !== null;
    }

    public function isFromAssistant(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Extract recommendations from the message content for display.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recommendationsForView(): array
    {
        return self::recommendationsFromContent($this->content);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function recommendationsFromContent(?string $content): array
    {
        if (! $content) {
            return [];
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded) || ! isset($decoded['recommendations']) || ! is_array($decoded['recommendations'])) {
            return [];
        }

        return Collection::make($decoded['recommendations'])
            ->filter(fn ($item) => is_array($item))
            ->sortByDesc('confidence')
            ->values()
            ->all();
    }
}
