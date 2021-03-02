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

namespace Sonata\AdminBundle\Admin;

/**
 * This interface can be implemented to provide hooks that will be called
 * during the lifecycle of the object.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 */
interface LifecycleHookProviderInterface
{
    /**
     * @phpstan-param T $object
     * @phpstan-return T $object
     */
    public function update(object $object): object;

    /**
     * @phpstan-param T $object
     * @phpstan-return T $object
     */
    public function create(object $object): object;

    /**
     * @phpstan-param T $object
     */
    public function delete(object $object): void;
}
