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

namespace SensioLabs\AdminBundle\Tests\App\Admin;

use Knp\Menu\ItemInterface as MenuItemInterface;
use SensioLabs\AdminBundle\Admin\AbstractAdmin;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Datagrid\ListMapper;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\Form\FormMapper;
use SensioLabs\AdminBundle\Form\Type\CollectionType;
use SensioLabs\AdminBundle\Form\Type\ModelAutocompleteType;
use SensioLabs\AdminBundle\Form\Type\TemplateType;
use SensioLabs\AdminBundle\Show\ShowMapper;
use SensioLabs\AdminBundle\Tests\App\Model\Bar;
use SensioLabs\AdminBundle\Tests\App\Model\Foo;
use SensioLabs\AdminBundle\Tests\Fixtures\Controller\BatchOtherController;
use SensioLabs\AdminBundle\Form\Type\ImmutableArrayType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @phpstan-extends AbstractAdmin<Foo>
 */
class FooAdmin extends AbstractAdmin
{
    protected function createNewInstance(): object
    {
        return new Foo('test_id', 'foo_name');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('name', FieldDescriptionInterface::TYPE_STRING, [
                'sortable' => true,
            ])
            // Check for https://github.com/sonata-project/SensioLabsAdminBundle/issues/7447
            ->add('elements', FieldDescriptionInterface::TYPE_ARRAY);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', TextType::class, ['help' => 'Help me!'])
            ->add('customField', TemplateType::class, [
                'template' => 'foo/custom_field.html.twig',
                'parameters' => ['number' => 42],
            ])
            ->add(
                'referenced',
                ModelAutocompleteType::class,
                [
                    'class' => Bar::class,
                    'property' => ['name', 'title'],
                ],
                [
                    'admin_code' => 'sonata_bar_admin',
                ]
            )
            ->add(
                'elements',
                ImmutableArrayType::class,
                [
                    'help' => 'Elements main field help message',
                    'error_bubbling' => false,
                    'constraints' => [
                        new Collection(
                            fields: [
                                'elements_item' => new NotBlank(),
                                'missing_field' => new NotBlank(),
                            ],
                            allowMissingFields: false,
                        ),
                    ],
                    'keys' => [
                        [
                            'elements_item',
                            TextType::class,
                            [
                                'help' => 'Elements sub field help message',
                            ],
                        ],
                    ],
                ],
            )
            ->add(
                'collection',
                CollectionType::class,
                [
                    'error_bubbling' => false,
                    'constraints' => [
                        new Count(min: 2),
                    ],
                    'entry_type' => TextType::class,
                ],
            );
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('name', FieldDescriptionInterface::TYPE_STRING);
    }

    protected function configureTabMenu(MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
        // Check conflict between `MenuItemInterface::getLabel()` method and menu item with a child with the key `label`
        $menu->addChild('label')->addChild('label');
    }

    protected function configureBatchActions(array $actions): array
    {
        $actions = parent::configureBatchActions($actions);

        $actions['other'] = [
            'label' => 'Other',
            'ask_confirmation' => false,
            'controller' => BatchOtherController::class.'::batchAction',
        ];

        return $actions;
    }
}
