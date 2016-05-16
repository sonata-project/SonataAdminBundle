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

@trigger_error(
    'The '.__NAMESPACE__.'\AdminExtension class is deprecated since version 3.1 and will be removed in 4.0.'
    .' Use '.__NAMESPACE__.'\AbstractAdminExtension instead.',
    E_USER_DEPRECATED
);

/**
 * @deprecated since version 3.1, to be removed in 4.0. Use Sonata\AdminBundle\AbstractAdminExtension instead.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class AdminExtension extends AbstractAdminExtension
{
}
