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
    'The '.__NAMESPACE__.'\Exporter class is deprecated since version 3.14 and will be removed in 4.0.'.
    ' Use Exporter\Exporter instead',
    E_USER_DEPRECATED
);

/**
 * NEXT_MAJOR: remove this class.
 *
 * @deprecated
 */
class Exporter extends BaseExporter
{
}
