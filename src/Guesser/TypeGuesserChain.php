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

namespace Sonata\AdminBundle\Guesser;

if (!class_exists(\Sonata\AdminBundle\FieldDescription\TypeGuesserChain::class, false)) {
    @trigger_error(sprintf(
        'The %s\TypeGuesserChain class is deprecated since sonata-project/admin-bundle 3.92 and will be removed in 4.0.'
        .' Use \Sonata\AdminBundle\FieldDescription\TypeGuesserChain instead.',
        __NAMESPACE__
    ), \E_USER_DEPRECATED);
}

class_alias(
    \Sonata\AdminBundle\FieldDescription\TypeGuesserChain::class,
    __NAMESPACE__.'\TypeGuesserChain'
);

/*
 * @phpstan-ignore-next-line
 */
if (false) {
    /**
     * @deprecated since sonata-project/admin-bundle 3.92, to be removed in 4.0.
     * Use Sonata\AdminBundle\FieldDescription\TypeGuesserChain instead.
     */
    class TypeGuesserChain extends \Sonata\AdminBundle\FieldDescription\TypeGuesserChain
    {
    }
}
