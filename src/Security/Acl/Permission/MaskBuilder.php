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

use Symfony\Component\Security\Acl\Permission\MaskBuilder as BaseMaskBuilder;

/**
 * {@inheritdoc}
 * - LIST: the SID is allowed to view a list of the domain objects / fields.
 * - EXPORT: the SID is allowed to export the list of the domain objects / fields.
 * - HISTORY: the SID is allowed to see the history of edition of a domain objects / fields.
 */
final class MaskBuilder extends BaseMaskBuilder
{
    public const MASK_LIST = 4096;       // 1 << 12
    public const MASK_EXPORT = 8192;     // 1 << 13
    public const MASK_HISTORY = 16384;   // 1 << 14

    public const CODE_LIST = 'L';
    public const CODE_EXPORT = 'E';
    public const CODE_HISTORY = 'H';
}
