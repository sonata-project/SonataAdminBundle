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

final class EmptyAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'empty';
    protected $baseRouteName = 'admin_empty';

    protected function configureListFields(ListMapper $list)
    {
        // Empty
    }

    protected function configureFormFields(FormMapper $form)
    {
        // Empty
    }

    protected function configureShowFields(ShowMapper $show)
    {
        // Empty
    }
}
