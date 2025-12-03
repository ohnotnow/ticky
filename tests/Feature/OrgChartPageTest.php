<?php

use App\Enums\SkillLevel;
use App\Livewire\OrgChartPage;
use App\Models\MemberSkill;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows teams with members and skills and includes empty teams when not filtering', function (): void {
    $this->actingAs(User::factory()->create());

    $networkTeam = Team::factory()->create([
        'name' => 'Network Operations',
    ]);

    $memberWithGuidance = TeamMember::factory()->for($networkTeam)->create([
        'name' => 'Alice Routing',
        'route_guidance' => 'Handles networking escalations',
    ]);

    MemberSkill::factory()->for($memberWithGuidance, 'teamMember')->create([
        'name' => 'Networking',
        'level' => SkillLevel::High,
    ]);

    $memberWithoutGuidance = TeamMember::factory()->for($networkTeam)->create([
        'name' => 'Bob Builder',
        'route_guidance' => null,
    ]);

    MemberSkill::factory()->for($memberWithoutGuidance, 'teamMember')->create([
        'name' => 'Hardware',
        'level' => SkillLevel::Medium,
    ]);

    $emptyTeam = Team::factory()->create([
        'name' => 'Empty Team',
    ]);

    Livewire::test(OrgChartPage::class)
        ->assertSee('Organisation Chart')
        ->assertSee($networkTeam->name)
        ->assertSee('Networking (High)')
        ->assertSee('Handles networking escalations')
        ->assertSee('Bob Builder')
        ->assertSee('No guidance set')
        ->assertSee($emptyTeam->name)
        ->assertSee('No members');
});

it('filters members by search and selected team and hides empty teams when filtering', function (): void {
    $this->actingAs(User::factory()->create());

    $infrastructureTeam = Team::factory()->create([
        'name' => 'Infrastructure',
    ]);

    TeamMember::factory()->for($infrastructureTeam)->create([
        'name' => 'Alice Network',
    ]);

    $securityTeam = Team::factory()->create([
        'name' => 'Security',
    ]);

    TeamMember::factory()->for($securityTeam)->create([
        'name' => 'Bob Security',
    ]);

    $emptyTeam = Team::factory()->create([
        'name' => 'Empty Team',
    ]);

    Livewire::test(OrgChartPage::class)
        ->set('search', 'Alice')
        ->assertSee($infrastructureTeam->name)
        ->assertSee('Alice Network')
        ->assertDontSee('Bob Security')
        ->assertViewHas('teams', fn ($teams) => $teams->contains(fn ($team) => $team->id === $infrastructureTeam->id) && $teams->doesntContain(fn ($team) => $team->id === $securityTeam->id) && $teams->doesntContain(fn ($team) => $team->id === $emptyTeam->id))
        ->set('search', '')
        ->set('selectedTeamId', $securityTeam->id)
        ->assertSee($securityTeam->name)
        ->assertSee('Bob Security')
        ->assertViewHas('teams', fn ($teams) => $teams->count() === 1 && $teams->first()->id === $securityTeam->id);
});

it('loads and saves routing guidance while dispatching modal and toast events', function (): void {
    $this->actingAs(User::factory()->create());

    $member = TeamMember::factory()->create([
        'name' => 'Charlie Cloud',
        'route_guidance' => 'Old guidance',
    ]);

    Livewire::test(OrgChartPage::class)
        ->call('editMember', $member->id)
        ->assertSet('editingMemberId', $member->id)
        ->assertSet('editingMemberName', $member->name)
        ->assertSet('editingMemberGuidance', 'Old guidance')
        ->assertDispatched('modal-show', name: 'edit-member')
        ->set('editingMemberGuidance', 'Handles cloud escalations only')
        ->call('saveMember')
        ->assertDispatched('toast-show', function (string $event, array $params): bool {
            return ($params['slots']['text'] ?? null) === 'Member updated successfully.';
        })
        ->assertDispatched('modal-close', name: 'edit-member');

    expect($member->refresh()->route_guidance)->toBe('Handles cloud escalations only');
});

it('validates routing guidance length when saving a member', function (): void {
    $this->actingAs(User::factory()->create());

    $member = TeamMember::factory()->create([
        'route_guidance' => 'Existing guidance',
    ]);

    Livewire::test(OrgChartPage::class)
        ->set('editingMemberId', $member->id)
        ->set('editingMemberGuidance', str_repeat('a', 1001))
        ->call('saveMember')
        ->assertHasErrors(['editingMemberGuidance' => 'max']);

    expect($member->refresh()->route_guidance)->toBe('Existing guidance');
});
