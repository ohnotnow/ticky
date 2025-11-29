Proof of concept: Laravel 12 + Livewire 3 + Flux UI. Purpose: IT support can submit a ticket to an LLM for routing, using an org/staff chart and a triage prompt to suggest the best team/person.

What’s implemented
- Core models: Conversation, Message; config `config/ticky.php` holds org chart, LLM model, token limits.
- LLM service: `App\Services\LlmService` uses Prism for provider-agnostic calls; triage prompt lives in `resources/views/prompts/triage.blade.php`.
- Triage flow: `/triage` page (Livewire `TriageChat`) uses `flux:composer` to send prompts; now supports multiple tickets in one submission separated by `---`, creating a new conversation per ticket to keep LLM context isolated, storing each prompt/response, and rendering recommendations via shared partial (`resources/views/partials/assistant-recommendations.blade.php`).
- Home/dashboard: `/` (Livewire `HomePage`) lists user conversations with pagination, search filter (message content LIKE), and flyout modal to view full conversation with parsed recommendations.
- UI: Base layout at `resources/views/components/layouts/app.blade.php`, Flux components throughout, modals/pagination wired.
- Test data: `TestDataSeeder` seeds admin and realistic sample conversations + assistant JSON responses based on org chart.
- Tests: Feature coverage for triage/LLM flow and home pagination/filtering; Prism faked in tests.
- API and tokens: Sanctum added and wired; API Keys page (Livewire `ApiTokens`) now creates/revokes tokens, admin can view all, revoke modal works, examples updated to real triage endpoints. Sidebar “Settings” link repurposed to “API Keys”.
- API endpoints live (Sanctum-protected):
  - `POST /api/v1/triage`
    - Request:
    ```json
    {"tickets": ["Printer is jammed", "VPN is down"]}
    ```
    - Response:
    ```json
    {
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
    }
    ```
    - Behavior: one new conversation per ticket; saves user + assistant messages; uses existing LlmService; recommendations parsed like UI; raw_response always returned.
    - Validation: `tickets` required array of non-empty strings; max items/length enforced.
  - `GET /api/v1/conversations`
    - Response:
    ```json
    {
      "data": [
        {
          "id": 123,
          "created_at": "2024-02-01T12:34:56Z",
          "messages": [
            {"from": "user", "content": "Ticket text", "raw_response": null, "recommendations": [], "created_at": "2024-02-01T12:34:56Z"},
            {"from": "assistant", "content": null, "raw_response": "{\"recommendations\":[...]}", "recommendations": [{"team": "Service Desk", "person": "Alex Smith", "confidence": 9, "reasoning": "Handles print issues"}], "created_at": "2024-02-01T12:35:10Z"}
          ]
        }
      ]
    }
    ```
    - Behavior: returns only the authenticated user’s conversations; messages eager-loaded, ordered oldest; message shape mirrors UI parsing; assistant messages use `raw_response`, user messages use `content`.

Open next steps / ideas
- Phase 2: true multi-turn chat/history, optional small-model toggle.
- Maybe add sidebar links to home/triage, polish UX, and expose per-page setting if needed.
