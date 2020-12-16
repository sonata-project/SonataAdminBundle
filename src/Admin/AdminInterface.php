<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryAwareInterface;
use Sonata\Exporter\Source\SourceIteratorInterface;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * NEXT_MAJOR: Add all these methods to the interface by uncommenting them.
 *
 * @method array                           configureActionButtons(string $action, ?object $object = null)
 * @method string                          getSearchResultLink(object $object)
 * @method bool                            isCurrentRoute(string $name, ?string $adminCode)
 * @method bool                            canAccessObject(string $action, object $object)
 * @method mixed                           getPersistentParameter(string $name)
 * @method string[]                        getExportFields()
 * @method array                           getSubClasses()
 * @method AdminInterface                  getRoot()
 * @method string                          getRootCode()
 * @method array                           getActionButtons(string $action, ?object $object)
 * @method FieldDescriptionCollection|null getList()
 * @method string                          getBaseRoutePattern()
 * @method string                          getBaseRouteName()
 * @method ItemInterface                   getSideMenu(string $action, ?AdminInterface $childAdmin = null)
 * @method void                            addParentAssociationMapping(string $code, string $value)
 * @method string                          getClassnameLabel()
 * @method AdminInterface|null             getCurrentChildAdmin()
 * @method string|null                     getParentAssociationMapping()
 * @method void                            reorderFormGroup(string $group, array $keys)
 * @method void                            defineFormBuilder(FormBuilderInterface $formBuilder)
 *
 * @phpstan-template T of object
 * @phpstan-extends TaggedAdminInterface<T>
 * @phpstan-extends AccessRegistryInterface<T>
 * @phpstan-extends UrlGeneratorInterface<T>
 * @phpstan-extends LifecycleHookProviderInterface<T>
 */
interface AdminInterface extends TaggedAdminInterface, AccessRegistryInterface, FieldDescriptionRegistryInterface, LifecycleHookProviderInterface, MenuBuilderInterface, ParentAdminInterface, UrlGeneratorInterface, MutableTemplateRegistryAwareInterface
{
    /**
     * Returns subjectClass/class/subclass name managed
     * - subclass name if subclass parameter is defined
     * - subject class name if subject is defined
     * - class name if not.
     *
     * @return string
     *
     * @phpstan-return class-string<T>
     */
    public function getClass();

    /**
     * @return void
     */
    public function attachAdminClass(FieldDescriptionInterface $fieldDescription);

    /**
     * @return DatagridInterface
     */
    public function getDatagrid();

    /**
     * Set base controller name.
     *
     * @param string $baseControllerName
     *
     * @return void
     */
    public function setBaseControllerName($baseControllerName);

    /**
     * Get base controller name.
     *
     * @return string
     */
    public function getBaseControllerName();

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
     * @return FormInterface
     */
    public function getForm();

    /**
     * @return void
     */
    public function setRequest(Request $request);

    /**
     * NEXT MAJOR: Remove the throws tag.
     *
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
     * @return void
     */
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
     * @deprecated since sonata-project/admin-bundle 3.9, to be removed in 4.0
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
     * @param string|array $name
     * @param object|null  $object
     *
     * @return bool
     *
     * @phpstan-param T|null $object
     */
    public function isGranted($name, $object = null);

    /**
     * @param object $model
     *
     * @return string a string representation of the identifiers for this instance
     *
     * @phpstan-param T $model
     */
    public function getNormalizedIdentifier($model);

    /**
     * Shorthand method for templating.
     *
     * @param object $model
     *
     * @return mixed
     *
     * @phpstan-param T $model
     */
    public function id($model);

    /**
     * @return FieldDescriptionCollection|null
     */
    public function getShow();

//    NEXT_MAJOR: uncomment this method in 4.0
//    public function getList(): ?FieldDescriptionCollection;

    /**
     * @return void
     */
    public function setFormTheme(array $formTheme);

    /**
     * @return string[]
     */
    public function getFormTheme();

    /**
     * @param string[] $filterTheme
     *
     * @return void
     */
    public function setFilterTheme(array $filterTheme);

    /**
     * @return string[]
     */
    public function getFilterTheme();

    /**
     * @return void
     */
    public function addExtension(AdminExtensionInterface $extension);

    /**
     * Returns an array of extension related to the current Admin.
     *
     * @return AdminExtensionInterface[]
     */
    public function getExtensions();

    /**
     * @param object|null $object NEXT_MAJOR: Use `object` as type declaration for argument 1
     *
     * @return string
     *
     * @phpstan-param T $object
     */
    public function toString($object);

