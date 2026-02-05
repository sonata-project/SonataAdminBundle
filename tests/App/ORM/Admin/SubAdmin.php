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

namespace SensioLabs\AdminBundle\Tests\App\ORM\Admin;

use SensioLabs\AdminBundle\Admin\AbstractAdmin;
use SensioLabs\AdminBundle\Datagrid\ListMapper;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Sub;

/**
 * @phpstan-extends AbstractAdmin<Sub>
 */
final class SubAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');
        $list->add('otherField');
    }
}
