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

use Sonata\AdminBundle\Admin\FieldDescription;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\Datagrid;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class DatagridBuilder implements DatagridBuilderInterface
{

    /**
     * todo: put this in the DIC
     *
     * built-in definition
     *
     * @var array
     */
    protected $filterClasses = array(
        'string'     =>  'Sonata\\AdminBundle\\Filter\\StringFilter',
        'text'       =>  'Sonata\\AdminBundle\\Filter\\StringFilter',
        'boolean'    =>  'Sonata\\AdminBundle\\Filter\\BooleanFilter',
        'integer'    =>  'Sonata\\AdminBundle\\Filter\\IntegerFilter',
        'tinyint'    =>  'Sonata\\AdminBundle\\Filter\\IntegerFilter',
        'smallint'   =>  'Sonata\\AdminBundle\\Filter\\IntegerFilter',
        'mediumint'  =>  'Sonata\\AdminBundle\\Filter\\IntegerFilter',
        'bigint'     =>  'Sonata\\AdminBundle\\Filter\\IntegerFilter',
        'decimal'    =>  'Sonata\\AdminBundle\\Filter\\IntegerFilter',
        'callback'   =>  'Sonata\\AdminBundle\\Filter\\CallbackFilter',
    );

    public function fixFieldDescription(Admin $admin, FieldDescription $fieldDescription)
    {
        // set default values
        $fieldDescription->setAdmin($admin);

        // set the default field mapping
        if (isset($admin->getClassMetaData()->fieldMappings[$fieldDescription->getName()])) {
            $fieldDescription->setFieldMapping($admin->getClassMetaData()->fieldMappings[$fieldDescription->getName()]);
        }

        // set the default association mapping
        if (isset($admin->getClassMetaData()->associationMappings[$fieldDescription->getName()])) {
            $fieldDescription->setAssociationMapping($admin->getClassMetaData()->associationMappings[$fieldDescription->getName()]);
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
     * @param FieldDescription $fieldDescription
     * @return bool|string
     */
    public function getFilterFieldClass(FieldDescription $fieldDescription)
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

    public function getChoices(FieldDescription $fieldDescription)
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

    public function addFilter(Datagrid $datagrid, FieldDescription $fieldDescription)
    {

        if (!$fieldDescription->getType()) {

            return false;
        }

        switch($fieldDescription->getType()) {

            case ClassMetadataInfo::MANY_TO_ONE:
                $options = $fieldDescription->getOption('filter_field_options');
                $filter = new \Sonata\AdminBundle\Filter\IntegerFilter($fieldDescription);

                break;
            case ClassMetadataInfo::MANY_TO_MANY:

                $options = $fieldDescription->getOption('filter_field_options');
                $options['choices'] = $this->getChoices($fieldDescription);

                $fieldDescription->setOption('filter_field_options', $options);

                $filter = new \Sonata\AdminBundle\Filter\ChoiceFilter($fieldDescription);

                break;

            default:

                $class = $this->getFilterFieldClass($fieldDescription);

                $filter = new $class($fieldDescription);

        }

        $datagrid->addFilter($filter);
    }
   
}