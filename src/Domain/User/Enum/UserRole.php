<?php

namespace App\Domain\User\Enum;

enum UserRole: string
{
    case User = 'ROLE_USER';
    case Admin = 'ROLE_ADMIN';
    case SuperAdmin = 'ROLE_SUPER_ADMIN';
}
