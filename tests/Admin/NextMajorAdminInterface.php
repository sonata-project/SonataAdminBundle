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

namespace SensioLabs\AdminBundle\Tests\Admin;

use SensioLabs\AdminBundle\Admin\AdminExtensionInterface;
use SensioLabs\AdminBundle\Admin\AdminInterface;

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
