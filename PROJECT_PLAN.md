Proof of concept: Laravel 12 + Livewire 3 + Flux UI. Purpose: IT support can submit a ticket to an LLM for routing, using an org/staff chart and a triage prompt to suggest the best team/person.

What’s implemented
- Core models: Conversation, Message; config `config/ticky.php` holds org chart, LLM model, token limits.
- LLM service: `App\Services\LlmService` uses Prism for provider-agnostic calls; triage prompt lives in `resources/views/prompts/triage.blade.php`.
- Triage flow: `/triage` page (Livewire `TriageChat`) uses `flux:composer` to send prompts; now supports multiple tickets in one submission separated by `---`, creating a new conversation per ticket to keep LLM context isolated, storing each prompt/response, and rendering recommendations via shared partial (`resources/views/partials/assistant-recommendations.blade.php`).
- Home/dashboard: `/` (Livewire `HomePage`) lists user conversations with pagination, search filter (message content LIKE), and flyout modal to view full conversation with parsed recommendations.
- UI: Base layout at `resources/views/components/layouts/app.blade.php`, Flux components throughout, modals/pagination wired.
- Test data: `TestDataSeeder` seeds admin and realistic sample conversations + assistant JSON responses based on org chart.
- Tests: Feature coverage for triage/LLM flow and home pagination/filtering; Prism faked in tests.
- API groundwork: Sanctum added; API Keys page UI scaffolded (`App\Livewire\ApiTokens` + `resources/views/livewire/api-tokens.blade.php`), pending wiring to actual token actions and updated triage API docs/examples.

Open next steps / ideas
- Phase 2: true multi-turn chat/history, optional small-model toggle.
- Maybe add sidebar links to home/triage, polish UX, and expose per-page setting if needed.
- PRD: API surface for triage + token management (Sanctum, Livewire)
  - Goals
    - Allow authenticated users (via Sanctum PATs) to triage tickets via HTTP without using the UI.
    - Keep parity with UI shapes so automation users see the same structures they’d expect from the app.
    - Provide an in-app “API Keys” page to create/revoke PATs; admins can view all tokens.
    - Update nav (“Settings” -> “API Keys”) to expose the token page.
  - Endpoints (all `auth:sanctum`)
    - `GET /api/v1/conversations`
      - Returns all conversations for the authenticated user (no pagination/filtering).
      - Response shape:
      ```json
      {
        "data": [
          {
            "id": 123,
            "created_at": "2024-02-01T12:34:56Z",
            "messages": [
              {"from": "user", "content": "Ticket text", "created_at": "2024-02-01T12:34:56Z"},
              {
                "from": "assistant",
                "content": "{\"recommendations\":[...]}",
                "recommendations": [
                  {"team": "Team", "person": "Name", "confidence": 9, "reasoning": "text"}
                ],
                "created_at": "2024-02-01T12:35:10Z"
              }
            ]
          }
        ]
      }
      ```
      - Acceptance: only the owner’s conversations are returned; recommendations field mirrors `Message::recommendationsForView()`; consistent timestamps; 200 status.
    - `POST /api/v1/triage`
      - Request:
      ```json
      {"tickets": ["first ticket", "second ticket"]}
      ```
      - Validation: tickets is a required array of non-empty strings; enforce reasonable max items and max length per ticket; reject otherwise with 422.
      - Behavior: for each ticket create a new conversation, save user message, call `LlmService->generateResponse()` with that conversation’s messages, save assistant message, parse recommendations.
      - Response:
      ```json
      {
        "data": [
          {
            "conversation_id": 456,
            "ticket": "first ticket",
            "recommendations": [
              {"team": "Team", "person": "Name", "confidence": 9, "reasoning": "text"}
            ],
            "raw_response": "{\"recommendations\":[...]}"
          },
          {
            "conversation_id": 457,
            "ticket": "second ticket",
            "recommendations": [],
            "raw_response": "raw llm response if no recs parsed"
          }
        ]
      }
      ```
      - Acceptance: creates one conversation per ticket (no cross-ticket context); saves both user and assistant messages; recommendations array matches UI parsing; raw response returned even if parsing fails; 200 on success.
  - Token management UI (Livewire `ApiTokens`)
    - Properties: `isAdmin` (via `User::isAdmin()`), `showAllTokens` (admins only), `tokenName`, `newlyCreatedToken` (plaintext shown once), `tokens` (collection), `showExampleDocs`, `exampleTab`, `tokenToRevoke`, `tokenToRevokeName`.
    - Actions: `createToken` (validate name, create Sanctum token with all abilities for now, set `newlyCreatedToken`, refresh list), `confirmRevoke` (set id/name), `cancelRevoke` (clear state), `revokeToken` (delete token, clear state, refresh list).
    - Listing: default to current user’s tokens; admins can toggle to see all tokens (owner column visible).
    - Examples: update tabs to show real endpoints (`/api/v1/triage`, `/api/v1/conversations`) with curl/python snippets using Bearer token.
    - Acceptance: token creation shows plaintext once; revocation removes token; admin toggle respects `isAdmin`; non-admins cannot view others’ tokens.
  - Navigation
    - Repurpose unused “Settings” link in `resources/views/components/layouts/app.blade.php` to “API Keys”, pointing to the ApiTokens route/component.
