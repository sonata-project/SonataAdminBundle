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

interface ListBuilderInterface extends BuilderInterface
{
    /**
     * @param array $options
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionCollection
     */
    public function getBaseList(array $options = array());

    /**
     * Modify a field description to display it in the list view.
     *
     * @param null|mixed                                          $type
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param \Sonata\AdminBundle\Admin\AdminInterface            $admin
     */
    public function buildField($type = null, FieldDescriptionInterface $fieldDescription, AdminInterface $admin);

    /**
     * Modify a field description and add it to the displayed columns.
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionCollection $list
     * @param null|mixed                                           $type
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface  $fieldDescription
     * @param \Sonata\AdminBundle\Admin\AdminInterface             $admin
     */
    public function addField(FieldDescriptionCollection $list, $type = null, FieldDescriptionInterface $fieldDescription, AdminInterface $admin);
}
