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

namespace Sonata\AdminBundle\Twig;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Exception\AdminCodeNotFoundException;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
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
