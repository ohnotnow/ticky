<?php

namespace App\Enums;

enum SkillLevel: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function color(): string
    {
        return match ($this) {
            self::High => 'green',
            default => 'zinc',
        };
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}

