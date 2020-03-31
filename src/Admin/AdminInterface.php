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
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\Exporter\Source\SourceIteratorInterface;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AdminInterface extends AccessRegistryInterface, FieldDescriptionRegistryInterface, LifecycleHookProviderInterface, MenuBuilderInterface, ParentAdminInterface, UrlGeneratorInterface
{
    public function setMenuFactory(MenuFactoryInterface $menuFactory): void;

    public function getMenuFactory(): ?MenuFactoryInterface;

    public function setFormContractor(FormContractorInterface $formContractor): void;

    public function setListBuilder(ListBuilderInterface $listBuilder): void;

    public function getListBuilder(): ?ListBuilderInterface;

    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder): void;

    public function getDatagridBuilder(): ?DatagridBuilderInterface;

    public function setTranslator(TranslatorInterface $translator): void;

    public function getTranslator(): ?TranslatorInterface;

    public function setRequest(Request $request): void;

    public function setConfigurationPool(Pool $pool): void;

    /**
     * Returns subjectClass/class/subclass name managed
     * - subclass name if subclass parameter is defined
     * - subject class name if subject is defined
     * - class name if not.
     */
    public function getClass(): string;

    public function attachAdminClass(FieldDescriptionInterface $fieldDescription): void;

    public function getDatagrid(): ?DatagridInterface;

    /**
     * Set base controller name.
     */
    public function setBaseControllerName(string $baseControllerName): void;

    /**
     * Get base controller name.
     */
    public function getBaseControllerName(): string;

    /**
     * Sets a list of templates.
     */
    public function setTemplates(array $templates): void;

    /**
     * Sets a specific template.
     */
    public function setTemplate(string $name, string $template): void;

    public function getModelManager(): ?ModelManagerInterface;

    /**
     * @return string the manager type of the admin
     */
    public function getManagerType(): ?string;

    /**
     * @param string $context NEXT_MAJOR: remove this argument
     */
    public function createQuery($context = 'list'): ProxyQueryInterface;

    /**
     * @return FormBuilderInterface the form builder
     */
    public function getFormBuilder(): FormBuilderInterface;

    /**
     * Returns a form depend on the given $object.
     */
    public function getForm(): ?FormInterface;

    /**
     * NEXT MAJOR: Remove the throws tag.
     *
     * @throws \RuntimeException if no request is set
     */
    public function getRequest(): Request;

    /**
     * @return bool true if a request object is linked to this Admin, false
     *              otherwise
     */
    public function hasRequest(): bool;

    public function getCode(): string;

    public function getBaseCodeRoute(): string;

    /**
     * Return the roles and permissions per role
     * - different permissions per role for the acl handler
     * - one permission that has the same name as the role for the role handler
     * This should be used by experimented users.
     *
     * @return array 'role' => ['permission', 'permission']
     */
    public function getSecurityInformation(): array;

    public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription): void;

    /**
     * Get parent field description.
     *
     * @return FieldDescriptionInterface The parent field description
     */
    public function getParentFieldDescription(): ?FieldDescriptionInterface;

    /**
     * Returns true if the Admin is linked to a parent FieldDescription.
     */
    public function hasParentFieldDescription(): bool;

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
    public function trans($id, array $parameters = [], $domain = null, $locale = null): string;

    /**
     * Returns the parameter representing request id, ie: id or childId.
     */
    public function getIdParameter(): string;

    /**
     * Returns true if the route $name is available.
     */
    public function hasRoute(string $name): bool;

    public function setSecurityHandler(SecurityHandlerInterface $securityHandler): void;

    public function getSecurityHandler(): ?SecurityHandlerInterface;

    /**
     * @param string $name
     */
    public function isGranted($name, ?object $object = null): bool;

    /**
     * @param mixed $entity
     *
     * @return string a string representation of the identifiers for this instance
     */
    public function getNormalizedIdentifier($entity): ?string;

    /**
     * Shorthand method for templating.
     *
     * @param object $entity
     */
    public function id($entity): ?string;

    public function setValidator(ValidatorInterface $validator): void;

    public function getValidator(): ?ValidatorInterface;

    public function getShow(): ?FieldDescriptionCollection;

    public function setFormTheme(array $formTheme): void;

    /**
     * @return string[]
     */
    public function getFormTheme(): array;

    public function setFilterTheme(array $filterTheme): void;

    /**
     * @return string[]
     */
    public function getFilterTheme(): array;

    public function addExtension(AdminExtensionInterface $extension): void;

    /**
     * Returns an array of extension related to the current Admin.
     *
     * @return AdminExtensionInterface[]
     */
    public function getExtensions(): array;

    public function setRouteBuilder(RouteBuilderInterface $routeBuilder): void;

    public function getRouteBuilder(): ?RouteBuilderInterface;

    /**
     * @param object $object
     */
    public function toString($object): string;

    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy): void;

    public function getLabelTranslatorStrategy(): ?LabelTranslatorStrategyInterface;

    /**
     * Returning true will enable preview mode for
     * the target entity and show a preview button
     * when editing/creating an entity.
     */
    public function supportsPreviewMode(): bool;

    public function getNewInstance(): object;

    public function setUniqid(string $uniqId): void;

    /**
     * Returns the uniqid.
     */
    public function getUniqid(): string;

    /**
     * Returns the classname label.
     *
     * @return string the classname label
     */
    public function getClassnameLabel(): string;

    /**
     * @param mixed $id
     */
    public function getObject($id): ?object;

    public function setSubject(?object $subject): void;

    /**
     * NEXT MAJOR: return object.
     */
    public function getSubject(): ?object;

    /**
     * Returns a list FieldDescription.
     */
    public function getListFieldDescription(string $name): ?FieldDescriptionInterface;

    /**
     * Returns true if the list FieldDescription exists.
     */
    public function hasListFieldDescription(string $name): bool;

    /**
     * Returns the collection of list FieldDescriptions.
     */
    public function getListFieldDescriptions(): array;

    /**
     * Returns the array of allowed export formats.
     *
     * @return string[]
     */
    public function getExportFormats(): array;

    /**
     * Retuns a list of exported fields.
     */
    public function getExportFields(): array;

    /**
     * Returns SourceIterator.
     */
    public function getDataSourceIterator(): SourceIteratorInterface;

    /**
     * Call before the batch action, allow you to alter the query and the idx.
     */
    public function preBatchAction(string $actionName, ProxyQueryInterface $query, array &$idx, bool $allElements = false): void;

    /**
     * Return array of filter parameters.
     *
     * @return array<string, mixed>
     */
    public function getFilterParameters(): array;

    /**
     * Return true if the Admin is related to a subject.
     */
    public function hasSubject(): bool;

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @param object $object
     *
     * @deprecated this feature cannot be stable, use a custom validator,
     *             the feature will be removed with Symfony 2.2
     */
    public function validate(ErrorElement $errorElement, $object): void;

    public function showIn(string $context): bool;

    /**
     * Add object security, fe. make the current user owner of the object.
     */
    public function createObjectSecurity(object $object): void;

    /**
     * @return AdminInterface|null NEXT_MAJOR: return AdminInterface
     */
    public function getParent(): ?self;

    public function setParent(self $admin): void;

    /**
     * Returns true if the Admin class has an Parent Admin defined.
     */
    public function isChild(): bool;

    /**
     * Set the translation domain.
     *
     * @param string $translationDomain the translation domain
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
    public function getFormGroups();

    /**
     * Set the form groups.
     */
    public function setFormGroups(array $formGroups): void;

    /**
     * NEXT_MAJOR: must return only `array<string, mixed>`.
     */
    public function getFormTabs();

    public function setFormTabs(array $formTabs): void;

    /**
     * NEXT_MAJOR: must return only `array<string, mixed>`.
     */
    public function getShowTabs();

    public function setShowTabs(array $showTabs): void;

    /**
     * Remove a form group field.
     */
    public function removeFieldFromFormGroup(string $key): void;

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
     */
    public function setShowGroups(array $showGroups): void;

    /**
     * Reorder items in showGroup.
     */
    public function reorderShowGroup(string $group, array $keys): void;

    /**
     * add a FieldDescription.
     */
    public function addFormFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void;

    /**
     * Remove a FieldDescription.
     */
    public function removeFormFieldDescription(string $name): void;

    /**
     * Returns true if this admin uses ACL.
     */
    public function isAclEnabled(): bool;

    /**
     * Returns list of supported sub classes.
     */
    public function getSubClasses(): array;

    /**
     * Adds a new class to a list of supported sub classes.
     *
     * @param $subClass
     */
    public function addSubClass($subClass): void;

    /**
     * Sets the list of supported sub classes.
     */
    public function setSubClasses(array $subClasses): void;

    /**
     * Returns true if the admin has the sub classes.
     *
     * @param string $name The name of the sub class
     */
    public function hasSubClass(string $name): bool;

    /**
     * Returns true if a subclass is currently active.
     */
    public function hasActiveSubClass(): bool;

    /**
     * Returns the currently active sub class.
     *
     * @return string the active sub class
     */
    public function getActiveSubClass(): string;

    /**
     * Returns the currently active sub class code.
     *
     * @return string the code for active sub class
     */
    public function getActiveSubclassCode(): string;

    /**
     * Returns the list of batchs actions.
     *
     * @return array<string, mixed> the list of batchs actions
     */
    public function getBatchActions(): array;

    /**
     * Returns Admin`s label.
     */
    public function getLabel(): ?string;

    /**
     * Returns an array of persistent parameters.
     *
     * @return array<string, mixed>
     */
    public function getPersistentParameters(): array;

    public function getPersistentParameter(string $name);

    /**
     * Set the current child status.
     */
    public function setCurrentChild(bool $currentChild): void;

    /**
     * Returns the current child status.
     *
     * NEXT_MAJOR: Rename the function isCurrentChild()
     */
    public function getCurrentChild(): bool;

    /**
     * Get translation label using the current TranslationStrategy.
     */
    public function getTranslationLabel(string $label, string $context = '', string $type = ''): string;

    /**
     * @param object $object
     */
    public function getObjectMetadata($object): MetadataInterface;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getListModes(): array;

    public function setListMode(string $mode): void;

    /**
     * return the list mode.
     */
    public function getListMode(): string;

    /*
     * Configure buttons for an action
     */
    public function getActionButtons(string $action, ?object $object = null): array;

    /**
     * Get the list of actions that can be accessed directly from the dashboard.
     */
    public function getDashboardActions(): array;

    /**
     * Check the current request is given route or not.
     */
    public function isCurrentRoute(string $name, ?string $adminCode = null): bool;

    /**
     * Returns the result link for an object.
     */
    public function getSearchResultLink(object $object): ?string;

    /**
     * Setting to true will enable mosaic button for the admin screen.
     * Setting to false will hide mosaic button for the admin screen.
     */
    public function showMosaicButton(bool $isShown): void;

    /**
     * Checks if a filter type is set to a default value.
     */
    public function isDefaultFilter(string $name): bool;

    public function configureActionButtons(array $buttonList, string $action, ?object $object = null): array;

    /**
     * Check object existence and access, without throwing Exception.
     */
    public function canAccessObject(string $action, ?object $object = null): bool;

    /**
     * Returns the master admin.
     */
    public function getRoot(): self;

    /**
     * Returns the root code.
     */
    public function getRootCode(): string;
}

class_exists(ErrorElement::class);
