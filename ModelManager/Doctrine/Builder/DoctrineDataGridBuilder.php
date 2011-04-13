<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\ModelManager\Doctrine\Builder;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\ModelManager\Doctrine\DataGrid\DoctrinePager;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class DoctrineDataGridBuilder implements DatagridBuilderInterface
{

    /**
     * todo: put this in the DIC
     *
     * built-in definition
     *
     * @var array
     */
    protected $filterClasses = array(
        'string'     =>  'Sonata\\AdminBundle\\ModelManager\\Doctrine\\Filter\\DoctrineStringFilter',
        'text'       =>  'Sonata\\AdminBundle\\ModelManager\\Doctrine\\Filter\\DoctrineStringFilter',
        'boolean'    =>  'Sonata\\AdminBundle\\ModelManager\\Doctrine\\Filter\\DoctrineBooleanFilter',
        'integer'    =>  'Sonata\\AdminBundle\\ModelManager\\Doctrine\\Filter\\DoctrineIntegerFilter',
        'tinyint'    =>  'Sonata\\AdminBundle\\ModelManager\\Doctrine\\Filter\\DoctrineIntegerFilter',
        'smallint'   =>  'Sonata\\AdminBundle\\ModelManager\\Doctrine\\Filter\\DoctrineIntegerFilter',
        'mediumint'  =>  'Sonata\\AdminBundle\\ModelManager\\Doctrine\\Filter\\DoctrineIntegerFilter',
        'bigint'     =>  'Sonata\\AdminBundle\\ModelManager\\Doctrine\\Filter\\DoctrineIntegerFilter',
        'decimal'    =>  'Sonata\\AdminBundle\\ModelManager\\Doctrine\\Filter\\DoctrineIntegerFilter',
        'callback'   =>  'Sonata\\AdminBundle\\ModelManager\\Doctrine\\Filter\\DoctrineCallbackFilter',
    );

    /**
     * @throws \RuntimeException
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescription $fieldDescription
     * @return void
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        // set default values
        $fieldDescription->setAdmin($admin);

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // set the default field mapping
            if (isset($metadata->fieldMappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$fieldDescription->getName()]);
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$fieldDescription->getName()])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$fieldDescription->getName()]);
            }
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin)));
        }

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));
        $fieldDescription->setOption('filter_value', $fieldDescription->getOption('filter_value', null));
        $fieldDescription->setOption('filter_options', $fieldDescription->getOption('filter_options', null));
        $fieldDescription->setOption('filter_field_options', $fieldDescription->getOption('filter_field_options', null));
        $fieldDescription->setOption('name', $fieldDescription->getOption('name', $fieldDescription->getName()));

        // set the default type if none is set
        if (!$fieldDescription->getType()) {
            $fieldDescription->setType('string');
        }

        if (!$fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate(sprintf('SonataAdminBundle:CRUD:filter_%s.html.twig', $fieldDescription->getType()));

            if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:filter_many_to_one.html.twig');
            }

            if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
                $fieldDescription->setTemplate('SonataAdminBundle:CRUD:filter_many_to_many.html.twig');
            }
        }
    }

    /**
     * return the class associated to a FieldDescription if any defined
     *
     * @throws RuntimeException
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return bool|string
     */
    public function getFilterFieldClass(FieldDescriptionInterface $fieldDescription)
    {

        if ($fieldDescription->getOption('filter_field_widget', false)) {
            $class = $fieldDescription->getOption('filter_field_widget', false);
        } else {
            $class = array_key_exists($fieldDescription->getType(), $this->filterClasses) ? $this->filterClasses[$fieldDescription->getType()] : false;
        }

        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf('The class `%s` does not exist for field `%s`', $class, $fieldDescription->getType()));
        }

        return $class;
    }

    /**
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return array
     */
    public function getChoices(FieldDescriptionInterface $fieldDescription)
    {
        $targets = $fieldDescription->getAdmin()->getModelManager()
            ->createQueryBuilder()
            ->select('t')
            ->from($fieldDescription->getTargetEntity(), 't')
            ->getQuery()
            ->execute();

        $choices = array();
        foreach ($targets as $target) {
            // todo : puts this into a configuration option and use reflection
            foreach (array('getTitle', 'getName', '__toString') as $getter) {
                if (method_exists($target, $getter)) {
                    $choices[$target->getId()] = $target->$getter();
                    break;
                }
            }
        }

        return $choices;
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ORM\Datagrid $datagrid
     * @param \Sonata\AdminBundle\Admin\FieldDescription $fieldDescription
     * @return bool
     */
    public function addFilter(DatagridInterface $datagrid, FieldDescriptionInterface $fieldDescription)
    {

        if (!$fieldDescription->getType()) {

            return false;
        }

        switch($fieldDescription->getType()) {

            case ClassMetadataInfo::MANY_TO_ONE:
                $options = $fieldDescription->getOption('filter_field_options');
                $filter = new \Sonata\AdminBundle\Filter\ORM\IntegerFilter($fieldDescription);

                break;
            case ClassMetadataInfo::MANY_TO_MANY:

                $options = $fieldDescription->getOption('filter_field_options');
                $options['choices'] = $this->getChoices($fieldDescription);

                $fieldDescription->setOption('filter_field_options', $options);

                $filter = new \Sonata\AdminBundle\Filter\ORM\ChoiceFilter($fieldDescription);

                break;

            default:

                $class = $this->getFilterFieldClass($fieldDescription);

                $filter = new $class($fieldDescription);

        }

        $datagrid->addFilter($filter);
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param array $values
     * @return \Sonata\AdminBundle\Datagrid\ORM\Datagrid
     */
    public function getBaseDatagrid(AdminInterface $admin, array $values = array())
    {
        return new Datagrid(
            $admin->getModelManager()->createQuery($admin->getClass()),
            $admin->getList(),
            new DoctrinePager,
            $values
        );
    }
}
