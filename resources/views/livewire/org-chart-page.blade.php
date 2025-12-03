<div class="p-6 space-y-8 max-w-7xl mx-auto">
    <div>
        <flux:heading size="xl">Organisation Chart</flux:heading>
        <flux:text>Manage teams, members, and routing guidance for the triage assistant.</flux:text>
    </div>

    <div class="flex gap-4">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Filter members..." class="w-full sm:w-64" />

        <flux:select wire:model.live="selectedTeamId" variant="listbox" searchable placeholder="All Teams" clearable class="w-full sm:w-64">
            <flux:select.option value="">All Teams</flux:select.option>
            @foreach($teamOptions as $teamOption)
                <flux:select.option value="{{ $teamOption->id }}">{{ $teamOption->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @foreach($teams as $team)
        <flux:card class="space-y-4">
            <div>
                <flux:heading size="lg">{{ $team->name }}</flux:heading>
                <flux:text>{{ $team->description }}</flux:text>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Skills</flux:table.column>
                    <flux:table.column>Routing Guidance</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($team->members as $member)
                        <flux:table.row wire:key="member-{{ $member->id }}">
                            <flux:table.cell class="whitespace-nowrap font-medium">{{ $member->name }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($member->skills as $skill)
                                        <flux:badge size="sm" :color="$skill->level->color()">{{ $skill->name }} ({{ $skill->level->label() }})</flux:badge>
                                    @endforeach
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($member->route_guidance)
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ $member->route_guidance }}</span>
                                @else
                                    <span class="text-zinc-400 italic">No guidance set</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button wire:click="editMember({{ $member->id }})" size="sm" variant="ghost" icon="pencil-square">Edit</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endforeach

    <flux:modal name="edit-member" class="md:w-96" flyout>
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Team Member</flux:heading>
                <flux:text class="mt-2">Update details and routing guidance for this member.</flux:text>
            </div>

            <flux:input label="Name" value="{{ $editingMemberName }}" disabled class="w-full" />

            <flux:textarea wire:model="editingMemberGuidance" label="Routing Guidance" description="Add specific notes to help the AI understand what this person should or should not handle." rows="4" />

            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="saveMember" variant="primary">Save changes</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
