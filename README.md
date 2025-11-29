
Proof of concept: Laravel 12 + Livewire 3 + Flux UI. Purpose: IT support can submit a ticket to an LLM for routing, using an org/staff chart and a triage prompt to suggest the best team/person.

What’s implemented
- Core models: Conversation, Message; config `config/ticky.php` holds org chart, LLM model, token limits.
- LLM service: `App\Services\LlmService` uses Prism for provider-agnostic calls; triage prompt lives in `resources/views/prompts/triage.blade.php`.
- Triage flow: `/triage` page (Livewire `TriageChat`) uses `flux:composer` to send a prompt, stores messages, calls LLM, and renders recommendations via shared partial (`resources/views/partials/assistant-recommendations.blade.php`).
- Home/dashboard: `/` (Livewire `HomePage`) lists user conversations with pagination, search filter (message content LIKE), and flyout modal to view full conversation with parsed recommendations.
- UI: Base layout at `resources/views/components/layouts/app.blade.php`, Flux components throughout, modals/pagination wired.
- Test data: `TestDataSeeder` seeds admin and realistic sample conversations + assistant JSON responses based on org chart.
- Tests: Feature coverage for triage/LLM flow and home pagination/filtering; Prism faked in tests.

Open next steps / ideas
- Phase 2: true multi-turn chat/history, optional small-model toggle.
- Maybe add sidebar links to home/triage, polish UX, and expose per-page setting if needed.
