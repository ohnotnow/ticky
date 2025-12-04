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
- **Re-roll Triage**: Re-process existing triage recommendations for a conversation with updated Org Chart context. Accessible via a button in the conversation detail modal.
- **Bulk Re-roll**: Select multiple conversations via checkboxes and re-roll them all at once. Useful after updating team member routing guidance.

### Org Chart Management (`/org-chart`)
- View and manage the organisation structure (Teams, Members, Skills)
- Filter members by name or specific team
- **Steerability**: Edit specific "Routing Guidance" notes for both teams and individual members which are injected into the LLM prompt to guide decisions
  - Team-level guidance (e.g. "Only route here for infrastructure-level changes, not day-to-day user issues")
  - Member-level guidance (e.g. "Only handles physical hardware")
- Skill proficiency tracking (High/Medium/Low) with visual indicators
- Guidance text truncated in table view with full text on hover

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
- `Team` - Departments/Groups within the org (includes `route_guidance`)
- `TeamMember` - Staff members, belongs to Team (includes `route_guidance`)
- `MemberSkill` - Specific skills linked to members (uses `SkillLevel` enum)

### Key Files
- `config/ticky.php` - Default LLM model, model choices, token limits (Org chart moved to DB)
- `App\Services\LlmService` - Provider-agnostic LLM calls, injects dynamic org chart
- `resources/views/prompts/triage.blade.php` - System prompt for triage
- `App\Enums\SkillLevel.php` - Enum for skill levels (Low, Medium, High)

### Livewire Components
- `TriageChat` - Ticket submission with model picker
- `HomePage` - Conversation list with search and detail modal
- `OrgChartPage` - Team/Member management with filtering and editing
- `ApiTokens` - Token management

## Development

### Test Data
```bash
php artisan db:seed --class=TestDataSeeder
```

Seeds an admin user (`admin2x@example.com` / `secret`) along with:

- **Realistic org chart** (5 teams, 32 members) with explicit skill levels
- **Route guidance at team level** demonstrating when to route to each team, e.g.:
  - Infrastructure: "Only for infrastructure-level changes, not day-to-day user issues"
  - Service Delivery: "This is the DEFAULT team for most tickets"
- **Route guidance for every member** demonstrating nuanced routing, e.g.:
  - Callum (networking expert): "WiFi issues and VPN client problems go to Service Delivery"
  - Ewan (AD expert): "Day-to-day password resets go to Service Delivery"
  - Dr Kerr (HPC lead): "Day-to-day job failures go to Rory or Niamh first"
- **12 sample conversations** showing the AI making smart routing decisions:
  - VPN issues → Service Delivery (not the networking expert)
  - Password resets → Service Delivery (not the AD expert)
  - Firewall rules → Infrastructure (correctly routed to specialist)

### Running Tests
```bash
php artisan test
```

## Future Ideas
- Multi-turn chat/follow-up questions
- Batch processing improvements
- Analytics dashboard
- **Local vs Central IT guidance**: Define clearer routing rules for when tickets should go to "our local College IT team" vs "centrally-managed university services" (Central IT team added to seeder as placeholder)
