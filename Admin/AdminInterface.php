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
use Sonata\AdminBundle\Builder\FormBuilderInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
    
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
    
interface AdminInterface
{

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Builder\FormBuilderInterface $formBuilder
     * @return void
     */
    function setFormBuilder(FormBuilderInterface $formBuilder);

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
     * @param Doctrine\ORM\EntityManager|Doctrine\ODM\MongoDB\DocumentManager
     * @return void
     */
    function setModelManager($manager);

    /**
     * @abstract
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return void
     */
    function setRouter(RouterInterface $router);
}