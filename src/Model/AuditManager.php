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

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AuditManager implements AuditManagerInterface
{
    /**
     * @var array<string, string[]>
     *
     * @phpstan-var array<string, class-string[]>
     */
    private array $readers = [];

    public function __construct(
        private ContainerInterface $container,
    ) {
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

    /**
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return AuditReaderInterface<T>
     */
    public function getReader(string $class): AuditReaderInterface
    {
        foreach ($this->readers as $readerId => $classes) {
            if (\in_array($class, $classes, true)) {
                $service = $this->container->get($readerId);
                if (!$service instanceof AuditReaderInterface) {
                    throw new \LogicException(\sprintf(
                        'Service "%s" MUST implement interface "%s".',
                        $readerId,
                        AuditReaderInterface::class,
                    ));
                }

                /** @var AuditReaderInterface<T> $reader */
                $reader = $service;

                return $reader;
            }
        }

        throw new \LogicException(\sprintf('The class "%s" does not have any reader manager', $class));
    }
}
