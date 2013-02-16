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
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

interface AdminInterface
{
    /**
     * @param \Sonata\AdminBundle\Builder\FormContractorInterface $formContractor
     *
     * @return void
     */
    public function setFormContractor(FormContractorInterface $formContractor);

    /**
     * @param ListBuilderInterface $listBuilder
     *
     * @return void
     */
    public function setListBuilder(ListBuilderInterface $listBuilder);

    /**
     * @param \Sonata\AdminBundle\Builder\DatagridBuilderInterface $datagridBuilder
     *
     * @return void
     */
    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder);

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     *
     * @return void
     */
    public function setTranslator(TranslatorInterface $translator);

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function setRequest(Request $request);

    /**
     * @param Pool $pool
     *
     * @return void
     */
    public function setConfigurationPool(Pool $pool);

    /**
     * @param \Sonata\AdminBundle\Route\RouteGeneratorInterface $routeGenerator
     *
     * @return void
     */
    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator);

    /**
     * Returns the class name managed
     *
     * @return string
     */
    public function getClass();

    /**
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     *
     * @return void
     */
    public function attachAdminClass(FieldDescriptionInterface $fieldDescription);

    /**
     * @return \Sonata\AdminBundle\Datagrid\DatagridInterface
     */
    public function getDatagrid();

    /**
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @return string
     */
    public function generateUrl($name, array $parameters = array(), $absolute = false);

    /**
     * @return \Sonata\AdminBundle\Model\ModelManagerInterface;
     */
    public function getModelManager();

    /**
     * @return string the manager type of the admin
     */
    public function getManagerType();

    /**
     * @param string $context
     *
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function createQuery($context = 'list');

    /**
     * @return \Symfony\Component\Form\FormBuilder the form builder
     */
    public function getFormBuilder();

    /**
     * @param string $name
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function getFormFieldDescription($name);

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest();

    /**
     *
     * @return string
     */
    public function getCode();

    /**
     * Return the roles and permissions per role
     * - different permissions per role for the acl handler
     * - one permission that has the same name as the role for the role handler
     * This should be used by experimented users
     *
     * @return array [role] => array([permission], [permission])
     */
    public function getSecurityInformation();

    /**
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $parentFieldDescription
     *
     * @return void
     */
    public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription);

    /**
     * translate a message id
     *
     * @param string $id
     * @param array  $parameters
     * @param null   $domain
     * @param null   $locale
     *
     * @return string the translated string
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null);

    /**
     * Return the parameter name used to represente the id in the url
     *
     * @return string
     */
    public function getRouterIdParameter();

    /**
     * add a FieldDescription
     *
     * @param string                                              $name
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     *
     * @return void
     */
    public function addShowFieldDescription($name, FieldDescriptionInterface $fieldDescription);

    /**
     * add a list FieldDescription
     *
     * @param string                                              $name
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     *
     * @return void
     */
    public function addListFieldDescription($name, FieldDescriptionInterface $fieldDescription);

    /**
     * add a filter FieldDescription
     *
     * @param string                                              $name
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     *
     * @return void
     */
    public function addFilterFieldDescription($name, FieldDescriptionInterface $fieldDescription);

    /**
     * Returns a list depend on the given $object
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionCollection
     */
    public function getList();

    /**
     * @param \Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface $securityHandler
     *
     * @return void
     */
    public function setSecurityHandler(SecurityHandlerInterface $securityHandler);

    /**
     * @return \Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface|null
     */
    public function getSecurityHandler();

    /**
     * @param string      $name
     * @param object|null $object
     *
     * @return boolean
     */
    public function isGranted($name, $object = null);

    /**
     * @param mixed $entity
     */
    public function getUrlsafeIdentifier($entity);

    /**
     * @param mixed $entity
     */
    public function getNormalizedIdentifier($entity);

    /**
     * Shorthand method for templating
     *
     * @param object $entity
     *
     * @return mixed
     */
    public function id($entity);

    /**
     * @param \Symfony\Component\Validator\ValidatorInterface $validator
     *
     * @return void
     */
    public function setValidator(ValidatorInterface $validator);

    /**
     * @return \Symfony\Component\Validator\ValidatorInterface
     */
    public function getValidator();

    /**
     * @return array
     */
    public function getShow();

    /**
     * @param array $formTheme
     *
     * @return void
     */
    public function setFormTheme(array $formTheme);

    /**
     * @return array
     */
    public function getFormTheme();

    /**
     * @param array $filterTheme
     *
     * @return void
     */
    public function setFilterTheme(array $filterTheme);

    /**
     * @return array
     */
    public function getFilterTheme();

    /**
     * @param AdminExtensionInterface $extension
     *
     * @return void
     */
    public function addExtension(AdminExtensionInterface $extension);

    /**
     * Returns an array of extension related to the current Admin
     *
     * @return void
     */
    public function getExtensions();

    /**
     * @param \Knp\Menu\FactoryInterface $menuFactory
     *
     * @return void
     */
    public function setMenuFactory(MenuFactoryInterface $menuFactory);

    /**
     * @return \Knp\Menu\FactoryInterface
     */
    public function getMenuFactory();

    /**
     * @param \Sonata\AdminBundle\Builder\RouteBuilderInterface $routeBuilder
     */
    public function setRouteBuilder(RouteBuilderInterface $routeBuilder);

    /**
     * @return \Sonata\AdminBundle\Builder\RouteBuilderInterface
     */
    public function getRouteBuilder();

    /**
     * @param mixed $object
     *
     * @return string
     */
    public function toString($object);

    /**
     * @param \Sonata\Adminbundle\Translator\LabelTranslatorStrategyInterface $labelTranslatorStrategy
     */
    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy);

    /**
     * @return \Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface
     */
    public function getLabelTranslatorStrategy();

    /**
     * add an Admin child to the current one
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $child
     *
     * @return void
     */
    public function addChild(AdminInterface $child);

    /**
     * Returns true or false if an Admin child exists for the given $code
     *
     * @param string $code Admin code
     *
     * @return bool True if child exist, false otherwise
     */
    public function hasChild($code);

    /**
     * Returns an collection of admin children
     *
     * @return array list of Admin children
     */
    public function getChildren();

    /**
     * Returns an admin child with the given $code
     *
     * @param string $code
     *
     * @return array|null
     */
    public function getChild($code);

    /**
     * @return mixed a new object instance
     */
    public function getNewInstance();

    /**
     * @param string $uniqId
     *
     * @return mixed
     */
    public function setUniqid($uniqId);

    /**
     * @param mixed $id
     *
     * @return mixed
     */
    public function getObject($id);

    /**
     * @param string $subject
     *
     * @return mixed
     */
    public function setSubject($subject);

    /**
     * @return mixed
     */
    public function getSubject();

    /**
     * Returns a list FieldDescription
     *
     * @param string $name
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function getListFieldDescription($name);

    /**
     * @return void
     */
    public function configure();

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function update($object);

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function create($object);

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function delete($object);

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function preUpdate($object);

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function postUpdate($object);

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function prePersist($object);

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function postPersist($object);

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function preRemove($object);

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function postRemove($object);

    /**
     * Return true if the Admin is related to a subject
     *
     * @return boolean
     */
    public function hasSubject();

    /**
     *
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param mixed                                      $object
     *
     * @return void
     *
     * @deprecated this feature cannot be stable, use a custom validator,
     *             the feature will be removed with Symfony 2.2
     */
    public function validate(ErrorElement $errorElement, $object);

    /**
     * @param string $context
     *
     * @return boolean
     */
    public function showIn($context);

    /**
     * Add object security, fe. make the current user owner of the object
     *
     * @param mixed $object
     */
    public function createObjectSecurity($object);

    /**
     * Returns the url defined by the $name
     *
     * @param string $name
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function getRoute($name);

    /**
     * @return AdminInterface
     */
    public function getParent();

    /**
     * @param AdminInterface $admin
     *
     * @return void
     */
    public function setParent(AdminInterface $admin);

    /**
     * @param string $name
     *
     * @return null|string
     */
    public function getTemplate($name);
}
