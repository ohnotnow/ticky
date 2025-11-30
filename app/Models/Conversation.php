<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

class Conversation extends Model
{
    /** @use HasFactory<\Database\Factories\ConversationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

        /**
     * Convert conversation messages to Prism message format ready for sending to an LLM.
     *
     * @return array<int, UserMessage|AssistantMessage>
     */
    public function toPrismMessages(): array
    {
        return $this->messages->map(function ($message) {
            if ($message->isFromUser()) {
                return new UserMessage($message->content);
            }

            return new AssistantMessage($message->content);
        })->toArray();
    }

}
