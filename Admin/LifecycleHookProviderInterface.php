<?php

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
     *
     * @param object $object
     *
     * @return object
     */
    public function update($object);

    /**
     * This method should call prePersist, do the creation, and call postPersist.
     *
     * @param object $object
     *
     * @return object
     */
    public function create($object);

    /**
     * This method should call preRemove, do the removal, and call postRemove.
     *
     * @param object $object
     */
    public function delete($object);

    //NEXT_MAJOR: uncomment this method for 4.0
    //    /**
    //     * @param object $object
    //     */
    //    public function preValidate($object);

    /**
     * @param object $object
     */
    public function preUpdate($object);

    /**
     * @param object $object
     */
    public function postUpdate($object);

    /**
     * @param object $object
     */
    public function prePersist($object);

    /**
     * @param object $object
     */
    public function postPersist($object);

    /**
     * @param object $object
     */
    public function preRemove($object);

    /**
     * @param object $object
     */
    public function postRemove($object);
}
