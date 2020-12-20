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

use Knp\Menu\FactoryInterface as MenuFactoryInterface;
use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exporter\DataSourceInterface;
use Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryAwareInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\Exporter\Source\SourceIteratorInterface as SourceIteratorInterface;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @method array                           configureActionButtons(string $action, ?object $object = null)
 * @method string                          getSearchResultLink(object $object)
 * @method void                            showMosaicButton(bool $isShown)
 * @method bool                            isDefaultFilter(string $name)                                         // NEXT_MAJOR: Remove this
 * @method bool                            isCurrentRoute(string $name, ?string $adminCode)
 * @method bool                            canAccessObject(string $action, object $object)
 * @method mixed                           getPersistentParameter(string $name)
 * @method string[]                        getExportFields()
 * @method array                           getSubClasses()
 * @method AdminInterface                  getRoot()
 * @method string                          getRootCode()
 * @method array                           getActionButtons(string $action, ?object $object)
 * @method FieldDescriptionCollection|null getList()
 * @method void                            setFilterPersister(?FilterPersisterInterface $filterPersister = null)
 * @method string                          getBaseRoutePattern()
 * @method string                          getBaseRouteName()
 * @method ItemInterface                   getSideMenu(string $action, ?AdminInterface $childAdmin = null)
 * @method void                            addParentAssociationMapping(string $code, string $value)
 * @method RouteGeneratorInterface         getRouteGenerator()
 * @method string                          getClassnameLabel()
 * @method AdminInterface|null             getCurrentChildAdmin()
 * @method string|null                     getParentAssociationMapping()
 * @method void                            reorderFormGroup(string $group, array $keys)
 * @method void                            defineFormBuilder(FormBuilderInterface $formBuilder)
 * @method string                          getPagerType()
 * @method DataSourceInterface|null        getDataSource()
 *
 * @phpstan-template T of object
 * @phpstan-extends AccessRegistryInterface<T>
 * @phpstan-extends UrlGeneratorInterface<T>
 * @phpstan-extends LifecycleHookProviderInterface<T>
 */
interface AdminInterface extends AccessRegistryInterface, FieldDescriptionRegistryInterface, LifecycleHookProviderInterface, MenuBuilderInterface, ParentAdminInterface, UrlGeneratorInterface, MutableTemplateRegistryAwareInterface
{
    /**
     * @param MenuFactoryInterface $menuFactory
     *
     * @return void
     */
    public function setMenuFactory(MenuFactoryInterface $menuFactory): void;

    /**
     * @return MenuFactoryInterface
     */
    public function getMenuFactory(): MenuFactoryInterface;

    /**
     * @param FormContractorInterface $formContractor
     *
     * @return void
     */
    public function setFormContractor(FormContractorInterface $formContractor): void;

    /**
     * @param ListBuilderInterface $listBuilder
     *
     * @return void
     */
    public function setListBuilder(ListBuilderInterface $listBuilder): void;

    /**
     * @return ListBuilderInterface
     */
    public function getListBuilder(): ListBuilderInterface;

