<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\HomePageSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HomePageSettingsRepository::class)]
#[ORM\Table(name: 'home_page_settings')]
class HomePageSettings
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Заголовок главной страницы обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $heroTitle = null;

    #[Assert\NotBlank(message: 'Подзаголовок главной страницы обязателен.')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $heroSubtitle = null;

    #[Assert\NotBlank(message: 'Текст кнопки обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $ctaLabel = null;

    #[Assert\NotBlank(message: 'Ссылка кнопки обязательна.')]
    #[ORM\Column(length: 255)]
    private ?string $ctaUrl = null;

    #[Assert\NotBlank(message: 'SEO title обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $seoTitle = null;

    #[Assert\NotBlank(message: 'SEO description обязателен.')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $seoDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ogTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $ogDescription = null;

    public static function createDefault(): self
    {
        return (new self())
            ->setHeroTitle('Добро пожаловать')
            ->setHeroSubtitle("Умная система оценки инвестиционных проектов\nс персональным ИИ-помощником")
            ->setCtaLabel('Начать работу')
            ->setCtaUrl('/models')
            ->setSeoTitle('Добро пожаловать - ИнвестОценка')
            ->setSeoDescription('Умная система оценки инвестиционных проектов с персональным ИИ-помощником.')
            ->setOgTitle('ИнвестОценка')
            ->setOgDescription('Умная система оценки инвестиционных проектов с персональным ИИ-помощником.');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeroTitle(): ?string
    {
        return $this->heroTitle;
    }

    public function setHeroTitle(string $heroTitle): static
    {
        $this->heroTitle = $this->normalizeRequiredString($heroTitle, 'Заголовок главной страницы не может быть пустым.');

        return $this;
    }

    public function getHeroSubtitle(): ?string
    {
        return $this->heroSubtitle;
    }

    public function setHeroSubtitle(string $heroSubtitle): static
    {
        $this->heroSubtitle = $this->normalizeRequiredString($heroSubtitle, 'Подзаголовок главной страницы не может быть пустым.');

        return $this;
    }

    public function getCtaLabel(): ?string
    {
        return $this->ctaLabel;
    }

    public function setCtaLabel(string $ctaLabel): static
    {
        $this->ctaLabel = $this->normalizeRequiredString($ctaLabel, 'Текст кнопки не может быть пустым.');

        return $this;
    }

    public function getCtaUrl(): ?string
    {
        return $this->ctaUrl;
    }

    public function setCtaUrl(string $ctaUrl): static
    {
        $this->ctaUrl = $this->normalizeRequiredString($ctaUrl, 'Ссылка кнопки не может быть пустой.');

        return $this;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function setSeoTitle(string $seoTitle): static
    {
        $this->seoTitle = $this->normalizeRequiredString($seoTitle, 'SEO title не может быть пустым.');

        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(string $seoDescription): static
    {
        $this->seoDescription = $this->normalizeRequiredString($seoDescription, 'SEO description не может быть пустым.');

        return $this;
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function setOgTitle(?string $ogTitle): static
    {
        $this->ogTitle = $this->normalizeNullableString($ogTitle);

        return $this;
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    public function setOgDescription(?string $ogDescription): static
    {
        $this->ogDescription = $this->normalizeNullableString($ogDescription);

        return $this;
    }

    private function normalizeRequiredString(string $value, string $errorMessage): string
    {
        $value = trim($value);

        if ('' === $value) {
            throw new \InvalidArgumentException($errorMessage);
        }

        return $value;
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }
}
