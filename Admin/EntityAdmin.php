<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Admin;


abstract class EntityAdmin extends Admin
{

    /**
     * return the entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * return the doctrine class metadata handled by the Admin instance
     *
     * @return ClassMetadataInfo the doctrine class metadata handled by the Admin instance
     */
    public function getClassMetaData()
    {

        return $this->getEntityManager()
            ->getClassMetaData($this->getClass());
    }

    /**
     * return the FormBuilder
     *
     * @return FormBuilder
     */
    public function getFormBuilder()
    {
        return $this->container->get('sonata_base_application.builder.orm_form');
    }

    /**
     * return the ListBuilder
     *
     * @return ListBuilder
     */
    public function getListBuilder()
    {
        return $this->container->get('sonata_base_application.builder.orm_list');
    }

    /**
     * return the DatagridBuilder
     *
     * @return DatagridBuilder
     */
    public function getDatagridBuilder()
    {
        return $this->container->get('sonata_base_application.builder.orm_datagrid');
    }

}