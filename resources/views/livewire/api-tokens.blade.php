<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">API Tokens</flux:heading>

        @if($isAdmin)
            <flux:switch wire:model.live="showAllTokens" label="Show all tokens" />
        @endif
    </div>

    {{-- Create Token Form --}}
    <flux:card>
        <flux:heading class="mb-4">Create New Token</flux:heading>
        <form wire:submit="createToken" class="flex gap-4 items-end">
            <flux:input.group>
                <flux:input
                    wire:model="tokenName"
                    placeholder="e.g. My Python Script"
                    class="flex-1"
                />
                <flux:button type="submit" variant="primary">Create Token</flux:button>
            </flux:input.group>
        </form>

        @if($newlyCreatedToken)
            <div class="mt-4 space-y-6">
                <flux:callout variant="warning">
                    Make sure to copy this token - it will only be shown once.
                </flux:callout>

                <flux:input readonly copyable :value="$newlyCreatedToken" />
            </div>
        @endif

    </flux:card>

    {{-- Token List --}}
    <flux:card>
        <div class="flex items-center justify-between mb-4">
            <flux:heading>
                {{ $showAllTokens ? 'All Tokens' : 'Your Tokens' }}
            </flux:heading>

            <flux:button
                wire:click="$toggle('showExampleDocs')"
                variant="ghost"
                size="sm"
                icon="{{ $showExampleDocs ? 'chevron-up' : 'chevron-down' }}"
            >
                {{ $showExampleDocs ? 'Hide' : 'Show' }} API Examples
            </flux:button>
        </div>

        @if($tokens->isEmpty())
            <flux:text>No tokens found.</flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    @if($showAllTokens)
                        <flux:table.column>Owner</flux:table.column>
                    @endif
                    <flux:table.column>Created</flux:table.column>
                    <flux:table.column>Last Used</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($tokens as $token)
                        <flux:table.row wire:key="token-{{ $token->id }}">
                            <flux:table.cell>{{ $token->name }}</flux:table.cell>
                            @if($showAllTokens)
                                <flux:table.cell>{{ $token->tokenable?->email ?? 'Unknown' }}</flux:table.cell>
                            @endif
                            <flux:table.cell>{{ $token->created_at->diffForHumans() }}</flux:table.cell>
                            <flux:table.cell>
                                @if($token->last_used_at)
                                    {{ $token->last_used_at->diffForHumans() }}
                                @else
                                    <flux:text variant="subtle">Never</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button
                                    wire:click="confirmRevoke({{ $token->id }})"
                                    variant="danger"
                                    size="sm"
                                    icon="trash"
                                    title="Revoke Token"
                                >
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- API Examples --}}
    @if($showExampleDocs)
        <flux:card>
            <flux:heading class="mb-4">API Examples</flux:heading>

            <flux:tab.group>
                <flux:tabs wire:model="exampleTab" variant="pills">
                    <flux:tab name="curl">cURL</flux:tab>
                    <flux:tab name="python">Python</flux:tab>
                </flux:tabs>

                <flux:tab.panel name="curl">
                    <div class="space-y-6 mt-4">
                        <div>
                            <flux:text variant="strong" class="block mb-2">Triage tickets</flux:text>
                            <pre class="bg-zinc-950 text-zinc-50 p-4 rounded overflow-x-auto"><code>curl -X POST {{ config('app.url') }}/api/v1/triage \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"tickets": ["Printer is jammed", "VPN is down"]}'</code></pre>
                            <flux:text variant="subtle" class="block mt-2 mb-1">Response:</flux:text>
                            <pre class="bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 p-4 rounded overflow-x-auto text-sm"><code>{
  "data": [
    {
      "conversation_id": 456,
      "ticket": "Printer is jammed",
      "recommendations": [
        {"team": "Service Desk", "person": "Alex Smith", "confidence": 9, "reasoning": "Handles print issues"}
      ],
      "raw_response": "{\"recommendations\":[...]}"
    },
    {
      "conversation_id": 457,
      "ticket": "VPN is down",
      "recommendations": [],
      "raw_response": "Assistant text if no recommendations found"
    }
  ]
}</code></pre>
                        </div>

                        <div>
                            <flux:text variant="strong" class="block mb-2">List your conversations</flux:text>
                            <pre class="bg-zinc-950 text-zinc-50 p-4 rounded overflow-x-auto"><code>curl {{ config('app.url') }}/api/v1/conversations \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>
                            <flux:text variant="subtle" class="block mt-2 mb-1">Response:</flux:text>
                            <pre class="bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 p-4 rounded overflow-x-auto text-sm"><code>{
  "data": [
    {
      "id": 123,
      "created_at": "2024-02-01T12:34:56Z",
      "messages": [
        {"from": "user", "content": "Ticket text", "created_at": "2024-02-01T12:34:56Z"},
        {
          "from": "assistant",
          "content": "{\"recommendations\":[...]}",
          "recommendations": [{"team": "Service Desk", "person": "Alex Smith", "confidence": 9, "reasoning": "Handles print issues"}],
          "created_at": "2024-02-01T12:35:10Z"
        }
      ]
    }
  ]
}</code></pre>
                        </div>
                    </div>
                </flux:tab.panel>

                <flux:tab.panel name="python">
                    <div class="space-y-6 mt-4">
                        <div>
                            <flux:text variant="strong" class="block mb-2">Setup</flux:text>
                            <pre class="bg-zinc-950 text-zinc-50 p-4 rounded overflow-x-auto"><code>import requests

