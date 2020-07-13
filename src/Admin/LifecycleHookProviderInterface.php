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
 */
interface LifecycleHookProviderInterface
{
    /**
     * This method should call preUpdate, do the update, and call postUpdate.
     */
    public function update(object $object): object;

    /**
     * This method should call prePersist, do the creation, and call postPersist.
     */
    public function create(object $object): object;

    /**
     * This method should call preRemove, do the removal, and call postRemove.
     */
    public function delete(object $object): void;

    public function preValidate(object $object);

    public function preUpdate(object $object);

    public function postUpdate(object $object);

    public function prePersist(object $object);

    public function postPersist(object $object);

    public function preRemove(object $object);

    public function postRemove(object $object);
}
