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

namespace Sonata\AdminBundle\Model;

use Psr\Container\ContainerInterface;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddAuditReadersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AuditManager implements AuditManagerInterface
{
    /**
     * @var array<string, string[]>
     * @phpstan-var array<string, class-string[]>
     */
    private $readers = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setReader(string $serviceId, array $classes): void
    {
        $this->readers[$serviceId] = $classes;
    }

    public function hasReader(string $class): bool
    {
        foreach ($this->readers as $classes) {
            if (\in_array($class, $classes, true)) {
                return true;
            }
        }

        return false;
    }

    public function getReader(string $class): AuditReaderInterface
    {
        foreach ($this->readers as $readerId => $classes) {
            if (\in_array($class, $classes, true)) {
                return $this->container->get($readerId);
            }
        }

        throw new \LogicException(sprintf('The class "%s" does not have any reader manager', $class));
    }
}
