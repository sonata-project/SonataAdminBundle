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
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;

/**
 * Interface ShowBuilderInterface.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface ShowBuilderInterface extends BuilderInterface
{
    /**
     * @abstract
     *
     * @param array $options
     */
    public function getBaseList(array $options = array());

    /**
     * @abstract
     *
     * @param FieldDescriptionCollection $list
     * @param string|null                $type
     * @param FieldDescriptionInterface  $fieldDescription
     * @param AdminInterface             $admin
     */
    public function addField(FieldDescriptionCollection $list, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin);
}
