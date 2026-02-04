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

namespace SensioLabs\AdminBundle\Model;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AuditManagerInterface
{
    /**
     * Set AuditReaderInterface service id for array of $classes.
     *
     * @param string[] $classes
     *
     * @phpstan-param class-string[] $classes
     */
    public function setReader(string $serviceId, array $classes): void;

    /**
     * Returns true if $class has AuditReaderInterface.
     *
     * @phpstan-param class-string $class
     */
    public function hasReader(string $class): bool;

    /**
     * Get AuditReaderInterface service for $class.
     *
     * @throws \LogicException
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return AuditReaderInterface<T>
     */
    public function getReader(string $class): AuditReaderInterface;
}
