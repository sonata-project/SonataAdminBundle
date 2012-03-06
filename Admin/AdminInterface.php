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
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;

use Knp\Menu\FactoryInterface as MenuFactoryInterface;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

interface AdminInterface
{
    /**
     * @param \Sonata\AdminBundle\Builder\FormContractorInterface $formContractor
     * @return void
     */
    function setFormContractor(FormContractorInterface $formContractor);

    /**
     * @param ListBuilderInterface $listBuilder
     * @return void
     */
    function setListBuilder(ListBuilderInterface $listBuilder);

    /**
     * @param \Sonata\AdminBundle\Builder\DatagridBuilderInterface $datagridBuilder
     * @return void
     */
    function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder);

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     * @return void
     */
    function setTranslator(TranslatorInterface $translator);

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return void
     */
    function setRequest(Request $request);

    /**
     * @param Pool $pool
     * @return void
     */
    function setConfigurationPool(Pool $pool);

    /**
     * @param \Sonata\AdminBundle\Route\RouteGeneratorInterface $router
     * @return void
     */
    function setRouteGenerator(RouteGeneratorInterface $routeGenerator);

    /**
     * Returns the class name managed
     *
     * @return void
     */
    function getClass();

    /**
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    function attachAdminClass(FieldDescriptionInterface $fieldDescription);

    /**
     * @return \Sonata\AdminBundle\Datagrid\DatagridInterface
     */
    function getDatagrid();

    /**
     * @param $name
     * @param array $parameters
     * @param bool $absolute
     * @return void
     */
    function generateUrl($name, array $parameters = array(), $absolute = false);

    /**
     * @return \Sonata\AdminBundle\ModelManagerInterface;
     */
    function getModelManager();

    /**
     * @return string the manager type of the admin
     */
    function getManagerType();

    /**
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    function createQuery($context = 'list');

    /**
     *
     * @return \Symfony\Component\Form\FormBuilder the form builder
     */
    function getFormBuilder();

    /**
     * @param string $name
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    function getFormFieldDescription($name);

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    function getRequest();

    /**
     *
     * @return string
     */
    function getCode();

    /**
     * Return the roles and permissions per role
     * - different permissions per role for the acl handler
     * - one permission that has the same name as the role for the role handler
     * This should be used by experimented users
     *
     * @return array [role] => array([permission], [permission])
     */
    function getSecurityInformation();

    /**
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $parentFieldDescription
     * @return void
     */
    function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription);

    /**
     * translate a message id
     *
     * @param string $id
     * @param array $parameters
     * @param null $domain
     * @param null $locale
     * @return string the translated string
     */
    function trans($id, array $parameters = array(), $domain = null, $locale = null);

    /**
     * Return the parameter name used to represente the id in the url
     *
     * @return string
     */
    function getRouterIdParameter();

    /**
     * add a FieldDescription
     *
     * @param string $name
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
     function addShowFieldDescription($name, FieldDescriptionInterface $fieldDescription);

    /**
     * add a list FieldDescription
     *
     * @param string $name
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
     function addListFieldDescription($name, FieldDescriptionInterface $fieldDescription);

    /**
     * add a filter FieldDescription
     *
     * @param string $name
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    function addFilterFieldDescription($name, FieldDescriptionInterface $fieldDescription);

    /**
     * Returns a list depend on the given $object
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionCollection
     */
    function getList();

    /**
     * @param \Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface $securityHandler
     * @return void
     */
    function setSecurityHandler(SecurityHandlerInterface $securityHandler);

    /**
     * @return \Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface|null
     */
    function getSecurityHandler();

    /**
     * @param string $name
     * @param object|null $object
     * @return boolean
     */
    function isGranted($name, $object = null);

    /**
     * @param $entity
     */
    function getNormalizedIdentifier($entity);

    /**
     * Shorthand method for templating
     *
     * @param object $entity
     * @return mixed
     */
    function id($entity);

    /**
     * @param \Symfony\Component\Validator\ValidatorInterface $validator
     * @return void
     */
    function setValidator(ValidatorInterface $validator);

    /**
     * @return \Symfony\Component\Validator\ValidatorInterface
     */
    function getValidator();

    /**
     * @return array
     */
    function getShow();

    /**
     * @param array $formTheme
     * @return void
     */
    function setFormTheme(array $formTheme);

    /**
     * @return array
     */
    function getFormTheme();

    /**
     * @param array $filterTheme
     * @return void
     */
    function setFilterTheme(array $filterTheme);

    /**
     * @return array
     */
    function getFilterTheme();

    /**
     * @param AdminExtensionInterface $extension
     * @return void
     */
    function addExtension(AdminExtensionInterface $extension);

    /**
     * Returns an array of extension related to the current Admin
     *
     * @return void
     */
    function getExtensions();

    /**
     * @param \Knp\Menu\FactoryInterface $menuFactory
     * @return void
     */
    function setMenuFactory(MenuFactoryInterface $menuFactory);

    /**
     * @return \Knp\Menu\FactoryInterface
     */
    function getMenuFactory();

    /**
     * @param \Sonata\AdminBundle\Builder\RouteBuilderInterface $routeBuilder
     */
    function setRouteBuilder(RouteBuilderInterface $routeBuilder);

    /**
     * @return \Sonata\AdminBundle\Builder\RouteBuilderInterface
     */
    function getRouteBuilder();

    /**
     * @param $object
     * @return string
     */
    function toString($object);

    /**
     * @param \Sonata\Adminbundle\Translator\LabelTranslatorStrategyInterface $labelTranslatorStrategy
     */
    function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy);

    /**
     * @return \Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface
     */
    function getLabelTranslatorStrategy();

    /**
     * add an Admin child to the current one
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $child
     * @return void
     */
    function addChild(AdminInterface $child);

    /**
     * Returns true or false if an Admin child exists for the given $code
     *
     * @param string $code Admin code
     * @return bool True if child exist, false otherwise
     */
    function hasChild($code);

    /**
     * Returns an collection of admin children
     *
     * @return array list of Admin children
     */
    function getChildren();

    /**
     * Returns an admin child with the given $code
     *
     * @param string $code
     * @return array|null
     */
    function getChild($code);

    /**
     * @return object a new object instance
     */
    function getNewInstance();

    function setUniqid($uniqId);

    function getObject($id);

    function setSubject($subject);

    function getSubject();

    /**
     * Returns a list FieldDescription
     *
     * @param string $name
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    function getListFieldDescription($name);

    function configure();

    function update($object);

    function create($object);

    function delete($object);

    function preUpdate($object);

    function postUpdate($object);

    function prePersist($object);

    function postPersist($object);

    function preRemove($object);

    function postRemove($object);

    /**
     * Return true if the Admin is related to a subject
     *
     * @return boolean
     */
    function hasSubject();

    /**
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param $object
     * @return void
     */
    function validate(ErrorElement $errorElement, $object);

    /**
     * @param $context
     * @return boolean
     */
    function showIn($context);

    /**
     * Add object security, fe. make the current user owner of the object
     *
     * @param $object
     */
    function createObjectSecurity($object);

    /**
     * Returns the url defined by the $name
     *
     * @param strinf $name
     * @return Route
     */
    function getRoute($name);

    /**
     * @return AdminInterface
     */
    function getParent();

    /**
     * @param AdminInterface $admin
     * @return void
     */
    function setParent(AdminInterface $admin);
}
