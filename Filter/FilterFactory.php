<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter;

use Sonata\AdminBundle\Filter\Filter as BaseFilter;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Form\FormBuilder;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Symfony\Component\Form\FormFactory;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterFactory implements FilterFactoryInterface
{
    protected $container;

    protected $types;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, array $types = array())
    {
        $this->container = $container;
        $this->types     = $types;
    }

    /**
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function create(FieldDescriptionInterface $fieldDescription)
    {
        if (!$fieldDescription->getType()) {
            throw new \RunTimeException('The type must be defined');
        }

        $type = $fieldDescription->getType();
//        $
//        switch($fieldDescription->getMappingType()) {
//            case ClassMetadataInfo::MANY_TO_ONE:
//                $options = $fieldDescription->getOption('filter_field_options');
//                $filter = new \Sonata\AdminBundle\Filter\ORM\IntegerFilter($fieldDescription);
//
//                break;
//
//        }

        $id = isset($this->types[$type]) ? $this->types[$type] : false;

        if (!$id) {
            throw new \RunTimeException(sprintf('No attached service to type named `%s`', $type));
        }

        $filter = $this->container->get($id);

        if (!$filter instanceof FilterInterface) {
            throw new \RunTimeException(sprintf('The service `%s` must implement `FilterInterface`', $id));
        }

        $fieldDescription->mergeOption('field_options', array('required' => false));
        $filter->setFieldDescription($fieldDescription);

        return $filter;
    }
}