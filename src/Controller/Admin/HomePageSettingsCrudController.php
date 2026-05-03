<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\HomePageSettings;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class HomePageSettingsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return HomePageSettings::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::NEW, Action::DELETE);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Главная страница')
            ->setEntityLabelInPlural('Главная страница')
            ->setPageTitle(Crud::PAGE_EDIT, 'Главная страница')
            ->setFormOptions(
                newFormOptions: [
                    'csrf_token_id' => 'ea_home_page_settings',
                ],
                editFormOptions: [
                    'csrf_token_id' => 'ea_home_page_settings',
                ],
            );
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield FormField::addTab('Главный экран');
        yield TextField::new('brandText', 'Бренд');
        yield TextField::new('navFeaturesLabel', 'Меню 1: текст');
        yield TextField::new('navFeaturesUrl', 'Меню 1: ссылка');
        yield TextField::new('navTemplatesLabel', 'Меню 2: текст');
        yield TextField::new('navTemplatesUrl', 'Меню 2: ссылка');
        yield TextField::new('navInvestorLabel', 'Меню 3: текст');
        yield TextField::new('navInvestorUrl', 'Меню 3: ссылка');
        yield TextField::new('eyebrowText', 'Надзаголовок');
        yield TextField::new('heroTitle', 'Заголовок');
        yield TextareaField::new('heroSubtitle', 'Подзаголовок');
        yield TextField::new('ctaLabel', 'Основная кнопка: текст');
        yield TextField::new('ctaUrl', 'Основная кнопка: ссылка');
        yield TextField::new('secondaryCtaLabel', 'Вторичная кнопка: текст');
        yield TextField::new('secondaryCtaUrl', 'Вторичная кнопка: ссылка');
        yield TextareaField::new('trustTagsText', 'Теги доверия')
            ->setHelp('Каждый тег с новой строки.');

        yield FormField::addTab('Превью модели');
        yield TextField::new('previewKicker', 'Превью: надзаголовок');
        yield TextField::new('previewTitle', 'Превью: заголовок');
        yield TextField::new('previewStatus', 'Превью: статус');
        yield TextField::new('previewMetric1Label', 'Метрика 1: название');
        yield TextField::new('previewMetric1Value', 'Метрика 1: значение');
        yield TextField::new('previewMetric1Note', 'Метрика 1: описание');
        yield TextField::new('previewMetric2Label', 'Метрика 2: название');
        yield TextField::new('previewMetric2Value', 'Метрика 2: значение');
        yield TextField::new('previewMetric2Note', 'Метрика 2: описание');
        yield TextField::new('previewMetric3Label', 'Метрика 3: название');
        yield TextField::new('previewMetric3Value', 'Метрика 3: значение');
        yield TextField::new('previewMetric3Note', 'Метрика 3: описание');
        yield TextField::new('previewMetric4Label', 'Метрика 4: название');
        yield TextField::new('previewMetric4Value', 'Метрика 4: значение');
        yield TextField::new('previewMetric4Note', 'Метрика 4: описание');
        yield TextField::new('chartTitle', 'График: заголовок');
        yield TextField::new('chartPeriod', 'График: период');
        yield TextareaField::new('assistantNote', 'AI-подсказка');

        yield FormField::addTab('Попап');
        yield TextField::new('popupKicker', 'Попап: надзаголовок');
        yield TextField::new('popupTitle', 'Попап: заголовок');
        yield TextareaField::new('popupDescription', 'Попап: описание');
        yield TextField::new('popupStep1Title', 'Шаг 1: заголовок');
        yield TextField::new('popupStep1Description', 'Шаг 1: описание');
        yield TextField::new('popupStep2Title', 'Шаг 2: заголовок');
        yield TextField::new('popupStep2Description', 'Шаг 2: описание');
        yield TextField::new('popupStep3Title', 'Шаг 3: заголовок');
        yield TextField::new('popupStep3Description', 'Шаг 3: описание');
        yield TextField::new('popupPrimaryCtaLabel', 'Основная кнопка: текст');
        yield TextField::new('popupPrimaryCtaUrl', 'Основная кнопка: ссылка');
        yield TextField::new('popupSecondaryCtaLabel', 'Вторичная кнопка: текст');

        yield FormField::addTab('SEO');
        yield TextField::new('seoTitle', 'SEO title');
        yield TextareaField::new('seoDescription', 'SEO description');
        yield TextField::new('ogTitle', 'OG title')->setRequired(false);
        yield TextareaField::new('ogDescription', 'OG description')->setRequired(false);
    }
}
