<?php

namespace Database\Seeders;

use App\Models\MemberSkill;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class OrgChartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgChart = config('ticky.org_chart');
        $skillLevels = ['low', 'medium', 'high'];

        foreach ($orgChart['teams'] as $teamData) {
            $team = Team::create([
                'name' => $teamData['name'],
                'description' => $teamData['description'],
            ]);

            foreach ($teamData['members'] as $memberData) {
                $member = TeamMember::create([
                    'team_id' => $team->id,
                    'name' => $memberData['name'],
                    // route_guidance is nullable, so no need to set if not present in config
                ]);

                // Split skills string and create MemberSkill records
                $skills = explode(', ', $memberData['skills']);
                foreach ($skills as $skillName) {
                    MemberSkill::create([
                        'team_member_id' => $member->id,
                        'name' => trim($skillName),
                        'level' => $skillLevels[array_rand($skillLevels)], // Assign a random level
                    ]);
                }
            }
        }
    }
}
