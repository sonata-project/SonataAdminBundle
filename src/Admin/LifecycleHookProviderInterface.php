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

use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;

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
     * @param object $object
     *
     * @throws ModelManagerException
     * @throws LockException
     *
     * @return object
     *
     * @phpstan-param T $object
     * @phpstan-return T $object
     */
    public function update($object);

    /**
     * This method should call prePersist, do the creation, and call postPersist.
     *
     * @param object $object
     *
     * @throws ModelManagerException
     *
     * @return object
     *
     * @phpstan-param T $object
     * @phpstan-return T $object
     */
    public function create($object);

    /**
     * This method should call preRemove, do the removal, and call postRemove.
     *
     * @param object $object
     *
     * @throws ModelManagerException
     *
     * @phpstan-param T $object
     */
    public function delete($object);

    /**
     * NEXT_MAJOR: Remove this.
     *
     * @deprecated since sonata-project/admin-bundle 3.90
     *
     * @param object $object
     *
     * @phpstan-param T $object
     */
    public function preUpdate($object);

    /**
     * NEXT_MAJOR: Remove this.
     *
     * @deprecated since sonata-project/admin-bundle 3.90
     *
     * @param object $object
     *
     * @phpstan-param T $object
     */
    public function postUpdate($object);

    /**
     * NEXT_MAJOR: Remove this.
     *
     * @deprecated since sonata-project/admin-bundle 3.90
     *
     * @param object $object
     *
     * @phpstan-param T $object
     */
    public function prePersist($object);

    /**
     * NEXT_MAJOR: Remove this.
     *
     * @deprecated since sonata-project/admin-bundle 3.90
     *
     * @param object $object
     *
     * @phpstan-param T $object
     */
    public function postPersist($object);

    /**
     * NEXT_MAJOR: Remove this.
     *
     * @deprecated since sonata-project/admin-bundle 3.90
     *
     * @param object $object
     *
     * @phpstan-param T $object
     */
    public function preRemove($object);

    /**
     * NEXT_MAJOR: Remove this.
     *
     * @deprecated since sonata-project/admin-bundle 3.90
     *
     * @param object $object
     *
     * @phpstan-param T $object
     */
    public function postRemove($object);
}