    /**
     * @param DatagridBuilderInterface $datagridBuilder
     *
     * @return void
     */
    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder): void;

    /**
     * @return DatagridBuilderInterface
     */
    public function getDatagridBuilder(): DatagridBuilderInterface;

    /**
     * @param TranslatorInterface $translator
     *
     * @return void
     */
    public function setTranslator(TranslatorInterface $translator): void;

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface;

    /**
     * @param Request $request
     *
     * @return void
     */
    public function setRequest(Request $request): void;

    /**
     * @param Pool $pool
     *
     * @return void
     */
    public function setConfigurationPool(Pool $pool): void;

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
    public function getClass(): string;

    /**
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return void
     */
    public function attachAdminClass(FieldDescriptionInterface $fieldDescription): void;

    // NEXT_MAJOR: uncomment this method in 4.0
    //public function getPagerType(): string;

    /**
     * @return DatagridInterface
     */
    public function getDatagrid(): DatagridInterface;

    /**
     * Set base controller name.
     *
     * @param string $baseControllerName
     *
     * @return void
     */
    public function setBaseControllerName(string $baseControllerName): void;

    /**
     * Get base controller name.
     *
     * @return string
     */
    public function getBaseControllerName(): string;

    /**
     * @return ModelManagerInterface
     */
    public function getModelManager(): ModelManagerInterface;

    // NEXT_MAJOR: Uncomment the next line.
    // public function getDataSource(): DataSourceInterface;

    /**
     * @return string the manager type of the admin
     */
    public function getManagerType(): string;

    /**
     * @param string $context NEXT_MAJOR: remove this argument
     *
     * @return ProxyQueryInterface
     */
    public function createQuery(string $context = 'list'): ProxyQueryInterface;

    /**
     * @return FormBuilderInterface the form builder
     */
    public function getFormBuilder(): FormBuilderInterface;

    /**
     * Returns a form depend on the given $object.
     *
     * @return FormInterface
     */
    public function getForm(): FormInterface;

    /**
     * NEXT MAJOR: Remove the throws tag.
     *
     * @throws \RuntimeException if no request is set
     *
     * @return Request
     */
    public function getRequest(): Request;

    /**
     * @return bool true if a request object is linked to this Admin, false
     *              otherwise
     */
    public function hasRequest(): bool;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return string
     */
    public function getBaseCodeRoute(): string;

    /**
     * Return the roles and permissions per role
     * - different permissions per role for the acl handler
     * - one permission that has the same name as the role for the role handler
     * This should be used by experimented users.
     *
     * @return array<string, string[]> 'role' => ['permission', 'permission']
     */
    public function getSecurityInformation(): array;

    /**
     * @param FieldDescriptionInterface $parentFieldDescription
     *
     * @return void
     */
    public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription): void;

    /**
     * Get parent field description.
     *
     * @return FieldDescriptionInterface|null The parent field description
     */
    public function getParentFieldDescription(): ?FieldDescriptionInterface;

    /**
     * Returns true if the Admin is linked to a parent FieldDescription.
     *
     * @return bool
     */
    public function hasParentFieldDescription(): bool;

    /**
     * translate a message id.
     *
     * NEXT_MAJOR: remove this method
     *
     * @param string      $id
     * @param array       $parameters
     * @param string|null $domain
     * @param string|null $locale
     *
     * @return string the translated string
     *
     * @deprecated since sonata-project/admin-bundle 3.9, to be removed in 4.0
     */
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string;

    /**
     * Returns the parameter representing request id, ie: id or childId.
     *
     * @return string
     */
    public function getIdParameter(): string;

    /**
     * Returns true if the route $name is available.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasRoute(string $name): bool;

    /**
     * @param SecurityHandlerInterface $securityHandler
     *
     * @return void
     */
    public function setSecurityHandler(SecurityHandlerInterface $securityHandler): void;

    /**
     * @return SecurityHandlerInterface|null
     */
    public function getSecurityHandler(): ?SecurityHandlerInterface;

    /**
     * @param string|array $name
     * @param object|null  $object
     *
     * @return bool
     *
     * @phpstan-param T|null $object
     */
    public function isGranted($name, ?object $object = null): bool;

    /**
     * @param object $model
     *
     * @return string a string representation of the identifiers for this instance
     *
     * @phpstan-param T $model
     */
    public function getNormalizedIdentifier(object $model): string;

    /**
     * Shorthand method for templating.
     *
     * @param object $model
     *
     * @return mixed
     *
     * @phpstan-param T $model
     */
    public function id(object $model);

    /**
     * @param ValidatorInterface $validator
     *
     * @return void
     */
    public function setValidator(ValidatorInterface $validator): void;

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface;

    /**
     * @return FieldDescriptionCollection|null
     */
    public function getShow(): ?FieldDescriptionCollection;

