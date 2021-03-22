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
if (!class_exists(\Sonata\AdminBundle\FieldDescription\BaseFieldDescription::class, false)) {
    @trigger_error(sprintf(
        'The %s\BaseFieldDescription class is deprecated since sonata-project/admin-bundle 3.92 and will be removed in 4.0.'
        .' Use \Sonata\AdminBundle\FieldDescription\BaseFieldDescription instead.',
        __NAMESPACE__
    ), \E_USER_DEPRECATED);
}

class_alias(
    \Sonata\AdminBundle\FieldDescription\BaseFieldDescription::class,
    __NAMESPACE__.'\BaseFieldDescription'
);

/*
 * @phpstan-ignore-next-line
 */
if (false) {
    /**
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0.
     * Use Sonata\AdminBundle\FieldDescription\BaseFieldDescription instead.
     */
    abstract class BaseFieldDescription extends \Sonata\AdminBundle\FieldDescription\BaseFieldDescription
    {
    }

    // NEXT_MAJOR: Uncomment this code:
//    final public function describesAssociation(): bool
//    {
//        return $this->describesSingleValuedAssociation() || $this->describesCollectionValuedAssociation();
//    }
}
