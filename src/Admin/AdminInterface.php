<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Knp\Menu\FactoryInterface as MenuFactoryInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AdminInterface extends AccessRegistryInterface, FieldDescriptionRegistryInterface, LifecycleHookProviderInterface, MenuBuilderInterface, ParentAdminInterface, UrlGeneratorInterface
{
    public function setMenuFactory(MenuFactoryInterface $menuFactory);

    /**
     * @return MenuFactoryInterface
     */
    public function getMenuFactory();

    public function setFormContractor(FormContractorInterface $formContractor);

    public function setListBuilder(ListBuilderInterface $listBuilder);

    /**
     * @return ListBuilderInterface
     */
    public function getListBuilder();

    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder);

    /**
     * @return DatagridBuilderInterface
     */
    public function getDatagridBuilder();

    public function setTranslator(TranslatorInterface $translator);

    /**
     * @return TranslatorInterface
     */
    public function getTranslator();

    public function setRequest(Request $request);

    public function setConfigurationPool(Pool $pool);

    /**
     * Returns subjectClass/class/subclass name managed
     * - subclass name if subclass parameter is defined
     * - subject class name if subject is defined
     * - class name if not.
     *
     * @return string
     */
    public function getClass();

    public function attachAdminClass(FieldDescriptionInterface $fieldDescription);

    /**
     * @return \Sonata\AdminBundle\Datagrid\DatagridInterface
     */
    public function getDatagrid();

    /**
     * Set base controller name.
     *
     * @param string $baseControllerName
     */
    public function setBaseControllerName($baseControllerName);

    /**
     * Get base controller name.
     *
     * @return string
     */
    public function getBaseControllerName();

    /**
     * @return \Sonata\AdminBundle\Model\ModelManagerInterface
     */
    public function getModelManager();

    /**
     * @return string the manager type of the admin
     */
    public function getManagerType();

    /**
     * @param string $context NEXT_MAJOR: remove this argument
     *
     * @return ProxyQueryInterface
     */
    public function createQuery($context = 'list');

    /**
     * @return FormBuilderInterface the form builder
     */
    public function getFormBuilder();

    /**
     * Returns a form depend on the given $object.
     *
     * @return Form
     */
    public function getForm();

    /**
     * @throws \RuntimeException if no request is set
     *
     * @return Request
     */
    public function getRequest();

    /**
     * @return bool true if a request object is linked to this Admin, false
     *              otherwise
     */
    public function hasRequest();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getBaseCodeRoute();

    /**
     * Return the roles and permissions per role
     * - different permissions per role for the acl handler
     * - one permission that has the same name as the role for the role handler
     * This should be used by experimented users.
     *
     * @return array 'role' => ['permission', 'permission']
     */
    public function getSecurityInformation();

    public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription);

    /**
     * Get parent field description.
     *
     * @return FieldDescriptionInterface The parent field description
     */
    public function getParentFieldDescription();

    /**
     * Returns true if the Admin is linked to a parent FieldDescription.
     *
     * @return bool
     */
    public function hasParentFieldDescription();

    /**
     * translate a message id.
     *
     * NEXT_MAJOR: remove this method
     *
     * @param string      $id
     * @param string|null $domain
     * @param string|null $locale
     *
     * @return string the translated string
     *
     * @deprecated since 3.9, to be removed in 4.0
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null);

    /**
     * Returns the parameter representing request id, ie: id or childId.
     *
     * @return string
     */
    public function getIdParameter();

    /**
     * Returns true if the route $name is available.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasRoute($name);

    /**
     * Check the current request is given route or not.
     *
     * TODO: uncomment this method before releasing 4.0
     *
     * ```
     * $this->isCurrentRoute('create'); // is create page?
     * $this->isCurrentRoute('edit', 'some.admin.code'); // is some.admin.code admin's edit page?
     * ```
     *
     * @param string $name
     * @param string $adminCode
     *
     * @return bool
     */
    // public function isCurrentRoute($name, $adminCode = null);

    public function setSecurityHandler(SecurityHandlerInterface $securityHandler);

    /**
     * @return SecurityHandlerInterface|null
     */
    public function getSecurityHandler();

    /**
     * @param string      $name
     * @param object|null $object
     *
     * @return bool
     */
    public function isGranted($name, $object = null);

    /**
     * @param mixed $entity
     *
     * @return string a string representation of the identifiers for this instance
     */
    public function getNormalizedIdentifier($entity);

    /**
     * Shorthand method for templating.
     *
     * @param object $entity
     *
     * @return mixed
     */
    public function id($entity);

    /**
     * @param ValidatorInterface $validator
     */
    public function setValidator($validator);

    /**
     * @return ValidatorInterface
     */
    public function getValidator();

    /**
     * @return array
     */
    public function getShow();

    public function setFormTheme(array $formTheme);

    /**
     * @return array
     */
    public function getFormTheme();

    public function setFilterTheme(array $filterTheme);

    /**
     * @return array
     */
    public function getFilterTheme();

    public function addExtension(AdminExtensionInterface $extension);

    /**
     * Returns an array of extension related to the current Admin.
     *
     * @return AdminExtensionInterface[]
     */
    public function getExtensions();

    public function setRouteBuilder(RouteBuilderInterface $routeBuilder);

    /**
     * @return RouteBuilderInterface
     */
    public function getRouteBuilder();

    /**
     * @param mixed $object
     *
     * @return string
     */
    public function toString($object);

    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy);

    /**
     * @return LabelTranslatorStrategyInterface
     */
    public function getLabelTranslatorStrategy();

    /**
     * Returning true will enable preview mode for
     * the target entity and show a preview button
     * when editing/creating an entity.
     *
     * @return bool
     */
    public function supportsPreviewMode();

    /**
     * @return mixed a new object instance
     */
    public function getNewInstance();

    /**
     * @param string $uniqId
     */
    public function setUniqid($uniqId);

    /**
     * Returns the uniqid.
     *
     * @return int
     */
    public function getUniqid();

    /**
     * @param mixed $id
     *
     * @return mixed
     */
    public function getObject($id);

    /**
     * @param object $subject
     */
    public function setSubject($subject);

    /**
     * @return mixed
     */
    public function getSubject();

    /**
     * Returns a list FieldDescription.
     *
     * @param string $name
     *
     * @return FieldDescriptionInterface
     */
    public function getListFieldDescription($name);

    /**
     * Returns true if the list FieldDescription exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasListFieldDescription($name);

    /**
     * Returns the collection of list FieldDescriptions.
     *
     * @return array
     */
    public function getListFieldDescriptions();

    /**
     * Returns the array of allowed export formats.
     *
     * @return array
     */
    public function getExportFormats();

    /**
     * Returns SourceIterator.
     *
     * @return \Exporter\Source\SourceIteratorInterface
     */
    public function getDataSourceIterator();

    public function configure();

    /**
     * Call before the batch action, allow you to alter the query and the idx.
     *
     * @param string $actionName
     * @param array  $idx
     * @param bool   $allElements
     */
    public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements);

    /**
     * Return array of filter parameters.
     *
     * @return array
     */
    public function getFilterParameters();

    /**
     * Return true if the Admin is related to a subject.
     *
     * @return bool
     */
    public function hasSubject();

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @param mixed $object
     *
     * @deprecated this feature cannot be stable, use a custom validator,
     *             the feature will be removed with Symfony 2.2
     */
    public function validate(ErrorElement $errorElement, $object);

    /**
     * @param string $context
     *
     * @return bool
     */
    public function showIn($context);

    /**
     * Add object security, fe. make the current user owner of the object.
     *
     * @param mixed $object
     */
    public function createObjectSecurity($object);

    /**
     * @return AdminInterface
     */
    public function getParent();

    public function setParent(self $admin);

    /**
     * Returns true if the Admin class has an Parent Admin defined.
     *
     * @return bool
     */
    public function isChild();

    /**
     * Returns template.
     *
     * @deprecated since 3.35. To be removed in 4.0. Use TemplateRegistry services instead
     *
     * @param string $name
     *
     * @return null|string
     */
    public function getTemplate($name);

    /**
     * Set the translation domain.
     *
     * @param string $translationDomain the translation domain
     */
    public function setTranslationDomain($translationDomain);

    /**
     * Returns the translation domain.
     *
     * @return string the translation domain
     */
    public function getTranslationDomain();

    /**
     * Return the form groups.
     *
     * @return array
     */
    public function getFormGroups();

    /**
     * Set the form groups.
     */
    public function setFormGroups(array $formGroups);

    public function getFormTabs();

    public function setFormTabs(array $formTabs);

    public function getShowTabs();

    public function setShowTabs(array $showTabs);

    /**
     * Remove a form group field.
     *
     * @param string $key
     */
    public function removeFieldFromFormGroup($key);

    /**
     * Returns the show groups.
     *
     * @return array
     */
    public function getShowGroups();

    /**
     * Set the show groups.
     */
    public function setShowGroups(array $showGroups);

    /**
     * Reorder items in showGroup.
     *
     * @param string $group
     */
    public function reorderShowGroup($group, array $keys);

    /**
     * add a FieldDescription.
     *
     * @param string $name
     */
    public function addFormFieldDescription($name, FieldDescriptionInterface $fieldDescription);

    /**
     * Remove a FieldDescription.
     *
     * @param string $name
     */
    public function removeFormFieldDescription($name);

    /**
     * Returns true if this admin uses ACL.
     *
     * @return bool
     */
    public function isAclEnabled();

    /**
     * Sets the list of supported sub classes.
     */
    public function setSubClasses(array $subClasses);

    /**
     * Returns true if the admin has the sub classes.
     *
     * @param string $name The name of the sub class
     *
     * @return bool
     */
    public function hasSubClass($name);

    /**
     * Returns true if a subclass is currently active.
     *
     * @return bool
     */
    public function hasActiveSubClass();

    /**
     * Returns the currently active sub class.
     *
     * @return string the active sub class
     */
    public function getActiveSubClass();

    /**
     * Returns the currently active sub class code.
     *
     * @return string the code for active sub class
     */
    public function getActiveSubclassCode();

    /**
     * Returns the list of batchs actions.
     *
     * @return array the list of batchs actions
     */
    public function getBatchActions();

    /**
     * Returns Admin`s label.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Returns an array of persistent parameters.
     *
     * @return array
     */
    public function getPersistentParameters();

    /**
     * NEXT_MAJOR: remove this signature
     * Get breadcrumbs for $action.
     *
     * @param string $action
     *
     * @return mixed array|Traversable
     */
    public function getBreadcrumbs($action);

    /**
     * Set the current child status.
     *
     * @param bool $currentChild
     */
    public function setCurrentChild($currentChild);

    /**
     * Returns the current child status.
     *
     * @return bool
     */
    public function getCurrentChild();

    /**
     * Get translation label using the current TranslationStrategy.
     *
     * @param string $label
     * @param string $context
     * @param string $type
     *
     * @return string
     */
    public function getTranslationLabel($label, $context = '', $type = '');

    /**
     * @param $object
     *
     * @return Metadata
     */
    public function getObjectMetadata($object);

    /**
     * @return array
     */
    public function getListModes();

    /**
     * @param string $mode
     */
    public function setListMode($mode);

    /**
     * return the list mode.
     *
     * @return string
     */
    public function getListMode();

    /*
     * Configure buttons for an action
     *
     * @param string $action
     * @param object $object
     *
     */
    // public function configureActionButtons($action, $object = null);

    //TODO: uncomment this method for 4.0
    /*
     * Returns the result link for an object.
     *
     * @param mixed $object
     *
     * @return string|null
     */
    //public function getSearchResultLink($object)

//    TODO: uncomment this method in 4.0
//    /**
//     * Setting to true will enable mosaic button for the admin screen.
//     * Setting to false will hide mosaic button for the admin screen.
//     *
//     * @param bool $isShown
//     */
//    public function showMosaicButton($isShown);

    /*
     * Checks if a filter type is set to a default value
     *
     * @param string $name
     *
     * @return bool
     */
//    NEXT_MAJOR: uncomment this method in 4.0
    // public function isDefaultFilter($name);
}
