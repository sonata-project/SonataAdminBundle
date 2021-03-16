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

// NEXT_MAJOR: Remove this file.
if (!interface_exists(\Sonata\AdminBundle\FieldDescription\FieldDescriptionRegistryInterface::class, false)) {
    @trigger_error(sprintf(
        'The %s\FieldDescriptionRegistryInterface class is deprecated since sonata-project/admin-bundle 3.92 and will be removed in 4.0.'
        .' Use \Sonata\AdminBundle\FieldDescription\FieldDescriptionRegistryInterface instead.',
        __NAMESPACE__
    ), \E_USER_DEPRECATED);
}

class_alias(
    \Sonata\AdminBundle\FieldDescription\FieldDescriptionRegistryInterface::class,
    __NAMESPACE__.'\FieldDescriptionRegistryInterface'
);

/*
 * @phpstan-ignore-next-line
 */
if (false) {
    /**
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0.
     * Use Sonata\AdminBundle\FieldDescription\FieldDescriptionRegistryInterface instead.
     */
    interface FieldDescriptionRegistryInterface extends \Sonata\AdminBundle\FieldDescription\FieldDescriptionRegistryInterface
    {
    }
}
