<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\HomePage\HomePageSettingsProvider;
use App\Entity\HomePageSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomePageSettings>
 */
final class HomePageSettingsRepository extends ServiceEntityRepository implements HomePageSettingsProvider
{
    public const SINGLETON_ID = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomePageSettings::class);
    }

    public function getSettings(): ?HomePageSettings
    {
        return $this->find(self::SINGLETON_ID);
    }
}
