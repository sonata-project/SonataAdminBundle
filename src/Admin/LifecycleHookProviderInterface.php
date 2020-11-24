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
     * This method should call preUpdate, do the update, and call postUpdate.
     *
     * @phpstan-param T $object
     */
    public function update(object $object): object;

    /**
     * This method should call prePersist, do the creation, and call postPersist.
     *
     * @phpstan-param T $object
     */
    public function create(object $object): object;

    /**
     * This method should call preRemove, do the removal, and call postRemove.
     *
     * @phpstan-param T $object
     */
    public function delete(object $object): void;

    /**
     * @phpstan-param T $object
     */
    public function preUpdate(object $object): void;

    /**
     * @phpstan-param T $object
     */
    public function postUpdate(object $object): void;

    /**
     * @phpstan-param T $object
     */
    public function prePersist(object $object): void;

    /**
     * @phpstan-param T $object
     */
    public function postPersist(object $object): void;

    /**
     * @phpstan-param T $object
     */
    public function preRemove(object $object): void;

    /**
     * @phpstan-param T $object
     */
    public function postRemove(object $object): void;
}
