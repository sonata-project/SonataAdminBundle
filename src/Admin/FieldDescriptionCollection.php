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
if (!class_exists(\Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection::class, false)) {
    @trigger_error(sprintf(
        'The %s\FieldDescriptionCollection class is deprecated since sonata-project/admin-bundle 3.92 and will be removed in 4.0.'
        .' Use \Sonata\AdminBundle\FieldDescription\TypeGuesserChain instead.',
        __NAMESPACE__
    ), \E_USER_DEPRECATED);
}

class_alias(
    \Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection::class,
    __NAMESPACE__.'\FieldDescriptionCollection'
);

/*
 * @phpstan-ignore-next-line
 */
if (false) {
    /**
     * @deprecated since sonata-project/admin-bundle 3.95, to be removed in 4.0.
     * Use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection instead.
     *
     * @phpstan-template TValue of FieldDescriptionInterface
     * @phpstan-extends \Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection<TValue>
     */
    class FieldDescriptionCollection extends \Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection
    {
    }
}
