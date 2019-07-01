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
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
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
    public function setMenuFactory(MenuFactoryInterface $menuFactory);

    public function getMenuFactory(): MenuFactoryInterface;

    public function setFormContractor(FormContractorInterface $formContractor);

    public function setListBuilder(ListBuilderInterface $listBuilder);

    public function getListBuilder(): ListBuilderInterface;

    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder);

    public function getDatagridBuilder(): DatagridBuilderInterface;

    public function setTranslator(TranslatorInterface $translator);

    public function getTranslator(): TranslatorInterface;

    public function setRequest(Request $request);

    public function setConfigurationPool(Pool $pool);

    /**
     * Returns subjectClass/class/subclass name managed
     * - subclass name if subclass parameter is defined
     * - subject class name if subject is defined
     * - class name if not.
     */
    public function getClass(): string;

    public function attachAdminClass(FieldDescriptionInterface $fieldDescription);

    public function getDatagrid(): \Sonata\AdminBundle\Datagrid\DatagridInterface;

    /**
     * Set base controller name.
     *
     * @param string $baseControllerName
     */
    public function setBaseControllerName($baseControllerName);

    /**
     * Get base controller name.
     */
    public function getBaseControllerName(): string;

    /**
     * Sets a list of templates.
     */
    public function setTemplates(array $templates);

    /**
     * Sets a specific template.
     *
     * @param string $name
     * @param string $template
     */
    public function setTemplate($name, $template);

    /**
     * Get all templates.
     */
    public function getTemplates(): array;

    public function getModelManager(): \Sonata\AdminBundle\Model\ModelManagerInterface;

    /**
     * @return string the manager type of the admin
     */
    public function getManagerType(): string;

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
    public function getForm(): FormInterface;

    /**
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

    public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription);

    /**
     * Get parent field description.
     *
     * @return FieldDescriptionInterface The parent field description
     */
    public function getParentFieldDescription(): FieldDescriptionInterface;

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
     * @deprecated since 3.9, to be removed in 4.0
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null): string;

    /**
     * Returns the parameter representing request id, ie: id or childId.
     */
    public function getIdParameter(): string;

    /**
     * Returns true if the route $name is available.
     *
     * @param string $name
     */
    public function hasRoute($name): bool;

    public function setSecurityHandler(SecurityHandlerInterface $securityHandler);

    public function getSecurityHandler(): ?SecurityHandlerInterface;

    /**
     * @param string      $name
     * @param object|null $object
     */
    public function isGranted($name, $object = null): bool;

    /**
     * @param mixed $entity
     *
     * @return string a string representation of the identifiers for this instance
     */
    public function getNormalizedIdentifier($entity): string;

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

    public function getValidator(): ValidatorInterface;

    public function getShow(): array;

    public function setFormTheme(array $formTheme);

    public function getFormTheme(): array;

    public function setFilterTheme(array $filterTheme);

    public function getFilterTheme(): array;

    public function addExtension(AdminExtensionInterface $extension);

    /**
     * Returns an array of extension related to the current Admin.
     *
     * @return AdminExtensionInterface[]
     */
    public function getExtensions(): array;

    public function setRouteBuilder(RouteBuilderInterface $routeBuilder);

    public function getRouteBuilder(): RouteBuilderInterface;

    /**
     * @param mixed $object
     */
    public function toString($object): string;

    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy);

    public function getLabelTranslatorStrategy(): LabelTranslatorStrategyInterface;

    /**
     * Returning true will enable preview mode for
     * the target entity and show a preview button
     * when editing/creating an entity.
     */
    public function supportsPreviewMode(): bool;

    /**
     * @return mixed a new object instance
     */
    public function getNewInstance(): object;

    /**
     * @param string $uniqId
     */
    public function setUniqid($uniqId);

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
    public function getSubject(): object;

    /**
     * Returns a list FieldDescription.
     *
     * @param string $name
     */
    public function getListFieldDescription($name): FieldDescriptionInterface;

    /**
     * Returns true if the list FieldDescription exists.
     *
     * @param string $name
     */
    public function hasListFieldDescription($name): bool;

    /**
     * Returns the collection of list FieldDescriptions.
     */
    public function getListFieldDescriptions(): array;

    /**
     * Returns the array of allowed export formats.
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
     *
     * @param string $actionName
     * @param bool   $allElements
     */
    public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements);

    /**
     * Return array of filter parameters.
     */
    public function getFilterParameters(): array;

    /**
     * Return true if the Admin is related to a subject.
     */
    public function hasSubject(): bool;

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
     */
    public function showIn($context): bool;

    /**
     * Add object security, fe. make the current user owner of the object.
     *
     * @param mixed $object
     */
    public function createObjectSecurity($object);

    public function getParent(): ?self;

    public function setParent(self $admin);

    /**
     * Returns true if the Admin class has an Parent Admin defined.
     */
    public function isChild(): bool;

    /**
     * Returns template.
     *
     * @deprecated since 3.35. To be removed in 4.0. Use TemplateRegistry services instead
     *
     * @param string $name
     */
    public function getTemplate($name): ?string;

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
    public function getTranslationDomain(): string;

    /**
     * Return the form groups.
     */
    public function getFormGroups(): array;

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
     */
    public function getShowGroups(): array;

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
    public function addSubClass($subClass);

    /**
     * Sets the list of supported sub classes.
     */
    public function setSubClasses(array $subClasses);

    /**
     * Returns true if the admin has the sub classes.
     *
     * @param string $name The name of the sub class
     */
    public function hasSubClass($name): bool;

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
     * @return array the list of batchs actions
     */
    public function getBatchActions(): array;

    /**
     * Returns Admin`s label.
     */
    public function getLabel(): string;

    /**
     * Returns an array of persistent parameters.
     */
    public function getPersistentParameters(): array;

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getPersistentParameter($name);

    /**
     * Set the current child status.
     *
     * @param bool $currentChild
     */
    public function setCurrentChild($currentChild);

    /**
     * Returns the current child status.
     */
    public function getCurrentChild(): bool;

    /**
     * Get translation label using the current TranslationStrategy.
     *
     * @param string $label
     * @param string $context
     * @param string $type
     */
    public function getTranslationLabel($label, $context = '', $type = ''): string;

    public function getObjectMetadata($object): MetadataInterface;

    public function getListModes(): array;

    /**
     * @param string $mode
     */
    public function setListMode($mode);

    /**
     * return the list mode.
     */
    public function getListMode(): string;

    /*
     * Configure buttons for an action
     *
     * @param string $action
     * @param mixed  $object
     */
    public function getActionButtons($action, $object = null): array;

    /**
     * Get the list of actions that can be accessed directly from the dashboard.
     */
    public function getDashboardActions(): array;

    /**
     * Check the current request is given route or not.
     *
     * @param string $name
     * @param string $adminCode
     */
    public function isCurrentRoute($name, $adminCode = null): bool;

    /**
     * Returns the result link for an object.
     *
     * @param mixed $object
     */
    public function getSearchResultLink($object): ?string;

    /**
     * Setting to true will enable mosaic button for the admin screen.
     * Setting to false will hide mosaic button for the admin screen.
     *
     * @param bool $isShown
     */
    public function showMosaicButton($isShown);

    /**
     * Checks if a filter type is set to a default value.
     *
     * @param string $name
     */
    public function isDefaultFilter($name): bool;
}

class_exists(\Sonata\Form\Validator\ErrorElement::class);
