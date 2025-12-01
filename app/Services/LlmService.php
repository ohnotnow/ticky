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
     * @param  string|null  $providerModel  Specific provider/model in the format provider/model, or null to use the configured default
     * @return array{text: string, model: string}
     */
    public function generateResponse(Conversation $conversation, ?string $systemPrompt = null, bool $useSmallModel = false, ?int $maxTokens = null, ?string $providerModel = null): array
    {
        [$provider, $model] = $this->parseProviderAndModel($providerModel);

        $systemPrompt = $systemPrompt ?? $this->renderChatPrompt($conversation);
        $prismMessages = $conversation->toPrismMessages();

        $maxTokens = $maxTokens ?? config('ticky.max_tokens.default', 100000);

        $response = Prism::text()
            ->using($provider, $model)
            ->withSystemPrompt($systemPrompt)
            ->withMessages($prismMessages)
            ->withMaxTokens($maxTokens)
            ->asText();

        $providerModel = $providerModel ?? config('ticky.llm_model');

        return [
            'text' => $response->text,
            'model' => $providerModel,
        ];
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
    protected function parseProviderAndModel(?string $providerModel = null): array
    {
        $llmConfig = $providerModel ?? config('ticky.llm_model');

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
