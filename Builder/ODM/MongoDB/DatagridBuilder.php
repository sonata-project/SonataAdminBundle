<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder\ODM\MongoDB;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\ODM\MongoDB\Pager;
use Sonata\AdminBundle\Datagrid\ODM\MongoDB\ProxyQuery;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Symfony\Component\Form\FormFactory;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;

class DatagridBuilder implements DatagridBuilderInterface
{

    protected $filterFactory;
    protected $formFactory;
    protected $guesser;

    /**
     * @param \Symfony\Component\Form\FormFactory $formFactory
     * @param \Sonata\AdminBundle\Filter\FilterFactoryInterface $filterFactory
     * @param \Sonata\AdminBundle\Guesser\TypeGuesserInterface $guesser
     */
    public function __construct(FormFactory $formFactory, FilterFactoryInterface $filterFactory, TypeGuesserInterface $guesser)
    {
        $this->formFactory = $formFactory;
        $this->filterFactory = $filterFactory;
        $this->guesser = $guesser;
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
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

                // set the default association mapping
                if (isset($metadata->fieldMappings[$fieldDescription->getName()]['reference'])) {
                    $fieldDescription->setAssociationMapping($metadata->fieldMappings[$fieldDescription->getName()]);
                }
            }
        }

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));
        $fieldDescription->setOption('name', $fieldDescription->getOption('name', $fieldDescription->getName()));
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface $datagrid
     * @param null $type
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @return \Sonata\AdminBundle\Filter\FilterInterface
     */
    public function addFilter(DatagridInterface $datagrid, $type = null, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        if ($type == null) {
            $guessType = $this->guesser->guessType($admin->getClass(), $fieldDescription->getName());

            $type = $guessType->getType();

            $fieldDescription->setType($type);

            $options = $guessType->getOptions();

            foreach ($options as $name => $value) {
                if (is_array($value)) {
                    $fieldDescription->setOption($name, array_merge($value, $fieldDescription->getOption($name, array())));
                } else {
                    $fieldDescription->setOption($name, $fieldDescription->getOption($name, $value));
                }
            }
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($admin, $fieldDescription);
        $admin->addFilterFieldDescription($fieldDescription->getName(), $fieldDescription);

        $fieldDescription->mergeOption('field_options', array('required' => false));
        $filter = $this->filterFactory->create($fieldDescription->getName(), $type, $fieldDescription->getOptions());

        $datagrid->addFilter($filter);

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

        $formBuilder = $this->formFactory->createNamedBuilder('form', 'filter', array(), array('csrf_protection' => false));

        return new Datagrid($query, $admin->getList(), $pager, $formBuilder, $values);
    }

}