    /**
     * Returning true will enable preview mode for
     * the target entity and show a preview button
     * when editing/creating an entity.
     *
     * @return bool
     */
    public function supportsPreviewMode();

    /**
     * @return object a new object instance
     *
     * @phpstan-return T
     */
    public function getNewInstance();

    /**
     * @param string $uniqId
     *
     * @return void
     */
    public function setUniqid($uniqId);

    /**
     * Returns the uniqid.
     *
     * @return string
     */
    public function getUniqid();

    /**
     * @param mixed $id
     *
     * @return object|null
     *
     * @phpstan-return T|null
     */
    public function getObject($id);

    /**
     * @param object|null $subject
     *
     * @return void
     *
     * @phpstan-param T|null $subject
     */
    public function setSubject($subject);

    /**
     * NEXT MAJOR: return object.
     *
     * @return object|null
     *
     * @phpstan-return T|null
     */
    public function getSubject();

    /**
     * NEXT_MAJOR: Remove this method, since it's already in FieldDescriptionRegistryInterface.
     *
     * Returns a list FieldDescription.
     *
     * @param string $name
     *
     * @return FieldDescriptionInterface|null // NEXT_MAJOR: Return FieldDescriptionInterface
     */
    public function getListFieldDescription($name);

    /**
     * NEXT_MAJOR: Remove this method, since it's already in FieldDescriptionRegistryInterface.
     *
     * Returns true if the list FieldDescription exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasListFieldDescription($name);

    /**
     * NEXT_MAJOR: Remove this method, since it's already in FieldDescriptionRegistryInterface.
     *
     * Returns the collection of list FieldDescriptions.
     *
     * @return array<string, FieldDescriptionInterface>
     */
    public function getListFieldDescriptions();

    /**
     * Returns the array of allowed export formats.
     *
     * @return string[]
     */
    public function getExportFormats();

    /**
     * Returns SourceIterator.
     *
     * @return SourceIteratorInterface
     */
    public function getDataSourceIterator();

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-admin/admin-bundle 3.x
     *
     * @return void
     */
    public function configure();

