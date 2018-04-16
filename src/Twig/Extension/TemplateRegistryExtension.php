<?php

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
     * @var ContainerInterface
     */
    private $container;

    public function __construct(TemplateRegistryInterface $globalTemplateRegistry, ContainerInterface $container)
    {
        $this->globalTemplateRegistry = $globalTemplateRegistry;
        $this->container = $container;
    }

    public function getFunctions()
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
     *
     * @return null|string
     */
    public function getAdminTemplate($name, $adminCode)
    {
        // NEXT_MAJOR: Remove this line and use commented line below it instead
        return $this->getAdmin($adminCode)->getTemplate($name);
        // return $this->getTemplateRegistry($adminCode)->getTemplate($name);
    }

    /**
     * @deprecated Sinds 3.34, to be removed in 4.0. Use getGlobalTemplate instead.
     *
     * @param $name
     *
     * @return null|string
     */
    public function getPoolTemplate($name)
    {
        return $this->getGlobalTemplate($name);
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    public function getGlobalTemplate($name)
    {
        return $this->globalTemplateRegistry->getTemplate($name);
    }

    /**
     * @param string $adminCode
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     *
     * @return TemplateRegistryInterface
     */
    private function getTemplateRegistry($adminCode)
    {
        $serviceId = $adminCode.'.template_registry';
        $templateRegistry = $this->container->get($serviceId);
        if ($templateRegistry instanceof TemplateRegistryInterface) {
            return $templateRegistry;
        }

        throw new ServiceNotFoundException($serviceId);
    }

    /**
     * @deprecated since 3.34, will be dropped in 4.0. Use TemplateRegistry services instead
     *
     * @param string $adminCode
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     *
     * @return AdminInterface
     */
    private function getAdmin($adminCode)
    {
        $admin = $this->container->get($adminCode);
        if ($admin instanceof AdminInterface) {
            return $admin;
        }

        throw new ServiceNotFoundException($adminCode);
    }
}
