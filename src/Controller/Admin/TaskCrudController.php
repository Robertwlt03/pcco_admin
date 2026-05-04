<?php

namespace App\Controller\Admin;

use App\Entity\Task;
use App\Enum\Status;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TaskCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Task::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $statusChoices = [];
        foreach (Status::cases() as $status) {
            $statusChoices[$status->name] = $status->value;
        }

        return [
            TextField::new('title'),
            DateTimeField::new('deadline'),
            ChoiceField::new('status')
                ->setChoices($statusChoices)
                ->renderAsBadges(),
            AssociationField::new('project'),
            AssociationField::new('assignedTo'),
        ];
    }
}
