@if (count($recommendations))
    <div class="space-y-3">
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
    </div>
@endif
