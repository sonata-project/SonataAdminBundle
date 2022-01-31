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
use Sonata\AdminBundle\Tests\App\Model\Bar;

/**
 * @phpstan-extends AbstractAdmin<Bar>
 */
class BarAdmin extends AbstractAdmin
{
    protected function createNewInstance(): object
    {
        return new Bar('test_id');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->add('id');
    }

    protected function configureFormFields(FormMapper $form): void
    {
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id');
    }
}
