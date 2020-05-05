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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Tests\App\Model\PR6031;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class PR6031Admin extends AbstractAdmin
{
    public function getNewInstance()
    {
        return new PR6031('pr_6031', 'pr_6031');
    }

    protected function configureListFields(ListMapper $list)
    {
        $list->add('name', 'string');
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form->add('name', TextType::class);
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show->add('name', 'string');
    }

    protected function configureTabMenu(MenuItemInterface $menu, $action, ?AdminInterface $childAdmin = null)
    {
        $menu
            ->addChild('label')->addChild('label');
    }
}
