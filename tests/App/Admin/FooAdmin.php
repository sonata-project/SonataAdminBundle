<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\App\Admin;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Tests\App\Model\Foo;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FooAdmin extends AbstractAdmin
{
    protected function createNewInstance(): object
    {
        return new Foo('test_id', 'foo_name');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->add('name', FieldDescriptionInterface::TYPE_STRING, [
            'sortable' => true,
        ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('name', TextType::class, ['help' => 'Help me!']);
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
}
