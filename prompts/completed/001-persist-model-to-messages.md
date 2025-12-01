<objective>
Add a `model` column to the Message model to persist which LLM model was used for each triage response.

This enables audit/history tracking so you can see which model generated each assistant response. The format should match the existing `provider/model` convention used in the app (e.g., `anthropic/claude-sonnet-4-20250514`).
</objective>

<context>
This is a Laravel 12 + Livewire 3 application (Ticky) that triages IT support tickets using an LLM.

Key files to examine:
- @app/Models/Message.php - The Message model that needs the new column
- @app/Models/Conversation.php - Parent model for messages
- @app/Services/LlmService.php - Service that makes LLM calls and knows the model being used
- @config/ticky.php - Config containing model settings
- @app/Livewire/TriageChat.php - Livewire component that creates messages
- @app/Http/Controllers/Api/V1/TriageController.php - API controller that creates messages

Review the existing codebase to understand:
1. How messages are currently created
2. How the model selection is configured and passed around
3. The existing `provider/model` format convention
</context>

<requirements>
1. Create a migration to add a nullable `model` column (string) to the messages table
2. Update the Message model's $fillable array to include 'model'
3. Update all places where Message records are created to include the model value:
   - TriageChat Livewire component (for assistant messages)
   - TriageController API endpoint (for assistant messages)
   - Any other locations that create messages
4. The model value should only be set on assistant messages (user messages don't use an LLM)
5. Format should be `provider/model` string (e.g., `anthropic/claude-sonnet-4-20250514`)
</requirements>

<implementation>
- Follow existing patterns in the codebase for migrations and model updates
- The LlmService or config should already have the model information - reuse that
- Keep it simple - just store the string, no need for a separate models table or enum
- User messages should have null for the model column (they don't use an LLM)
</implementation>

<output>
Files to create/modify:
- `database/migrations/xxxx_xx_xx_xxxxxx_add_model_to_messages_table.php` - New migration
- `app/Models/Message.php` - Add 'model' to $fillable
- `app/Livewire/TriageChat.php` - Pass model when creating assistant messages
- `app/Http/Controllers/Api/V1/TriageController.php` - Pass model when creating assistant messages
- Any other files that create Message records
</output>

<verification>
1. Run the migration: `php artisan migrate`
2. Run existing tests to ensure nothing is broken: `php artisan test`
3. Manually test triage flow - verify the model is saved to new assistant messages
4. Check the database to confirm the model column contains the expected `provider/model` format
</verification>

<success_criteria>
- Migration runs successfully
- New assistant messages have the model column populated with `provider/model` format
- User messages have null for the model column
- All existing tests pass
- No breaking changes to existing functionality
</success_criteria>
