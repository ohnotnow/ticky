<div class="space-y-3">
    @isset($prompt)
        <flux:callout color="teal" icon="user" heading="Ticket">
            <flux:text class="whitespace-pre-wrap">{{ $prompt }}</flux:text>
        </flux:callout>
    @endisset

    @if (count($recommendations))
        @foreach ($recommendations as $index => $recommendation)
            <flux:callout
                :variant="$index === 0 ? 'success' : 'secondary'"
                :icon="$index === 0 ? 'check-circle' : 'information-circle'"
                :heading="$index === 0 ? 'Primary Recommendation' : 'Recommendation ' . ($index + 1)"
            >
                <div class="space-y-1">
                    <flux:text class="font-semibold">Team: {{ $recommendation['team'] ?? 'Unknown' }}</flux:text>
                    <flux:text>Person: {{ $recommendation['person'] ?? 'Unspecified' }}</flux:text>
                    <flux:text>Confidence: {{ $recommendation['confidence'] ?? 'N/A' }}/10</flux:text>
                    <flux:text>{{ $recommendation['reasoning'] ?? 'No reasoning provided.' }}</flux:text>
                </div>
            </flux:callout>
        @endforeach
    @else
        <flux:card>
            <flux:heading size="md" level="2">LLM response</flux:heading>
            <pre class="mt-3 whitespace-pre-wrap font-mono text-sm">{{ $response ?? '' }}</pre>
        </flux:card>
    @endif
</div>
