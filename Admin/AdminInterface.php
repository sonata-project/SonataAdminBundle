<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

interface AdminInterface
{

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Builder\FormContractorInterface $formContractor
     * @return void
     */
    function setFormContractor(FormContractorInterface $formContractor);

    /**
     * @abstract
     * @param ListBuilderInterface $listBuilder
     * @return void
     */
    function setListBuilder(ListBuilderInterface $listBuilder);

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Builder\DatagridBuilderInterface $datagridBuilder
     * @return void
     */
    function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder);

    /**
     * @abstract
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     * @return void
     */
    function setTranslator(TranslatorInterface $translator);

    /**
     * @abstract
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return void
     */
    function setRequest(Request $request);

    /**
     * @abstract
     * @param Pool $pool
     * @return void
     */
    function setConfigurationPool(Pool $pool);

    /**
     * @abstract
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return void
     */
    function setRouter(RouterInterface $router);

    /**
     * Returns the class name managed
     *
     * @abstract
     * @return void
     */
    function getClass();

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    function attachAdminClass(FieldDescriptionInterface $fieldDescription);

    /**
     * @abstract
     * @return \Sonata\AdminBundle\Datagrid\DatagridInterface
     */
    function getDatagrid();

    /**
     * @abstract
     * @param string $name
     * @param array $parameters
     * @return void
     */
    function generateUrl($name, array $parameters = array());

    /**
     * @abstract
     * @return \Sonata\AdminBundle\ModelManagerInterface;
     */
    function getModelManager();

    /**
     *
     * @return \Symfony\Component\Form\FormBuilder the form builder
     */
    function getFormBuilder();

    /**
     * @abstract
     * @param string $name
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    function getFormFieldDescription($name);

    /**
     * @abstract
     * @return \Symfony\Component\HttpFoundation\Request
     */
    function getRequest();

    /**
     *
     * @return string
     */
    function getCode();

    /**
     * @abstract
     * @return array
     */
    function getSecurityInformation();
}