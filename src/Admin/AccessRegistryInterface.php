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
 * Tells if the current user has access to a given action.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @method bool hasAccess(string $action, ?object $object = null)
 */
interface AccessRegistryInterface
{
    /**
     * Return the controller access mapping.
     *
     * @return array
     */
    public function getAccessMapping();

    /**
     * Hook to handle access authorization.
     *
     * @param string $action
     * @param object $object
     */
    public function checkAccess($action, $object = null);

    /*
     * Hook to handle access authorization, without throwing an exception.
     *
     * NEXT_MAJOR: uncomment this method
     */
     // public function hasAccess(string $action, ?object $object = null): bool;
}
