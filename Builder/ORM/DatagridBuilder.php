<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder\ORM;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\ORM\Pager;
use Sonata\AdminBundle\Datagrid\ORM\ProxyQuery;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Symfony\Component\Form\FormFactory;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class DatagridBuilder implements DatagridBuilderInterface
{
    protected $formFactory;

    /**
     * todo: put this in the DIC
     *
     * built-in definition
     *
     * @var array
     */
    protected $filterClasses = array(
        'string'     =>  'Sonata\\AdminBundle\\Filter\\ORM\\StringFilter',
        'text'       =>  'Sonata\\AdminBundle\\Filter\\ORM\\StringFilter',
        'boolean'    =>  'Sonata\\AdminBundle\\Filter\\ORM\\BooleanFilter',
        'integer'    =>  'Sonata\\AdminBundle\\Filter\\ORM\\IntegerFilter',
        'tinyint'    =>  'Sonata\\AdminBundle\\Filter\\ORM\\IntegerFilter',
        'smallint'   =>  'Sonata\\AdminBundle\\Filter\\ORM\\IntegerFilter',
        'mediumint'  =>  'Sonata\\AdminBundle\\Filter\\ORM\\IntegerFilter',
        'bigint'     =>  'Sonata\\AdminBundle\\Filter\\ORM\\IntegerFilter',
        'decimal'    =>  'Sonata\\AdminBundle\\Filter\\ORM\\IntegerFilter',
        'callback'   =>  'Sonata\\AdminBundle\\Filter\\ORM\\CallbackFilter',
        'choice'     =>  'Sonata\\AdminBundle\\Filter\\ORM\\ChoiceFilter',
    );

    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

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
        $modelManager = $fieldDescription->getAdmin()->getModelManager();
        $targets = $modelManager->getEntityManager()
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
                    $choices[$modelManager->getNormalizedIdentifier($target)] = $target->$getter();
                    break;
                }
            }
        }

        return $choices;
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface $datagrid
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return bool
     */
    public function addFilter(DatagridInterface $datagrid, FieldDescriptionInterface $fieldDescription)
    {
        if (!$fieldDescription->getType()) {
            return false;
        }

        switch($fieldDescription->getMappingType()) {
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

        return $datagrid->addFilter($filter);
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param array $values
     * @return \Sonata\AdminBundle\Datagrid\DatagridInterface
     */
    public function getBaseDatagrid(AdminInterface $admin, array $values = array())
    {
        $queryBuilder = $admin->getModelManager()->createQuery($admin->getClass());

        $query = new ProxyQuery($queryBuilder);
        $pager = new Pager;
        $pager->setCountColumn($admin->getModelManager()->getIdentifierFieldNames($admin->getClass()));

        return new Datagrid(
            $query,
            $admin->getList(),
            $pager,
            $this->formFactory,
            $values
        );
    }
}