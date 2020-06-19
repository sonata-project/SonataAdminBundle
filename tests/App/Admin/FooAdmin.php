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

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class FooAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list->add('name', 'string');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('name', TextType::class, ['help' => 'Help me!']);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('name', 'string');
    }
}
