<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin\Extension;

use Sonata\AdminBundle\Extension\Event\GetPersistentParametersTask;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
interface GetPersistentParametersInterface extends AdminExtensionInterface
{
    /**
     * Get a chance to add persistent parameters.
     */
    public function getPersistentParameters(GetPersistentParametersTask $event);
}
