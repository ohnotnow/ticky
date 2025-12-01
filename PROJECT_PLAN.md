# Ticky

IT support ticket triage assistant powered by LLMs. Submit tickets and get recommendations for the best team/person to handle them based on your organisation's staff chart.

Built with Laravel 12, Livewire 3, and Flux UI.

## Features

### Ticket Triage (`/triage`)
- Submit one or more support tickets for routing recommendations
- Multiple tickets can be submitted at once (separate with `---` on its own line)
- Each ticket creates an isolated conversation for clean LLM context
- Recommendations include team, person, confidence score (1-10), and reasoning
- Download results as Markdown or JSON

### Model Selection
- Choose which LLM provider/model to use for each submission
- Supported providers configured in `config/ticky.php`
- Model used is recorded against each assistant message for audit tracking
- UI displays the model used alongside each response

### Conversation History (`/`)
- View all your past triage conversations
- Search by message content
- Admins can toggle to view all users' conversations
- Flyout modal shows full conversation with parsed recommendations

### API Access
- Sanctum-protected REST API for programmatic access
- Manage API tokens from the API Keys page
- Admins can view and revoke any user's tokens

## API Endpoints

### `POST /api/v1/triage`

Submit tickets for triage recommendations.

**Request:**
```json
{"tickets": ["Printer is jammed", "VPN is down"]}
```

**Response:**
```json
{
  "data": [
    {
      "conversation_id": 456,
      "ticket": "Printer is jammed",
      "model": "anthropic/claude-sonnet-4-5",
      "recommendations": [
        {"team": "Service Desk", "person": "Alex Smith", "confidence": 9, "reasoning": "Handles print issues"}
      ],
      "raw_response": "{\"recommendations\":[...]}"
    }
  ]
}
```

### `GET /api/v1/conversations`

List your conversations with messages.

**Response:**
```json
{
  "data": [
    {
      "id": 123,
      "created_at": "2024-02-01T12:34:56Z",
      "messages": [
        {"from": "user", "content": "Ticket text", "model": null, "recommendations": [], "created_at": "2024-02-01T12:34:56Z"},
        {"from": "assistant", "content": null, "model": "anthropic/claude-sonnet-4-5", "raw_response": "{\"recommendations\":[...]}", "recommendations": [...], "created_at": "2024-02-01T12:35:10Z"}
      ]
    }
  ]
}
```

## Architecture

### Models
- `User` - Standard auth with admin flag
- `Conversation` - Belongs to user, has many messages
- `Message` - Stores user prompts and assistant responses; assistant messages include `model` field

### Key Files
- `config/ticky.php` - Org chart, default LLM model, model choices, token limits
- `App\Services\LlmService` - Provider-agnostic LLM calls via Prism
- `resources/views/prompts/triage.blade.php` - System prompt for triage

### Livewire Components
- `TriageChat` - Ticket submission with model picker
- `HomePage` - Conversation list with search and detail modal
- `ApiTokens` - Token management

## Development

### Test Data
```bash
php artisan db:seed --class=TestDataSeeder
```

Seeds an admin user (`admin2x@example.com` / `secret`) and realistic sample conversations.

### Running Tests
```bash
php artisan test
```

## Future Ideas
- Multi-turn chat/follow-up questions
- Batch processing improvements
- Analytics dashboard
