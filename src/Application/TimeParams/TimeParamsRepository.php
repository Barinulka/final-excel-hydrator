<?php

namespace App\Application\TimeParams;

use App\Entity\TimeParams;

interface TimeParamsRepository
{
    public function save(TimeParams $timeParams): void;
}
