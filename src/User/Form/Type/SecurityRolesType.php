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

namespace SensioLabs\AdminBundle\User\Form\Type;

use SensioLabs\AdminBundle\User\Form\Transformer\RestoreRolesTransformer;
use SensioLabs\AdminBundle\User\Security\EditableRolesBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SecurityRolesType extends AbstractType
{
    public function __construct(
        private readonly EditableRolesBuilder $rolesBuilder,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transformer = new RestoreRolesTransformer($this->rolesBuilder);

        $builder->addModelTransformer($transformer);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($transformer): void {
            $transformer->setOriginalRoles($event->getData());
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $attr = $view->vars['attr'] ?? [];
        $attr['data-read-only'] = implode(',', $this->rolesBuilder->getRolesReadOnly());
        $view->vars['attr'] = $attr;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $roles = $this->rolesBuilder->getRoles();
        $choices = array_combine(array_keys($roles), array_keys($roles));

        $resolver->setDefaults([
            'choices' => false !== $choices ? $choices : [],
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sensiolabs_security_roles';
    }
}
