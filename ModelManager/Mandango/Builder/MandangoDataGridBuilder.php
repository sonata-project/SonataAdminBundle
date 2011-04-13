<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\ModelManager\Mandango\Builder;

use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\ModelManager\Mandango\DataGrid\MandangoPager;

/**
 * MandangoDataGridBuilder.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoDataGridBuilder implements DatagridBuilderInterface
{
    /**
     * {@inheritdoc}
     */
     public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
     {
         $fieldDescription->setAdmin($admin);
     }

     /**
      * {@inheritdoc}
      */
     public function getFilterFieldClass(FieldDescriptionInterface $fieldDescription)
     {
     }

     /**
      * {@inheritdoc}
      */
     public function getChoices(FieldDescriptionInterface $fieldDescription)
     {
     }

     /**
      * {@inheritdoc}
      */
     public function addFilter(DatagridInterface $datagrid, FieldDescriptionInterface $fieldDescription)
     {
     }

     /**
      * {@inheritdoc}
      */
     public function getBaseDatagrid(AdminInterface $admin, array $values = array())
     {
         return new Datagrid(
             $admin->getModelManager()->createQuery($admin->getClass()),
             $admin->getList(),
             new MandangoPager(),
             $values
         );
     }
}
