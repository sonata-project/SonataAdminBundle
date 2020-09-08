<?php

namespace Sonata\AdminBundle\Templating;

use Psr\Container\ContainerInterface;

final class TemplateRegistryProvider
{
    /**
     * @var ContainerInterface
     */
    private $registryLocator;

    public function __construct(ContainerInterface $registryLocator)
    {
        $this->registryLocator = $registryLocator;
    }

    public function getTemplateRegistry(string $adminCode): ?TemplateRegistryInterface
    {
        if (!$this->registryLocator->has($adminCode)) {
            return null;
        }

        return $this->registryLocator->get($adminCode);
    }
}
