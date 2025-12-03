<?php

namespace Database\Factories;

use App\Enums\SkillLevel;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MemberSkill>
 */
class MemberSkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_member_id' => TeamMember::factory(),
            'name' => fake()->word(),
            'level' => fake()->randomElement(SkillLevel::class),
        ];
    }
}
