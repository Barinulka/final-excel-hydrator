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

    #[Assert\NotBlank(message: 'Название бренда обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $brandText = null;

    #[Assert\NotBlank(message: 'Текст первого пункта меню обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $navFeaturesLabel = null;

    #[Assert\NotBlank(message: 'Ссылка первого пункта меню обязательна.')]
    #[ORM\Column(length: 255)]
    private ?string $navFeaturesUrl = null;

    #[Assert\NotBlank(message: 'Текст второго пункта меню обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $navTemplatesLabel = null;

    #[Assert\NotBlank(message: 'Ссылка второго пункта меню обязательна.')]
    #[ORM\Column(length: 255)]
    private ?string $navTemplatesUrl = null;

    #[Assert\NotBlank(message: 'Текст третьего пункта меню обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $navInvestorLabel = null;

    #[Assert\NotBlank(message: 'Ссылка третьего пункта меню обязательна.')]
    #[ORM\Column(length: 255)]
    private ?string $navInvestorUrl = null;

    #[Assert\NotBlank(message: 'Надзаголовок главной страницы обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $eyebrowText = null;

    #[Assert\NotBlank(message: 'Заголовок главной страницы обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $heroTitle = null;

    #[Assert\NotBlank(message: 'Подзаголовок главной страницы обязателен.')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $heroSubtitle = null;

    #[Assert\NotBlank(message: 'Текст основной кнопки обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $ctaLabel = null;

    #[Assert\NotBlank(message: 'Ссылка основной кнопки обязательна.')]
    #[ORM\Column(length: 255)]
    private ?string $ctaUrl = null;

    #[Assert\NotBlank(message: 'Текст вторичной кнопки обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $secondaryCtaLabel = null;

    #[Assert\NotBlank(message: 'Ссылка вторичной кнопки обязательна.')]
    #[ORM\Column(length: 255)]
    private ?string $secondaryCtaUrl = null;

    #[Assert\NotBlank(message: 'Список тегов доверия обязателен.')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $trustTagsText = null;

    #[Assert\NotBlank(message: 'Надзаголовок preview обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $previewKicker = null;

    #[Assert\NotBlank(message: 'Заголовок preview обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $previewTitle = null;

    #[Assert\NotBlank(message: 'Статус preview обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $previewStatus = null;

    #[Assert\NotBlank(message: 'Название первой метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric1Label = null;

    #[Assert\NotBlank(message: 'Значение первой метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric1Value = null;

    #[Assert\NotBlank(message: 'Описание первой метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric1Note = null;

    #[Assert\NotBlank(message: 'Название второй метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric2Label = null;

    #[Assert\NotBlank(message: 'Значение второй метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric2Value = null;

    #[Assert\NotBlank(message: 'Описание второй метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric2Note = null;

    #[Assert\NotBlank(message: 'Название третьей метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric3Label = null;

    #[Assert\NotBlank(message: 'Значение третьей метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric3Value = null;

    #[Assert\NotBlank(message: 'Описание третьей метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric3Note = null;

    #[Assert\NotBlank(message: 'Название четвертой метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric4Label = null;

    #[Assert\NotBlank(message: 'Значение четвертой метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric4Value = null;

    #[Assert\NotBlank(message: 'Описание четвертой метрики обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $previewMetric4Note = null;

    #[Assert\NotBlank(message: 'Заголовок графика обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $chartTitle = null;

    #[Assert\NotBlank(message: 'Период графика обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $chartPeriod = null;

    #[Assert\NotBlank(message: 'AI-подсказка обязательна.')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $assistantNote = null;

    #[Assert\NotBlank(message: 'Надзаголовок popup обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $popupKicker = null;

    #[Assert\NotBlank(message: 'Заголовок popup обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $popupTitle = null;

    #[Assert\NotBlank(message: 'Описание popup обязательно.')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $popupDescription = null;

    #[Assert\NotBlank(message: 'Заголовок первого шага popup обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $popupStep1Title = null;

    #[Assert\NotBlank(message: 'Описание первого шага popup обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $popupStep1Description = null;

    #[Assert\NotBlank(message: 'Заголовок второго шага popup обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $popupStep2Title = null;

    #[Assert\NotBlank(message: 'Описание второго шага popup обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $popupStep2Description = null;

    #[Assert\NotBlank(message: 'Заголовок третьего шага popup обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $popupStep3Title = null;

    #[Assert\NotBlank(message: 'Описание третьего шага popup обязательно.')]
    #[ORM\Column(length: 255)]
    private ?string $popupStep3Description = null;

    #[Assert\NotBlank(message: 'Текст основной кнопки popup обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $popupPrimaryCtaLabel = null;

    #[Assert\NotBlank(message: 'Ссылка основной кнопки popup обязательна.')]
    #[ORM\Column(length: 255)]
    private ?string $popupPrimaryCtaUrl = null;

    #[Assert\NotBlank(message: 'Текст вторичной кнопки popup обязателен.')]
    #[ORM\Column(length: 255)]
    private ?string $popupSecondaryCtaLabel = null;

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
            ->setBrandText('ИнвестОценка')
            ->setNavFeaturesLabel('Возможности')
            ->setNavFeaturesUrl('#features')
            ->setNavTemplatesLabel('Шаблоны')
            ->setNavTemplatesUrl('#templates')
            ->setNavInvestorLabel('Для инвестора')
            ->setNavInvestorUrl('#investor')
            ->setEyebrowText('Рабочее пространство для финансовых решений')
            ->setHeroTitle('Создайте финансовую модель за несколько минут')
            ->setHeroSubtitle('Соберите прогноз, проверьте окупаемость и подготовьте проект к разговору с партнерами, банком или инвестором.')
            ->setCtaLabel('Начать работу')
            ->setCtaUrl('/models')
            ->setSecondaryCtaLabel('Посмотреть пример')
            ->setSecondaryCtaUrl('#example')
            ->setTrustTagsText("NPV\nIRR\nUnit-экономика\nСценарии")
            ->setPreviewKicker('Новая модель')
            ->setPreviewTitle('Кофейня у бизнес-центра')
            ->setPreviewStatus('Готово к оценке')
            ->setPreviewMetric1Label('NPV')
            ->setPreviewMetric1Value('₽ 18,4 млн')
            ->setPreviewMetric1Note('базовый сценарий')
            ->setPreviewMetric2Label('IRR')
            ->setPreviewMetric2Value('27%')
            ->setPreviewMetric2Note('выше ставки капитала')
            ->setPreviewMetric3Label('Окупаемость')
            ->setPreviewMetric3Value('3,2 года')
            ->setPreviewMetric3Note('с учетом CAPEX')
            ->setPreviewMetric4Label('EBITDA')
            ->setPreviewMetric4Value('21%')
            ->setPreviewMetric4Note('на 3-й год')
            ->setChartTitle('Прогноз денежного потока')
            ->setChartPeriod('2026-2030')
            ->setAssistantNote('Маржинальность чувствительна к аренде. Проверьте сценарий +12% к фиксированным расходам.')
            ->setPopupKicker('Добро пожаловать')
            ->setPopupTitle('Начнем с первой финансовой модели')
            ->setPopupDescription('У вас пока нет созданных моделей. Поможем собрать структуру проекта, рассчитать ключевые показатели и подготовить понятную оценку для принятия решения.')
            ->setPopupStep1Title('Опишите проект')
            ->setPopupStep1Description('сфера, формат, стартовые вложения')
            ->setPopupStep2Title('Проверьте сценарии')
            ->setPopupStep2Description('выручка, расходы, чувствительность')
            ->setPopupStep3Title('Получите вывод')
            ->setPopupStep3Description('NPV, IRR, окупаемость и риски')
            ->setPopupPrimaryCtaLabel('Создать первую модель')
            ->setPopupPrimaryCtaUrl('/models')
            ->setPopupSecondaryCtaLabel('Остаться на главном экране')
            ->setSeoTitle('ИнвестОценка - финансовые модели для бизнеса')
            ->setSeoDescription('Сервис для создания финансовых моделей, оценки окупаемости и подготовки инвестиционных решений.')
            ->setOgTitle('ИнвестОценка')
            ->setOgDescription('Создайте финансовую модель, проверьте окупаемость и подготовьте проект к разговору с инвестором.');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrandText(): ?string
    {
        return $this->brandText;
    }

    public function setBrandText(string $brandText): static
    {
        $this->brandText = $this->normalizeRequiredString($brandText, 'Название бренда не может быть пустым.');

        return $this;
    }

    public function getNavFeaturesLabel(): ?string
    {
        return $this->navFeaturesLabel;
    }

    public function setNavFeaturesLabel(string $navFeaturesLabel): static
    {
        $this->navFeaturesLabel = $this->normalizeRequiredString($navFeaturesLabel, 'Текст первого пункта меню не может быть пустым.');

        return $this;
    }

    public function getNavFeaturesUrl(): ?string
    {
        return $this->navFeaturesUrl;
    }

    public function setNavFeaturesUrl(string $navFeaturesUrl): static
    {
        $this->navFeaturesUrl = $this->normalizeRequiredString($navFeaturesUrl, 'Ссылка первого пункта меню не может быть пустой.');

        return $this;
    }

    public function getNavTemplatesLabel(): ?string
    {
        return $this->navTemplatesLabel;
    }

    public function setNavTemplatesLabel(string $navTemplatesLabel): static
    {
        $this->navTemplatesLabel = $this->normalizeRequiredString($navTemplatesLabel, 'Текст второго пункта меню не может быть пустым.');

        return $this;
    }

    public function getNavTemplatesUrl(): ?string
    {
        return $this->navTemplatesUrl;
    }

    public function setNavTemplatesUrl(string $navTemplatesUrl): static
    {
        $this->navTemplatesUrl = $this->normalizeRequiredString($navTemplatesUrl, 'Ссылка второго пункта меню не может быть пустой.');

        return $this;
    }

    public function getNavInvestorLabel(): ?string
    {
        return $this->navInvestorLabel;
    }

    public function setNavInvestorLabel(string $navInvestorLabel): static
    {
        $this->navInvestorLabel = $this->normalizeRequiredString($navInvestorLabel, 'Текст третьего пункта меню не может быть пустым.');

        return $this;
    }

    public function getNavInvestorUrl(): ?string
    {
        return $this->navInvestorUrl;
    }

    public function setNavInvestorUrl(string $navInvestorUrl): static
    {
        $this->navInvestorUrl = $this->normalizeRequiredString($navInvestorUrl, 'Ссылка третьего пункта меню не может быть пустой.');

        return $this;
    }

    public function getEyebrowText(): ?string
    {
        return $this->eyebrowText;
    }

    public function setEyebrowText(string $eyebrowText): static
    {
        $this->eyebrowText = $this->normalizeRequiredString($eyebrowText, 'Надзаголовок не может быть пустым.');

        return $this;
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
        $this->ctaLabel = $this->normalizeRequiredString($ctaLabel, 'Текст основной кнопки не может быть пустым.');

        return $this;
    }

    public function getCtaUrl(): ?string
    {
        return $this->ctaUrl;
    }

    public function setCtaUrl(string $ctaUrl): static
    {
        $this->ctaUrl = $this->normalizeRequiredString($ctaUrl, 'Ссылка основной кнопки не может быть пустой.');

        return $this;
    }

    public function getSecondaryCtaLabel(): ?string
    {
        return $this->secondaryCtaLabel;
    }

    public function setSecondaryCtaLabel(string $secondaryCtaLabel): static
    {
        $this->secondaryCtaLabel = $this->normalizeRequiredString($secondaryCtaLabel, 'Текст вторичной кнопки не может быть пустым.');

        return $this;
    }

    public function getSecondaryCtaUrl(): ?string
    {
        return $this->secondaryCtaUrl;
    }

    public function setSecondaryCtaUrl(string $secondaryCtaUrl): static
    {
        $this->secondaryCtaUrl = $this->normalizeRequiredString($secondaryCtaUrl, 'Ссылка вторичной кнопки не может быть пустой.');

        return $this;
    }

    public function getTrustTagsText(): ?string
    {
        return $this->trustTagsText;
    }

    public function setTrustTagsText(string $trustTagsText): static
    {
        $this->trustTagsText = $this->normalizeRequiredString($trustTagsText, 'Список тегов доверия не может быть пустым.');

        return $this;
    }

    public function getPreviewKicker(): ?string
    {
        return $this->previewKicker;
    }

    public function setPreviewKicker(string $previewKicker): static
    {
        $this->previewKicker = $this->normalizeRequiredString($previewKicker, 'Надзаголовок preview не может быть пустым.');

        return $this;
    }

    public function getPreviewTitle(): ?string
    {
        return $this->previewTitle;
    }

    public function setPreviewTitle(string $previewTitle): static
    {
        $this->previewTitle = $this->normalizeRequiredString($previewTitle, 'Заголовок preview не может быть пустым.');

        return $this;
    }

    public function getPreviewStatus(): ?string
    {
        return $this->previewStatus;
    }

    public function setPreviewStatus(string $previewStatus): static
    {
        $this->previewStatus = $this->normalizeRequiredString($previewStatus, 'Статус preview не может быть пустым.');

        return $this;
    }

    public function getPreviewMetric1Label(): ?string { return $this->previewMetric1Label; }
    public function setPreviewMetric1Label(string $value): static { $this->previewMetric1Label = $this->normalizeRequiredString($value, 'Название первой метрики не может быть пустым.'); return $this; }
    public function getPreviewMetric1Value(): ?string { return $this->previewMetric1Value; }
    public function setPreviewMetric1Value(string $value): static { $this->previewMetric1Value = $this->normalizeRequiredString($value, 'Значение первой метрики не может быть пустым.'); return $this; }
    public function getPreviewMetric1Note(): ?string { return $this->previewMetric1Note; }
    public function setPreviewMetric1Note(string $value): static { $this->previewMetric1Note = $this->normalizeRequiredString($value, 'Описание первой метрики не может быть пустым.'); return $this; }
    public function getPreviewMetric2Label(): ?string { return $this->previewMetric2Label; }
    public function setPreviewMetric2Label(string $value): static { $this->previewMetric2Label = $this->normalizeRequiredString($value, 'Название второй метрики не может быть пустым.'); return $this; }
    public function getPreviewMetric2Value(): ?string { return $this->previewMetric2Value; }
    public function setPreviewMetric2Value(string $value): static { $this->previewMetric2Value = $this->normalizeRequiredString($value, 'Значение второй метрики не может быть пустым.'); return $this; }
    public function getPreviewMetric2Note(): ?string { return $this->previewMetric2Note; }
    public function setPreviewMetric2Note(string $value): static { $this->previewMetric2Note = $this->normalizeRequiredString($value, 'Описание второй метрики не может быть пустым.'); return $this; }
    public function getPreviewMetric3Label(): ?string { return $this->previewMetric3Label; }
    public function setPreviewMetric3Label(string $value): static { $this->previewMetric3Label = $this->normalizeRequiredString($value, 'Название третьей метрики не может быть пустым.'); return $this; }
    public function getPreviewMetric3Value(): ?string { return $this->previewMetric3Value; }
    public function setPreviewMetric3Value(string $value): static { $this->previewMetric3Value = $this->normalizeRequiredString($value, 'Значение третьей метрики не может быть пустым.'); return $this; }
    public function getPreviewMetric3Note(): ?string { return $this->previewMetric3Note; }
    public function setPreviewMetric3Note(string $value): static { $this->previewMetric3Note = $this->normalizeRequiredString($value, 'Описание третьей метрики не может быть пустым.'); return $this; }
    public function getPreviewMetric4Label(): ?string { return $this->previewMetric4Label; }
    public function setPreviewMetric4Label(string $value): static { $this->previewMetric4Label = $this->normalizeRequiredString($value, 'Название четвертой метрики не может быть пустым.'); return $this; }
    public function getPreviewMetric4Value(): ?string { return $this->previewMetric4Value; }
    public function setPreviewMetric4Value(string $value): static { $this->previewMetric4Value = $this->normalizeRequiredString($value, 'Значение четвертой метрики не может быть пустым.'); return $this; }
    public function getPreviewMetric4Note(): ?string { return $this->previewMetric4Note; }
    public function setPreviewMetric4Note(string $value): static { $this->previewMetric4Note = $this->normalizeRequiredString($value, 'Описание четвертой метрики не может быть пустым.'); return $this; }

    public function getChartTitle(): ?string { return $this->chartTitle; }
    public function setChartTitle(string $value): static { $this->chartTitle = $this->normalizeRequiredString($value, 'Заголовок графика не может быть пустым.'); return $this; }
    public function getChartPeriod(): ?string { return $this->chartPeriod; }
    public function setChartPeriod(string $value): static { $this->chartPeriod = $this->normalizeRequiredString($value, 'Период графика не может быть пустым.'); return $this; }
    public function getAssistantNote(): ?string { return $this->assistantNote; }
    public function setAssistantNote(string $value): static { $this->assistantNote = $this->normalizeRequiredString($value, 'AI-подсказка не может быть пустой.'); return $this; }

    public function getPopupKicker(): ?string { return $this->popupKicker; }
    public function setPopupKicker(string $value): static { $this->popupKicker = $this->normalizeRequiredString($value, 'Надзаголовок popup не может быть пустым.'); return $this; }
    public function getPopupTitle(): ?string { return $this->popupTitle; }
    public function setPopupTitle(string $value): static { $this->popupTitle = $this->normalizeRequiredString($value, 'Заголовок popup не может быть пустым.'); return $this; }
    public function getPopupDescription(): ?string { return $this->popupDescription; }
    public function setPopupDescription(string $value): static { $this->popupDescription = $this->normalizeRequiredString($value, 'Описание popup не может быть пустым.'); return $this; }
    public function getPopupStep1Title(): ?string { return $this->popupStep1Title; }
    public function setPopupStep1Title(string $value): static { $this->popupStep1Title = $this->normalizeRequiredString($value, 'Заголовок первого шага popup не может быть пустым.'); return $this; }
    public function getPopupStep1Description(): ?string { return $this->popupStep1Description; }
    public function setPopupStep1Description(string $value): static { $this->popupStep1Description = $this->normalizeRequiredString($value, 'Описание первого шага popup не может быть пустым.'); return $this; }
    public function getPopupStep2Title(): ?string { return $this->popupStep2Title; }
    public function setPopupStep2Title(string $value): static { $this->popupStep2Title = $this->normalizeRequiredString($value, 'Заголовок второго шага popup не может быть пустым.'); return $this; }
    public function getPopupStep2Description(): ?string { return $this->popupStep2Description; }
    public function setPopupStep2Description(string $value): static { $this->popupStep2Description = $this->normalizeRequiredString($value, 'Описание второго шага popup не может быть пустым.'); return $this; }
    public function getPopupStep3Title(): ?string { return $this->popupStep3Title; }
    public function setPopupStep3Title(string $value): static { $this->popupStep3Title = $this->normalizeRequiredString($value, 'Заголовок третьего шага popup не может быть пустым.'); return $this; }
    public function getPopupStep3Description(): ?string { return $this->popupStep3Description; }
    public function setPopupStep3Description(string $value): static { $this->popupStep3Description = $this->normalizeRequiredString($value, 'Описание третьего шага popup не может быть пустым.'); return $this; }
    public function getPopupPrimaryCtaLabel(): ?string { return $this->popupPrimaryCtaLabel; }
    public function setPopupPrimaryCtaLabel(string $value): static { $this->popupPrimaryCtaLabel = $this->normalizeRequiredString($value, 'Текст основной кнопки popup не может быть пустым.'); return $this; }
    public function getPopupPrimaryCtaUrl(): ?string { return $this->popupPrimaryCtaUrl; }
    public function setPopupPrimaryCtaUrl(string $value): static { $this->popupPrimaryCtaUrl = $this->normalizeRequiredString($value, 'Ссылка основной кнопки popup не может быть пустой.'); return $this; }
    public function getPopupSecondaryCtaLabel(): ?string { return $this->popupSecondaryCtaLabel; }
    public function setPopupSecondaryCtaLabel(string $value): static { $this->popupSecondaryCtaLabel = $this->normalizeRequiredString($value, 'Текст вторичной кнопки popup не может быть пустым.'); return $this; }

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
