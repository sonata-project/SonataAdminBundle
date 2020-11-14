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
     * @phpstan-param class-string $class
     */
    public function getReader(string $class): AuditReaderInterface;
}
