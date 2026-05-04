<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $rolesChoices = [];
        foreach (UserRole::cases() as $role) {
            $rolesChoices[$role->name] = $role->value;
        }

        $password = TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Passwort'],
                'second_options' => ['label' => 'Repeat Password'],
                'invalid_message' => 'The passwords do not match',
                'mapped' => false,
            ])
            ->onlyOnForms()
            ->setRequired($pageName === Crud::PAGE_NEW);

        return [
            TextField::new('username'),
            EmailField::new('email'),
            ChoiceField::new('roles')
                ->setChoices($rolesChoices)
                ->allowMultipleChoices()
                ->renderAsBadges(),
            $password,
        ];
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $plainPassword = $entityDto->getInstance()?->getPassword();
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        return $this->addEncodePasswordEventListener($formBuilder, $plainPassword);
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        return $this->addEncodePasswordEventListener($formBuilder);
    }

    protected function addEncodePasswordEventListener(FormBuilderInterface $formBuilder, $plainPassword = null): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($plainPassword) {
            $user = $event->getData();
            $form = $event->getForm();
            $newPassword = $form->get('password')->getData();

            if (!empty($newPassword) && $newPassword !== $plainPassword) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
            }
        });
    }
}
