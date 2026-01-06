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

namespace Sonata\AdminBundle\Security\Acl\Permission;

use Symfony\Component\Security\Acl\Permission\PermissionMapInterface;

if (interface_exists(PermissionMapInterface::class)) {
    /**
     * @internal
     */
    abstract class BaseAdminPermissionMap implements PermissionMapInterface
    {
    }
} else {
    /**
     * @internal
     */
    abstract class BaseAdminPermissionMap
    {
    }
}

/**
 * This is basic permission map complements the masks which have been defined
 * on the standard implementation of the MaskBuilder.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Thomas Rabaix <thomas.rabaix@gmail.com>
 */
final class AdminPermissionMap extends BaseAdminPermissionMap
{
    public const PERMISSION_VIEW = 'VIEW';
    public const PERMISSION_EDIT = 'EDIT';
    public const PERMISSION_HISTORY = 'HISTORY';
    public const PERMISSION_CREATE = 'CREATE';
    public const PERMISSION_DELETE = 'DELETE';
    public const PERMISSION_UNDELETE = 'UNDELETE';
    public const PERMISSION_LIST = 'LIST';
    public const PERMISSION_EXPORT = 'EXPORT';
    public const PERMISSION_OPERATOR = 'OPERATOR';
    public const PERMISSION_MASTER = 'MASTER';
    public const PERMISSION_OWNER = 'OWNER';

    /**
     * Map each permission to the permissions it should grant access for
     * fe. grant access for the view permission if the user has the edit permission.
     *
     * @var array<string, int[]>
     */
    private array $map;

    public function __construct()
    {
        $this->map = $this->getMap();
    }

    /**
     * @param string $permission
     * @param object $object
     *
     * @return int[]|null
     */
    public function getMasks($permission, $object): ?array
    {
        if (!isset($this->map[$permission])) {
            return null;
        }

        return $this->map[$permission];
    }

    public function contains($permission): bool
    {
        return isset($this->map[$permission]);
    }

    /**
     * @return array<string, int[]>
     */
    private function getMap(): array
    {
        if (!class_exists(Symfony\Component\Security\Acl\Permission\MaskBuilder::class)) {
            return [
                self::PERMISSION_VIEW => [],
                self::PERMISSION_EDIT => [],
                self::PERMISSION_HISTORY => [],
                self::PERMISSION_CREATE => [],
                self::PERMISSION_DELETE => [],
                self::PERMISSION_UNDELETE => [],
                self::PERMISSION_LIST => [],
                self::PERMISSION_EXPORT => [],
                self::PERMISSION_OPERATOR => [],
                self::PERMISSION_MASTER => [],
                self::PERMISSION_OWNER => [],
            ];
        }

        return [
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

            self::PERMISSION_HISTORY => [
                MaskBuilder::MASK_HISTORY,
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
    }
}
