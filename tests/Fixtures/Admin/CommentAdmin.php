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

namespace SensioLabs\AdminBundle\Tests\Fixtures\Admin;

use SensioLabs\AdminBundle\Admin\AbstractAdmin;
use SensioLabs\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<object>
 */
class CommentAdmin extends AbstractAdmin
{
    public function setClassnameLabel(string $label): void
    {
        $this->classnameLabel = $label;
    }

    public function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('edit');
    }
}
