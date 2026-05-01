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
        return $crud->setFormOptions(
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

        yield FormField::addTab('Контент главной страницы');
        yield TextField::new('heroTitle', 'Заголовок');
        yield TextareaField::new('heroSubtitle', 'Подзаголовок');
        yield TextField::new('ctaLabel', 'Текст кнопки');
        yield TextField::new('ctaUrl', 'Ссылка кнопки');

        yield FormField::addTab('SEO');
        yield TextField::new('seoTitle', 'SEO title');
        yield TextareaField::new('seoDescription', 'SEO description');
        yield TextField::new('ogTitle', 'OG title')->setRequired(false);
        yield TextareaField::new('ogDescription', 'OG description')->setRequired(false);
    }
}