    /**
     * Call before the batch action, allow you to alter the query and the idx.
     *
     * @param string $actionName
     * @param bool   $allElements
     */
    public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements);

    /**
     * Return array of filter parameters.
     *
     * @return array<string, mixed>
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
     * @param object $object
     *
     * @return void
     *
     * @deprecated since sonata-project/admin-bundle 3.82.
     *
     * @phpstan-param T $object
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
     * @param object $object
     *
     * @phpstan-param T $object
     */
    public function createObjectSecurity($object);

    /**
     * @return AdminInterface|null NEXT_MAJOR: return AdminInterface
     */
    public function getParent();

    /**
     * @return void
     */
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
     * @deprecated since sonata-project/admin-bundle 3.35. To be removed in 4.0. Use TemplateRegistry services instead
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getTemplate($name);

    /**
     * Set the translation domain.
     *
     * @param string $translationDomain the translation domain
     *
     * @return void
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
     * NEXT_MAJOR: must return only `array<string, mixed>`.
     *
     * @return array<string, mixed>|false (false if the groups have not been initialized)
     */
    public function getFormGroups();

    /**
     * Set the form groups.
     *
     * @param array<string, mixed> $formGroups
     */
    public function setFormGroups(array $formGroups);

    /**
     * NEXT_MAJOR: must return only `array<string, mixed>`.
     *
     * @return array<string, mixed>|false
     */
    public function getFormTabs();

    /**
     * @param array<string, mixed> $formTabs
     *
     * @return void
     */
    public function setFormTabs(array $formTabs);

    /**
     * NEXT_MAJOR: must return only `array<string, mixed>`.
     *
     * @return array<string, mixed>|false
     */
    public function getShowTabs();

    /**
     * @param array<string, mixed> $showTabs
     *
     * @return void
     */
    public function setShowTabs(array $showTabs);

    /**
     * Remove a form group field.
     *
     * @param string $key
     *
     * @return void
     */
    public function removeFieldFromFormGroup($key);

    /**
     * Returns the show groups.
     *
     * NEXT_MAJOR: must return only `array<string, mixed>`.
     *
     * @return array<string, mixed>|false (false if the groups have not been initialized)
     */
    public function getShowGroups();

    /**
     * Set the show groups.
     *
     * @param array<string, mixed> $showGroups
     *
     * @return void
     */
    public function setShowGroups(array $showGroups);

    /**
     * Reorder items in showGroup.
     *
     * @param string   $group
     * @param string[] $keys
     *
     * @return void
     */
    public function reorderShowGroup($group, array $keys);

    /**
     * NEXT_MAJOR: Remove this method, since it's already in FieldDescriptionRegistryInterface.
     *
     * add a FieldDescription.
     *
     * @param string $name
     */
    public function addFormFieldDescription($name, FieldDescriptionInterface $fieldDescription);

    /**
     * NEXT_MAJOR: Remove this method, since it's already in FieldDescriptionRegistryInterface.
     *
     * Remove a FieldDescription.
     *
     * @param string $name
     *
     * @return void
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
     *
     * @param string[] $subClasses
     *
     * @return void
     *
     * @phpstan-param array<class-string<T>> $subClasses
     */
    public function setSubClasses(array $subClasses);

    /**
     * Returns true if the admin has the sub classes.
     *
     * @param string $name The name of the sub class
     *
     * @return bool
     *
     * @phpstan-param class-string $name
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
     *
     * @phpstan-return class-string
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
     * @return array<string, mixed> the list of batchs actions
     */
    public function getBatchActions();

    /**
     * Returns an array of persistent parameters.
     *
     * @return array<string, mixed>
     */
    public function getPersistentParameters();

    /**
     * NEXT_MAJOR: remove this signature
     * Get breadcrumbs for $action.
     *
     * @param string $action
     *
     * @return ItemInterface[]
     */
    public function getBreadcrumbs($action);

    /**
     * Set the current child status.
     *
     * @param bool $currentChild
     *
     * @return void
     */
    public function setCurrentChild($currentChild);

    /**
     * Returns the current child status.
     *
     * NEXT_MAJOR: Rename the function isCurrentChild()
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
     * @param object $object
     *
     * @return MetadataInterface
     *
     * @phpstan-param T $object
     */
    public function getObjectMetadata($object);

    /**
     * Check the current request is given route or not.
     *
     * NEXT_MAJOR: uncomment this method
     *
     * ```
     * $this->isCurrentRoute('create'); // is create page?
     * $this->isCurrentRoute('edit', 'some.admin.code'); // is some.admin.code admin's edit page?
     * ```
     */
    // public function isCurrentRoute(string $name, ?string $adminCode = null): bool;

    /**
     * @param string $mode
     *
     * @return void
     */
    public function setListMode($mode);

    /**
     * @return string
     */
    public function getListMode();

    /*
     * Configure buttons for an action
     */
    // public function configureActionButtons(string $action, ?object $object = null): array;

    // NEXT_MAJOR: uncomment this method for 4.0
    /*
     * Returns the result link for an object.
     */
    //public function getSearchResultLink(object $object): ?string

//    NEXT_MAJOR: uncomment this method in 4.0
//    /**
//     * Returns the baseRoutePattern used to generate the routing information.
//     */
//    public function getBaseRoutePattern(): string;

//    NEXT_MAJOR: uncomment this method in 4.0
//    /**
//     * Returns the baseRouteName used to generate the routing information.
//     */
//    public function getBaseRouteName(): string;

//    NEXT_MAJOR: uncomment this method in 4.0
//    public function getSideMenu(string $action, ?AdminInterface $childAdmin = null): \Knp\Menu\ItemInterface;

//    NEXT_MAJOR: uncomment this method in 4.0
//    public function addParentAssociationMapping(string $code, string $value): void;

//    NEXT_MAJOR: uncomment this method in 4.0
//    /**
//     * Returns the classname label.
//     */
//    public function getClassnameLabel(): string;

//    NEXT_MAJOR: uncomment this method in 4.0
//    /**
//     * Returns the current child admin instance.
//     */
//    public function getCurrentChildAdmin(): ?AdminInterface;

//    NEXT_MAJOR: uncomment this method in 4.0
//    /**
//     * Returns the name of the parent related field, so the field can be use to set the default
//     * value (ie the parent object) or to filter the object.
//     *
//     */
//    public function getParentAssociationMapping(): ?string;

//    NEXT_MAJOR: uncomment this method in 4.0
//    public function reorderFormGroup(string $group, array $keys): void;

//    NEXT_MAJOR: uncomment this method in 4.0
//    /**
//     * This method is being called by the main admin class and the child class,
//     * the getFormBuilder is only call by the main admin class.
//     */
//    public function defineFormBuilder(FormBuilderInterface $formBuilder): void;
}

class_exists(\Sonata\Form\Validator\ErrorElement::class);
