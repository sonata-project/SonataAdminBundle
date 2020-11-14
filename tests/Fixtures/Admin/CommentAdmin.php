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

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class CommentAdmin extends AbstractAdmin
{
    public function setClassnameLabel($label): void
    {
        $this->classnameLabel = $label;
    }

    public function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('edit');
    }

    public function setParentAssociationMapping($associationMapping): void
    {
        $this->parentAssociationMapping = $associationMapping;
    }
}