API_URL = "{{ config('app.url') }}/api/v1"
API_TOKEN = "YOUR_API_TOKEN"
HEADERS = {
    "Authorization": f"Bearer {API_TOKEN}",
    "Accept": "application/json",
    "Content-Type": "application/json"
}</code></pre>
                        </div>

                        <div>
                            <flux:text variant="strong" class="block mb-2">Triage tickets</flux:text>
                            <pre class="bg-zinc-950 text-zinc-50 p-4 rounded overflow-x-auto"><code>response = requests.post(
    f"{API_URL}/triage",
    headers=HEADERS,
    json={"tickets": ["Printer is jammed", "VPN is down"]}
)
result = response.json()
print(result)</code></pre>
                            <flux:text variant="subtle" class="block mt-2 mb-1">Output:</flux:text>
                            <pre class="bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 p-4 rounded overflow-x-auto text-sm"><code>{
  'data': [
    {
      'conversation_id': 456,
      'ticket': 'Printer is jammed',
      'recommendations': [
        {'team': 'Service Desk', 'person': 'Alex Smith', 'confidence': 9, 'reasoning': 'Handles print issues'}
      ],
      'raw_response': '{"recommendations":[...]}'
    }
  ]
}</code></pre>
                        </div>

                        <div>
                            <flux:text variant="strong" class="block mb-2">List your conversations</flux:text>
                            <pre class="bg-zinc-950 text-zinc-50 p-4 rounded overflow-x-auto"><code>response = requests.get(f"{API_URL}/conversations", headers=HEADERS)
conversations = response.json()
print(conversations)</code></pre>
                            <flux:text variant="subtle" class="block mt-2 mb-1">Output:</flux:text>
                            <pre class="bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 p-4 rounded overflow-x-auto text-sm"><code>{
  'data': [
    {
      'id': 123,
      'created_at': '2024-02-01T12:34:56Z',
      'messages': [
        {'from': 'user', 'content': 'Ticket text', 'created_at': '2024-02-01T12:34:56Z'},
        {
          'from': 'assistant',
          'content': '{"recommendations":[...]}',
          'recommendations': [{'team': 'Service Desk', 'person': 'Alex Smith', 'confidence': 9, 'reasoning': 'Handles print issues'}],
          'created_at': '2024-02-01T12:35:10Z'
        }
      ]
    }
  ]
}</code></pre>
                        </div>
                    </div>
                </flux:tab.panel>
            </flux:tab.group>
        </flux:card>
    @endif

    <flux:modal
        name="revoke-confirmation"
        wire:model.self="showRevokeModal"
        @close="$wire.cancelRevoke()"
        flyout
        class="md:w-lg"
    >
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Revoke Token</flux:heading>
                <flux:text class="mt-2">Are you sure you want to revoke this token? Any scripts using this token will stop working immediately.</flux:text>
                <p class="mt-2">
                    <flux:text :inline="true" variant="strong">Token: </flux:text> <flux:text :inline="true" variant="strong" color="red">{{ $tokenToRevokeName }}</flux:text>
                </p>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button wire:click="cancelRevoke">Cancel</flux:button>
                <flux:button wire:click="revokeToken" variant="danger">Revoke Token</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
