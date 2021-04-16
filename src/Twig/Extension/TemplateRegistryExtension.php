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

namespace Sonata\AdminBundle\Twig\Extension;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Exception\AdminCodeNotFoundException;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TemplateRegistryExtension extends AbstractExtension
{
    /**
     * @var TemplateRegistryInterface
     */
    private $globalTemplateRegistry;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * NEXT_MAJOR: Remove "null" from var type.
     *
     * @var Pool|null
     */
    private $pool;

    /**
     * @internal since sonata-project/admin-bundle 4. This class should only be used through Twig
     *
     * NEXT_MAJOR: Remove $container parameter and make Pool mandatory.
     */
    public function __construct(TemplateRegistryInterface $globalTemplateRegistry, ContainerInterface $container, ?Pool $pool = null)
    {
        $this->globalTemplateRegistry = $globalTemplateRegistry;
        $this->container = $container;
        $this->pool = $pool;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_admin_template', [$this, 'getAdminTemplate']),
            new TwigFunction('get_global_template', [$this, 'getGlobalTemplate']),
        ];
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function getAdminTemplate(string $name, string $adminCode): ?string
    {
        return $this->getTemplateRegistry($adminCode)->getTemplate($name);
    }

    public function getGlobalTemplate(string $name): ?string
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
