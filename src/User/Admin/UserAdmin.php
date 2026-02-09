<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\User\Admin;

use SensioLabs\AdminBundle\Admin\AbstractAdmin;
use SensioLabs\AdminBundle\Datagrid\DatagridMapper;
use SensioLabs\AdminBundle\Datagrid\ListMapper;
use SensioLabs\AdminBundle\Form\FormMapper;
use SensioLabs\AdminBundle\Show\ShowMapper;
use SensioLabs\AdminBundle\User\Form\Type\SecurityRolesType;
use SensioLabs\AdminBundle\User\Model\UserInterface;
use SensioLabs\AdminBundle\User\Model\UserManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;

/**
 * @phpstan-extends AbstractAdmin<UserInterface>
 */
class UserAdmin extends AbstractAdmin
{
    private ?UserManagerInterface $userManager = null;

    public function setUserManager(UserManagerInterface $userManager): void
    {
        $this->userManager = $userManager;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('email')
            ->add('firstname')
            ->add('lastname')
            ->add('groups')
            ->add('enabled', null, [
                'editable' => true,
            ])
            ->add('createdAt');

        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $list->add('impersonating', 'string', [
                'template' => '@SensioLabsAdmin/User/Field/impersonating.html.twig',
            ]);
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('groups');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('General')
                ->with('General')
                    ->add('email')
                    ->add('firstname')
                    ->add('lastname')
                ->end()
            ->end()
            ->tab('Profile')
                ->with('Profile')
                    ->add('phone')
                    ->add('locale')
                    ->add('timezone')
                ->end()
            ->end()
            ->tab('Groups')
                ->with('Groups')
                    ->add('groups')
                ->end()
            ->end()
            ->tab('Security')
                ->with('Security')
                    ->add('enabled')
                    ->add('createdAt')
                    ->add('updatedAt')
                ->end()
            ->end();
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('User')
                ->with('General', ['class' => 'col-md-6'])
                    ->add('email')
                    ->add('plainPassword', TextType::class, [
                        'required' => !$this->hasSubject() || null === $this->getSubject()->getId(),
                    ])
                    ->add('firstname', TextType::class, ['required' => false])
                    ->add('lastname', TextType::class, ['required' => false])
                ->end()
                ->with('Profile', ['class' => 'col-md-6'])
                    ->add('phone', TextType::class, ['required' => false])
                    ->add('locale', LocaleType::class, ['required' => false])
                    ->add('timezone', TimezoneType::class, ['required' => false])
                ->end()
            ->end()
            ->tab('Security')
                ->with('Status', ['class' => 'col-md-4'])
                    ->add('enabled')
                ->end()
                ->with('Groups', ['class' => 'col-md-4'])
                    ->add('groups', null, [
                        'required' => false,
                        'multiple' => true,
                    ])
                ->end()
                ->with('Roles', ['class' => 'col-md-4'])
                    ->add('realRoles', SecurityRolesType::class, [
                        'label' => false,
                        'required' => false,
                        'multiple' => true,
                        'expanded' => true,
                    ])
                ->end()
            ->end();
    }

    protected function configureExportFields(): array
    {
        return array_filter(
            parent::configureExportFields(),
            static fn (string $field): bool => !\in_array($field, ['password', 'confirmationToken'], true),
        );
    }

    protected function preUpdate(object $object): void
    {
        $this->userManager?->updatePassword($object);
    }

    protected function prePersist(object $object): void
    {
        $this->userManager?->updatePassword($object);
    }
}
