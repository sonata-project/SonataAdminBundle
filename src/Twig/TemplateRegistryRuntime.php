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

namespace SensioLabs\AdminBundle\Twig;

use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Exception\AdminCodeNotFoundException;
use SensioLabs\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Twig\Extension\RuntimeExtensionInterface;

final class TemplateRegistryRuntime implements RuntimeExtensionInterface
{
    /**
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private TemplateRegistryInterface $globalTemplateRegistry,
        private Pool $pool,
    ) {
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function getAdminTemplate(string $name, string $adminCode): string
    {
        return $this->getTemplateRegistry($adminCode)->getTemplate($name);
    }

    public function getGlobalTemplate(string $name): string
    {
        return $this->globalTemplateRegistry->getTemplate($name);
    }

    /**
     * @throws AdminCodeNotFoundException
     */
    private function getTemplateRegistry(string $adminCode): TemplateRegistryInterface
    {
        $admin = $this->pool->getAdminByAdminCode($adminCode);

        return $admin->getTemplateRegistry();
    }
}
