<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PublicationStatusEnum: int implements HasLabel, HasColor
{
    case DRAFT = 1;
    case PUBLISH = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISH => 'Published',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'warning',
            self::PUBLISH => 'success',
        };
    }
}
