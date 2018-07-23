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

use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
interface ConfigureExportFieldsInterface extends AdminExtensionInterface
{
    /**
     * Get a chance to modify export fields.
     *
     * @param string[] $fields
     *
     * @return string[]
     */
    public function configureExportFields(AdminInterface $admin, array $fields);
}