//    NEXT_MAJOR: uncomment this method in 4.0
//    public function getList(): ?FieldDescriptionCollection;

    /**
     * @param array $formTheme
     *
     * @return void
     */
    public function setFormTheme(array $formTheme): void;

    /**
     * @return string[]
     */
    public function getFormTheme(): array;

    /**
     * @param string[] $filterTheme
     *
     * @return void
     */
    public function setFilterTheme(array $filterTheme): void;

    /**
     * @return string[]
     */
    public function getFilterTheme(): array;

    /**
     * @param AdminExtensionInterface $extension
     *
     * @return void
     */
    public function addExtension(AdminExtensionInterface $extension): void;

    /**
     * Returns an array of extension related to the current Admin.
     *
     * @return AdminExtensionInterface[]
     */
    public function getExtensions(): array;

    /**
     * @param RouteBuilderInterface $routeBuilder
     *
     * @return void
     */
    public function setRouteBuilder(RouteBuilderInterface $routeBuilder): void;

    /**
     * @return RouteBuilderInterface
     */
    public function getRouteBuilder(): RouteBuilderInterface;

    /**
     * @param object|null $object NEXT_MAJOR: Use `object` as type declaration for argument 1
     *
     * @return string
     *
     * @phpstan-param T $object
     */
    public function toString(?object $object): string;

    /**
     * @param LabelTranslatorStrategyInterface $labelTranslatorStrategy
     *
     * @return void
     */
    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy): void;

    /**
     * @return LabelTranslatorStrategyInterface
     */
    public function getLabelTranslatorStrategy(): LabelTranslatorStrategyInterface;

    /**
     * Returning true will enable preview mode for
     * the target entity and show a preview button
     * when editing/creating an entity.
     *
     * @return bool
     */
    public function supportsPreviewMode(): bool;

    /**
     * @return object a new object instance
     *
     * @phpstan-return T
     */
    public function getNewInstance(): object;

    /**
     * @param string $uniqId
     *
     * @return void
     */
    public function setUniqid(string $uniqId): void;

    /**
     * Returns the uniqid.
     *
     * @return string
     */
    public function getUniqid(): string;

    /**
     * @param mixed $id
     *
     * @return object|null
     *
     * @phpstan-return T|null
     */
    public function getObject($id): ?object;

    /**
     * @param object|null $subject
     *
     * @return void
     *
     * @phpstan-param T|null $subject
     */
    public function setSubject(?object $subject): void;

    /**
     * NEXT MAJOR: return object.
     *
     * @return object|null
     *
     * @phpstan-return T|null
     */
    public function getSubject(): ?object;

    /**
     * NEXT_MAJOR: Remove this method, since it's already in FieldDescriptionRegistryInterface.
     *
     * Returns a list FieldDescription.
     *
     * @param string $name
     *
     * @return FieldDescriptionInterface|null // NEXT_MAJOR: Return FieldDescriptionInterface
     */
    public function getListFieldDescription(string $name): ?FieldDescriptionInterface;

    /**
     * NEXT_MAJOR: Remove this method, since it's already in FieldDescriptionRegistryInterface.
     *
     * Returns true if the list FieldDescription exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasListFieldDescription(string $name): bool;

    /**
     * NEXT_MAJOR: Remove this method, since it's already in FieldDescriptionRegistryInterface.
     *
     * Returns the collection of list FieldDescriptions.
     *
     * @return array<string, FieldDescriptionInterface>
     */
    public function getListFieldDescriptions(): array;

    /**
     * Returns the array of allowed export formats.
     *
     * @return string[]
     */
    public function getExportFormats(): array;

    /**
     * Returns SourceIterator.
     *
     * @return SourceIteratorInterface
     */
    public function getDataSourceIterator(): SourceIteratorInterface;

    /**
     * @return void
     */
    public function configure(): void;

    /**
     * Call before the batch action, allow you to alter the query and the idx.
     *
     * @param string              $actionName
     * @param ProxyQueryInterface $query
     * @param array               $idx
     * @param bool                $allElements
     */
    public function preBatchAction(string $actionName, ProxyQueryInterface $query, array &$idx, bool $allElements);

    /**
     * Return array of filter parameters.
     *
     * @return array<string, mixed>
     */
    public function getFilterParameters(): array;

    /**
     * Return true if the Admin is related to a subject.
     *
     * @return bool
     */
    public function hasSubject(): bool;

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @param object $object
     *
     * @return void
     *
     * @deprecated since sonata-project/admin-bundle 3.x.
     *
     * @phpstan-param T $object
     */
    public function validate(ErrorElement $errorElement, object $object): void;

    /**
     * @param string $context
     *
     * @return bool
     */
    public function showIn(string $context): bool;

    /**
     * Add object security, fe. make the current user owner of the object.
     *
     * @param object $object
     *
     * @phpstan-param T $object
     */
    public function createObjectSecurity(object $object);

    /**
     * @return AdminInterface|null NEXT_MAJOR: return AdminInterface
     */
    public function getParent(): ?AdminInterface;

    /**
     * @param AdminInterface $admin
     *
     * @return void
     */
    public function setParent(self $admin): void;

    /**
     * Returns true if the Admin class has an Parent Admin defined.
     *
     * @return bool
     */
    public function isChild(): bool;

    /**
     * Returns template.
     *
     * @deprecated since sonata-project/admin-bundle 3.35. To be removed in 4.0. Use TemplateRegistry services instead
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getTemplate(string $name): ?string;

    /**
     * Set the translation domain.
     *
     * @param string $translationDomain the translation domain
     *
     * @return void
     */
    public function setTranslationDomain(string $translationDomain): void;

    /**
     * Returns the translation domain.
     *
     * @return string the translation domain
     */
    public function getTranslationDomain(): string;

    /**
     * Return the form groups.
     *
     * NEXT_MAJOR: must return only `array<string, mixed>`.
     *
     * @return array<string, mixed>|false (false if the groups have not been initialized)
     */
    public function getFormGroups(): array;

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
    public function getFormTabs(): array;

    /**
     * @param array<string, mixed> $formTabs
     *
     * @return void
     */
    public function setFormTabs(array $formTabs): void;

    /**
     * NEXT_MAJOR: must return only `array<string, mixed>`.
     *
     * @return array<string, mixed>|false
     */
    public function getShowTabs(): array;

    /**
     * @param array<string, mixed> $showTabs
     *
     * @return void
     */
    public function setShowTabs(array $showTabs): void;

    /**
     * Remove a form group field.
     *
     * @param string $key
     *
     * @return void
     */
    public function removeFieldFromFormGroup(string $key): void;

    /**
     * Returns the show groups.
     *
     * NEXT_MAJOR: must return only `array<string, mixed>`.
     *
     * @return array<string, mixed>|false (false if the groups have not been initialized)
     */
    public function getShowGroups(): array;

    /**
     * Set the show groups.
     *
     * @param array<string, mixed> $showGroups
     *
     * @return void
     */
    public function setShowGroups(array $showGroups): void;

    /**
     * Reorder items in showGroup.
     *
     * @param string   $group
     * @param string[] $keys
     *
     * @return void
     */
    public function reorderShowGroup(string $group, array $keys): void;

    /**
     * NEXT_MAJOR: Remove this method, since it's already in FieldDescriptionRegistryInterface.
     *
     * add a FieldDescription.
     *
     * @param string                    $name
     * @param FieldDescriptionInterface $fieldDescription
     */
    public function addFormFieldDescription(string $name, FieldDescriptionInterface $fieldDescription);

    /**
     * NEXT_MAJOR: Remove this method, since it's already in FieldDescriptionRegistryInterface.
     *
     * Remove a FieldDescription.
     *
     * @param string $name
     *
     * @return void
     */
    public function removeFormFieldDescription(string $name): void;

    /**
     * Returns true if this admin uses ACL.
     *
     * @return bool
     */
    public function isAclEnabled(): bool;

    /**
     * Sets the list of supported sub classes.
     *
     * @param string[] $subClasses
     *
     * @return void
     *
     * @phpstan-param array<class-string<T>> $subClasses
     */
    public function setSubClasses(array $subClasses): void;

    /**
     * Returns true if the admin has the sub classes.
     *
     * @param string $name The name of the sub class
     *
     * @return bool
     *
     * @phpstan-param class-string $name
     */
    public function hasSubClass(string $name): bool;

    /**
     * Returns true if a subclass is currently active.
     *
     * @return bool
     */
    public function hasActiveSubClass(): bool;

    /**
     * Returns the currently active sub class.
     *
     * @return string|null the active sub class
     *
     * @phpstan-return class-string
     */
    public function getActiveSubClass(): ?string;

    /**
     * Returns the currently active sub class code.
     *
     * @return string|null the code for active sub class
     */
    public function getActiveSubclassCode(): ?string;

    /**
     * Returns the list of batchs actions.
     *
     * @return array<string, mixed> the list of batchs actions
     */
    public function getBatchActions(): array;

    /**
     * Returns Admin`s label.
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Returns an array of persistent parameters.
     *
     * @return array<string, mixed>
     */
    public function getPersistentParameters(): array;

    /**
     * NEXT_MAJOR: remove this signature
     * Get breadcrumbs for $action.
     *
     * @param string $action
     *
     * @return ItemInterface[]
     */
    public function getBreadcrumbs(string $action): array;

    /**
     * Set the current child status.
     *
     * @param bool $currentChild
     *
     * @return void
     */
    public function setCurrentChild(bool $currentChild): void;

    /**
     * Returns the current child status.
     *
     * NEXT_MAJOR: Rename the function isCurrentChild()
     *
     * @return bool
     */
    public function getCurrentChild(): bool;

    /**
     * Get translation label using the current TranslationStrategy.
     *
     * @param string $label
     * @param string $context
     * @param string $type
     *
     * @return string
     */
    public function getTranslationLabel(string $label, string $context = '', string $type = ''): string;

    /**
     * @param object $object
     *
     * @return MetadataInterface
     *
     * @phpstan-param T $object
     */
    public function getObjectMetadata(object $object): MetadataInterface;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getListModes(): array;

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
    public function setListMode(string $mode): void;

    /**
     * @return string
     */
    public function getListMode(): string;

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
//     * Setting to true will enable mosaic button for the admin screen.
//     * Setting to false will hide mosaic button for the admin screen.
//     */
//    public function showMosaicButton(bool $isShown): void;

//    NEXT_MAJOR: uncomment this method in 4.0
//    public function setFilterPersister(?\Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface\FilterPersisterInterface $filterPersister = null): void;

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
//    public function getRouteGenerator(): \Sonata\AdminBundle\Route\RouteGeneratorInterface;

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
