<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Component;

class ApiTokens extends Component
{
    public bool $isAdmin = false;

    public bool $showAllTokens = false;

    public string $tokenName = '';

    public ?string $newlyCreatedToken = null;

    public Collection $tokens;

    public bool $showExampleDocs = false;

    public string $exampleTab = 'curl';

    public ?int $tokenToRevoke = null;

    public ?string $tokenToRevokeName = null;

    public bool $showRevokeModal = false;

    public function mount(): void
    {
        $user = Auth::user();

        $this->isAdmin = $user?->isAdmin() ?? false;

        $this->loadTokens();
    }

    public function updatedShowAllTokens(): void
    {
        $this->loadTokens();
    }

    public function createToken(): void
    {
        $this->validate([
            'tokenName' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $token = $user->createToken($this->tokenName);

        $this->newlyCreatedToken = $token->plainTextToken;
        $this->tokenName = '';

        $this->loadTokens();
    }

    public function confirmRevoke(int $tokenId): void
    {
        $token = $this->findTokenForAction($tokenId);

        $this->tokenToRevoke = $token->id;
        $this->tokenToRevokeName = $token->name;
        $this->showRevokeModal = true;
    }

    public function cancelRevoke(): void
    {
        $this->reset(['tokenToRevoke', 'tokenToRevokeName', 'showRevokeModal']);
    }

    public function revokeToken(): void
    {
        if ($this->tokenToRevoke === null) {
            return;
        }

        $token = $this->findTokenForAction($this->tokenToRevoke);

        $token->delete();

        $this->cancelRevoke();

        $this->loadTokens();
    }

    public function render(): View
    {
        return view('livewire.api-tokens');
    }

    protected function loadTokens(): void
    {
        $user = Auth::user();

        $query = PersonalAccessToken::query()->latest();

        if (! $this->isAdmin || ! $this->showAllTokens) {
            $query->where('tokenable_id', $user?->getAuthIdentifier())
                ->where('tokenable_type', $user?->getMorphClass());
        }

        $this->tokens = $query->get();
    }

    protected function findTokenForAction(int $tokenId): PersonalAccessToken
    {
        $token = PersonalAccessToken::query()->findOrFail($tokenId);

        if ($this->isAdmin) {
            return $token;
        }

        $user = Auth::user();

        if (
            $token->tokenable_id !== $user?->getAuthIdentifier()
            || $token->tokenable_type !== $user?->getMorphClass()
        ) {
            abort(403);
        }

        return $token;
    }
}
