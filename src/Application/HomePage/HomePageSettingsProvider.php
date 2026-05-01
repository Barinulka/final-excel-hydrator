<?php

declare(strict_types=1);

namespace App\Application\HomePage;

use App\Entity\HomePageSettings;

interface HomePageSettingsProvider
{
    public function getSettings(): ?HomePageSettings;
}
