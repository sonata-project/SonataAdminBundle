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

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_admin_template', [$this, 'getAdminTemplate']),
            new TwigFunction('get_global_template', [$this, 'getGlobalTemplate']),
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
        return $this->getTemplateRegistry($adminCode)->getTemplate($name);
    }

    /**
     * @param string $name
     */
    public function getGlobalTemplate($name): ?string
    {
        return $this->globalTemplateRegistry->getTemplate($name);
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function getTemplateRegistry(string $adminCode): TemplateRegistryInterface
    {
        $serviceId = sprintf('%s.template_registry', $adminCode);
        $templateRegistry = $this->container->get($serviceId);
        if ($templateRegistry instanceof TemplateRegistryInterface) {
            return $templateRegistry;
        }

        throw new ServiceNotFoundException($serviceId);
    }
}
