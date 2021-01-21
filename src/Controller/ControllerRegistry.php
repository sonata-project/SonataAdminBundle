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

namespace Sonata\AdminBundle\Controller;

use Sonata\AdminBundle\Admin\AdminInterface;

final class ControllerRegistry
{
    /**
     * @var array<string, string>
     */
    private $controllers;

    /**
     * @var string
     */
    private $defaultController;

    public function __construct(array $controllers, string $defaultController)
    {
        $this->controllers = $controllers;
        $this->defaultController = $defaultController;
    }

    public function byAdmin(AdminInterface $admin): string
    {
        return $this->controllers[$admin->getCode()] ?? $this->defaultController;
    }
}
