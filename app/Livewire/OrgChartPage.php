<?php

namespace App\Livewire;

use App\Models\Team;
use App\Models\TeamMember;
use Flux\Flux;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class OrgChartPage extends Component
{
    public $editingMemberId = null;
    public $editingMemberName = '';
    public $editingMemberGuidance = '';

    public function render()
    {
        return view('livewire.org-chart-page', [
            'teams' => Team::with('members.skills')->get(),
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
            'editingMemberName' => 'required|string|max:255',
            'editingMemberGuidance' => 'nullable|string|max:1000',
        ]);

        $member = TeamMember::find($this->editingMemberId);
        
        if ($member) {
            $member->update([
                'name' => $this->editingMemberName,
                'route_guidance' => $this->editingMemberGuidance,
            ]);

            Flux::toast('Member updated successfully.');
        }

        Flux::modal('edit-member')->close();
    }
}
