<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;

/**
 * Interface BuilderInterface.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface BuilderInterface
{
    /**
     * Adds missing information to the given field description from the model manager metadata, and the given admin.
     *
     * @param AdminInterface            $admin            will be used to gather information
     * @param FieldDescriptionInterface $fieldDescription will be modified
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription);
}
