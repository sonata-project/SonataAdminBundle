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

use Sonata\AdminBundle\Admin\AdminInterface;
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

            // NEXT MAJOR: Remove this line
            new TwigFunction('get_admin_pool_template', [$this, 'getPoolTemplate'], ['deprecated' => true]),
        ];
    }

    /**
     * @param string $name
     * @param string $adminCode
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function getAdminTemplate($name, $adminCode): ?string
    {
        // NEXT_MAJOR: Remove this line and use commented line below it instead
        return $this->getAdmin($adminCode)->getTemplate($name);
        // return $this->getTemplateRegistry($adminCode)->getTemplate($name);
    }

    /**
     * @deprecated since sonata-project/admin-bundle 3.34, to be removed in 4.0. Use getGlobalTemplate instead.
     *
     * @param string $name
     */
    public function getPoolTemplate($name): ?string
    {
        return $this->getGlobalTemplate($name);
    }

    /**
     * @param string $name
     */
    public function getGlobalTemplate($name): ?string
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

    /**
     * @deprecated since sonata-project/admin-bundle 3.34, will be dropped in 4.0. Use TemplateRegistry services instead
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     *
     * @return AdminInterface<object>
     */
    private function getAdmin(string $adminCode): AdminInterface
    {
        $admin = $this->container->get($adminCode);
        if ($admin instanceof AdminInterface) {
            return $admin;
        }

        throw new ServiceNotFoundException($adminCode);
    }
}
