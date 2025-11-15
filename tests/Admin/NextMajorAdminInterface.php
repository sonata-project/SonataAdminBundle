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

namespace Sonata\AdminBundle\Tests\Admin;

use Sonata\AdminBundle\Admin\AdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * @phpstan-template T of object
 * @phpstan-extends AdminInterface<T>
 *
 * NEXT_MAJOR: remove this interface as the methods are part of AdminInterface
 */
interface NextMajorAdminInterface extends AdminInterface
{
    public function showInDashboard(): bool;

    /**
     * @param AdminExtensionInterface<T> $extension
     */
    public function removeExtension(AdminExtensionInterface $extension): void;
}
