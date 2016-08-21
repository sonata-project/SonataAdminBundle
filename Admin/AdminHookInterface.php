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

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * @author Christian Gripp <mail@core23.de>
 */
interface AdminHookInterface
{
    /**
     * @param mixed $object
     */
    public function preValidate($object);

    /**
     * @param mixed $object
     */
    public function preUpdate($object);

    /**
     * @param mixed $object
     */
    public function postUpdate($object);

    /**
     * @param mixed $object
     */
    public function prePersist($object);

    /**
     * @param mixed $object
     */
    public function postPersist($object);

    /**
     * @param mixed $object
     */
    public function preRemove($object);

    /**
     * @param mixed $object
     */
    public function postRemove($object);

    /**
     * Called before the batch action, allow you to alter the query and the idx.
     *
     * @param string              $action
     * @param ProxyQueryInterface $query
     * @param array               $idx
     * @param bool                $allElements
     */
    public function preBatchAction($action, ProxyQueryInterface $query, array &$idx, $allElements);
}
