<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Builder;

use Sonata\BaseApplicationBundle\Admin\FieldDescription;
use Sonata\BaseApplicationBundle\Admin\Admin;
use Sonata\BaseApplicationBundle\Datagrid\Datagrid;

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
        'string'     =>  'Sonata\\BaseApplicationBundle\\Filter\\StringFilter',
        'text'       =>  'Sonata\\BaseApplicationBundle\\Filter\\StringFilter',
        'boolean'    =>  'Sonata\\BaseApplicationBundle\\Filter\\BooleanFilter',
        'integer'    =>  'Sonata\\BaseApplicationBundle\\Filter\\IntegerFilter',
        'tinyint'    =>  'Sonata\\BaseApplicationBundle\\Filter\\IntegerFilter',
        'smallint'   =>  'Sonata\\BaseApplicationBundle\\Filter\\IntegerFilter',
        'mediumint'  =>  'Sonata\\BaseApplicationBundle\\Filter\\IntegerFilter',
        'bigint'     =>  'Sonata\\BaseApplicationBundle\\Filter\\IntegerFilter',
        'decimal'    =>  'Sonata\\BaseApplicationBundle\\Filter\\IntegerFilter',
        'callback'   =>  'Sonata\\BaseApplicationBundle\\Filter\\CallbackFilter',
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
            $fieldDescription->setTemplate(sprintf('SonataBaseApplicationBundle:CRUD:filter_%s.twig.html', $fieldDescription->getType()));

            if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
                $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:filter_many_to_one.twig.html');
            }

            if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
                $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:filter_many_to_many.twig.html');
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

        if($fieldDescription->getOption('filter_field_widget', false)) {

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
        $targets = $fieldDescription->getAdmin()->getEntityManager()
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

            case ClassMetadataInfo::MANY_TO_MANY:

                $options = $fieldDescription->getOption('filter_field_options');
                $options['choices'] = $this->getChoices($fieldDescription);

                $fieldDescription->setOption('filter_field_options', $options);

                $filter = new \Sonata\BaseApplicationBundle\Filter\ChoiceFilter($fieldDescription);

                break;

            default:

                $class = $this->getFilterFieldClass($fieldDescription);

                $filter = new $class($fieldDescription);

        }

        $datagrid->addFilter($filter);
    }
   
}