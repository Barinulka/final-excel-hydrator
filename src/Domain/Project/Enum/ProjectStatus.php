<?php

namespace App\Domain\Project\Enum;

enum ProjectStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
