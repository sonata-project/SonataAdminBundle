<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
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
    const PERMISSION_VIEW = 'VIEW';
    const PERMISSION_EDIT = 'EDIT';
    const PERMISSION_CREATE = 'CREATE';
    const PERMISSION_DELETE = 'DELETE';
    const PERMISSION_UNDELETE = 'UNDELETE';
    const PERMISSION_LIST = 'LIST';
    const PERMISSION_EXPORT = 'EXPORT';
    const PERMISSION_OPERATOR = 'OPERATOR';
    const PERMISSION_MASTER = 'MASTER';
    const PERMISSION_OWNER = 'OWNER';

    /**
     * Map each permission to the permissions it should grant access for
     * fe. grant access for the view permission if the user has the edit permission.
     *
     * @var array
     */
    private $map = [
        self::PERMISSION_VIEW => [
            MaskBuilder::MASK_VIEW,
            MaskBuilder::MASK_LIST,
            MaskBuilder::MASK_EDIT,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_EDIT => [
            MaskBuilder::MASK_EDIT,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_CREATE => [
            MaskBuilder::MASK_CREATE,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_DELETE => [
            MaskBuilder::MASK_DELETE,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_UNDELETE => [
            MaskBuilder::MASK_UNDELETE,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_LIST => [
            MaskBuilder::MASK_LIST,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_EXPORT => [
            MaskBuilder::MASK_EXPORT,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_OPERATOR => [
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_MASTER => [
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_OWNER => [
            MaskBuilder::MASK_OWNER,
        ],
    ];

    public function getMasks($permission, $object)
    {
        if (!isset($this->map[$permission])) {
            return;
        }

        return $this->map[$permission];
    }

    public function contains($permission)
    {
        return isset($this->map[$permission]);
    }
}
