<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Enum\Status;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $statusChoices = [];
        foreach (Status::cases() as $status) {
            $statusChoices[$status->name] = $status->value;
        }

        return [
            TextField::new('title'),
            TextEditorField::new('description'),
            ChoiceField::new('status')
                ->setChoices($statusChoices)
                ->renderAsBadges(),
            MoneyField::new('budget')
                ->setCurrency('EUR'),
            AssociationField::new('customer'),
        ];
    }
}
