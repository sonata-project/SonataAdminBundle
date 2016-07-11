<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Export;

use Sonata\CoreBundle\Exporter\Exporter as BaseExporter;

@trigger_error(
    'The '.__NAMESPACE__.'\Exporter class is deprecated since version 2.2.9 and will be removed in 4.0.'
    .' Use Sonata\CoreBundle\Exporter\Exporter instead.',
    E_USER_DEPRECATED
);

/**
 * @deprecated since version 2.2.9, to be removed in 4.0. Use Sonata\CoreBundle\Exporter\Exporter instead.
 */
class Exporter extends BaseExporter
{
}
