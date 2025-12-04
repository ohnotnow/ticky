<?php

namespace App\Livewire;

use App\Models\Team;
use App\Models\TeamMember;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrgChartPage extends Component
{
    public $editingMemberId = null;

    public $editingMemberName = '';

    public $editingMemberGuidance = '';

    public $editingTeamId = null;

    public $editingTeamName = '';

    public $editingTeamGuidance = '';

    public $search = '';

    public $selectedTeamId = '';

    public function render()
    {
        $teams = Team::query()
            ->when($this->selectedTeamId, fn ($q) => $q->where('id', $this->selectedTeamId))
            ->with(['members' => function ($query) {
                $query->with('skills');
                if ($this->search) {
                    $query->where('name', 'like', '%'.$this->search.'%');
                }
            }])
            ->get()
            ->filter(fn ($team) => $this->search ? $team->members->isNotEmpty() : true);

        return view('livewire.org-chart-page', [
            'teams' => $teams,
            'teamOptions' => Team::orderBy('name')->get(),
        ]);
    }

    public function editMember(TeamMember $member)
    {
        $this->editingMemberId = $member->id;
        $this->editingMemberName = $member->name;
        $this->editingMemberGuidance = $member->route_guidance;

        Flux::modal('edit-member')->show();
    }

    public function saveMember()
    {
        $this->validate([
            'editingMemberGuidance' => 'nullable|string|max:1000',
        ]);

        $member = TeamMember::findOrFail($this->editingMemberId);

        $member->update([
            'route_guidance' => $this->editingMemberGuidance,
        ]);

        Flux::toast('Member updated successfully.');

        Flux::modal('edit-member')->close();
    }

    public function editTeam(Team $team)
    {
        $this->editingTeamId = $team->id;
        $this->editingTeamName = $team->name;
        $this->editingTeamGuidance = $team->route_guidance;

        Flux::modal('edit-team')->show();
    }

    public function saveTeam()
    {
        $this->validate([
            'editingTeamGuidance' => 'nullable|string|max:1000',
        ]);

        $team = Team::findOrFail($this->editingTeamId);

        $team->update([
            'route_guidance' => $this->editingTeamGuidance,
        ]);

        Flux::toast('Team updated successfully.');

        Flux::modal('edit-team')->close();
    }
}
