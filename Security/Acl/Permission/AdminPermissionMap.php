<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Security\Acl\Permission;

use Symfony\Component\Security\Acl\Permission\PermissionMapInterface;

/**
 * This is basic permission map complements the masks which have been defined
 * on the standard implementation of the MaskBuilder.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Thomas Rabaix <thomas.rabaix@gmail.com>
 */
class AdminPermissionMap implements PermissionMapInterface
{
    const PERMISSION_SHOW        = 'SHOW';
    const PERMISSION_EDIT        = 'EDIT';
    const PERMISSION_CREATE      = 'CREATE';
    const PERMISSION_DELETE      = 'DELETE';
    const PERMISSION_UNDELETE    = 'UNDELETE';
    const PERMISSION_OPERATOR    = 'OPERATOR';
    const PERMISSION_MASTER      = 'MASTER';
    const PERMISSION_OWNER       = 'OWNER';

    const PERMISSION_LIST        = 'LIST';

    private $map = array(
        self::PERMISSION_LIST => array(
            MaskBuilder::MASK_LIST
        ),

        self::PERMISSION_SHOW => array(
            MaskBuilder::MASK_VIEW,
        ),

        self::PERMISSION_EDIT => array(
            MaskBuilder::MASK_EDIT,
        ),

        self::PERMISSION_CREATE => array(
            MaskBuilder::MASK_CREATE,
        ),

        self::PERMISSION_DELETE => array(
            MaskBuilder::MASK_DELETE,
        ),

        self::PERMISSION_UNDELETE => array(
            MaskBuilder::MASK_UNDELETE,
        ),

        self::PERMISSION_OPERATOR => array(
            MaskBuilder::MASK_OPERATOR,
        ),

        self::PERMISSION_MASTER => array(
            MaskBuilder::MASK_MASTER,
        ),

        self::PERMISSION_OWNER => array(
            MaskBuilder::MASK_OWNER,
        ),
    );

    /**
     * {@inheritDoc}
     */
    public function getMasks($permission, $object)
    {
        if (!isset($this->map[$permission])) {
            return null;
        }

        return $this->map[$permission];
    }

    /**
     * {@inheritDoc}
     */
    public function contains($permission)
    {
        return isset($this->map[$permission]);
    }
}