<?php

namespace App\Models;

use App\Enums\SkillLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberSkill extends Model
{
    protected $fillable = [
        'team_member_id',
        'name',
        'level',
    ];

    protected function casts(): array
    {
        return [
            'level' => SkillLevel::class,
        ];
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }
}
