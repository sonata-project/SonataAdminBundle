<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;

interface ShowBuilderInterface extends BuilderInterface
{
    /**
     * @abstract
     *
     * @param array $options
     *
     * @return void
     */
    public function getBaseList(array $options = array());

    /**
     * @abstract
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionCollection $list
     * @param null                                                 $type
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface  $fieldDescription
     * @param \Sonata\AdminBundle\Admin\AdminInterface             $admin
     *
     * @return void
     */
    public function addField(FieldDescriptionCollection $list, $type = null, FieldDescriptionInterface $fieldDescription, AdminInterface $admin);

}
