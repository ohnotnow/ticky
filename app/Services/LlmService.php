<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

class LlmService
{
    /**
     * Generate a response based on conversation history.
     *
     * @param  string|null  $systemPrompt  Custom system prompt, or null to use default chat prompt
     * @param  bool  $useSmallModel  Whether to use the small/cheap model (default: false)
     * @param  int|null  $maxTokens  Maximum tokens for response (default: config value or 100000)
     */
    public function generateResponse(Conversation $conversation, ?string $systemPrompt = null, bool $useSmallModel = false, ?int $maxTokens = null): string
    {
        [$provider, $model] = $this->parseProviderAndModel();

        $systemPrompt = $systemPrompt ?? $this->renderChatPrompt($conversation);
        $prismMessages = $conversation->toPrismMessages();

        $maxTokens = $maxTokens ?? config('ticky.max_tokens.default', 100000);

        $response = Prism::text()
            ->using($provider, $model)
            ->withSystemPrompt($systemPrompt)
            ->withMessages($prismMessages)
            ->withMaxTokens($maxTokens)
            ->asText();

        return $response->text;
    }

    /**
     * Render the default chat system prompt using the chat Blade template.
     */
    protected function renderChatPrompt(Conversation $conversation): string
    {
        return View::make('prompts.triage', [
            'conversation' => $conversation->load('user'),
            'org_chart' => config('ticky.org_chart'),
        ])->render();
    }

    /**
     * Parse the provider and model from the config using litellm format.
     *
     * @return array{Provider, string}
     *
     * @throws InvalidArgumentException
     */
    protected function parseProviderAndModel(): array
    {
        $llmConfig = config('ticky.llm_model');

        if (! str_contains($llmConfig, '/')) {
            throw new InvalidArgumentException('LLM configuration must be in the format "provider/model" (e.g., "openai/gpt-5.1").');
        }

        [$providerName, $model] = explode('/', $llmConfig, 2);

        $provider = match (strtolower($providerName)) {
            'anthropic' => Provider::Anthropic,
            'openai' => Provider::OpenAI,
            'openrouter' => Provider::OpenRouter,
            default => throw new InvalidArgumentException("Unsupported provider: {$providerName}. Supported providers are: anthropic, openai, openrouter."),
        };

        return [$provider, $model];
    }
}
