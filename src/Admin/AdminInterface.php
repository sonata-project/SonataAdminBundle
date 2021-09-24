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
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionRegistryInterface;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\Exporter\Source\SourceIteratorInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-import-type FieldDescriptionOptions from \Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface
 *
 * @phpstan-template T of object
 * @phpstan-extends AccessRegistryInterface<T>
 * @phpstan-extends UrlGeneratorInterface<T>
 * @phpstan-extends LifecycleHookProviderInterface<T>
 * @phpstan-extends TaggedAdminInterface<T>
 */
interface AdminInterface extends TaggedAdminInterface, AccessRegistryInterface, FieldDescriptionRegistryInterface, LifecycleHookProviderInterface, ParentAdminInterface, UrlGeneratorInterface
{
    /**
     * Returns subjectClass/class/subclass name managed
     * - subclass name if subclass parameter is defined
     * - subject class name if subject is defined
     * - class name if not.
     *
     * @phpstan-return class-string<T>
     */
    public function getClass(): string;

    public function attachAdminClass(FieldDescriptionInterface $fieldDescription): void;

    /**
     * @return DatagridInterface<ProxyQueryInterface>
     */
    public function getDatagrid(): DatagridInterface;

    /**
     * Set base controller name.
     */
    public function setBaseControllerName(string $baseControllerName): void;

    /**
     * Get base controller name.
     */
    public function getBaseControllerName(): string;

    public function createQuery(): ProxyQueryInterface;

    public function getFormBuilder(): FormBuilderInterface;

    /**
     * Returns a form depend on the given $object.
     */
    public function getForm(): FormInterface;

    public function setRequest(Request $request): void;

    public function getRequest(): Request;

    /**
     * Returns true if a request object is linked to this Admin, false otherwise.
     */
    public function hasRequest(): bool;

    public function getCode(): string;

    public function getBaseCodeRoute(): string;

    public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription): void;

    /**
     * @throws \LogicException if there is no parent field description
     */
    public function getParentFieldDescription(): FieldDescriptionInterface;

    /**
     * Returns true if the Admin is linked to a parent FieldDescription.
     */
    public function hasParentFieldDescription(): bool;

    /**
     * Returns the parameter representing request id, ie: id or childId.
     */
    public function getIdParameter(): string;

    /**
     * Returns true if the route $name is available.
     */
    public function hasRoute(string $name): bool;

    /**
     * @param string|string[] $name
     *
     * @phpstan-param T|null $object
     */
    public function isGranted($name, ?object $object = null): bool;

    /**
     * Returns a string representation of the identifiers for this instance.
     *
     * @phpstan-param T $model
     */
    public function getNormalizedIdentifier(object $model): ?string;

    /**
     * Shorthand method for templating.
     *
     * @phpstan-param T $model
     */
    public function id(object $model): ?string;

    /**
     * @return FieldDescriptionCollection<FieldDescriptionInterface>
     */
    public function getShow(): FieldDescriptionCollection;

    /**
     * @return FieldDescriptionCollection<FieldDescriptionInterface>
     */
    public function getList(): FieldDescriptionCollection;

    /**
     * @param string[] $formTheme
     */
    public function setFormTheme(array $formTheme): void;

    /**
     * @return string[]
     */
    public function getFormTheme(): array;

    /**
     * @param string[] $filterTheme
     */
    public function setFilterTheme(array $filterTheme): void;

    /**
     * @return string[]
     */
    public function getFilterTheme(): array;

    /**
     * @phpstan-param AdminExtensionInterface<T> $extension
     */
    public function addExtension(AdminExtensionInterface $extension): void;

    /**
     * Returns an array of extension related to the current Admin.
     *
     * @return AdminExtensionInterface[]
     *
     * @phpstan-return array<AdminExtensionInterface<T>>
     */
    public function getExtensions(): array;

    /**
     * @phpstan-param T $object
     */
    public function toString(object $object): string;

    /**
     * Returning true will enable preview mode for
     * the target entity and show a preview button
     * when editing/creating an entity.
     */
    public function supportsPreviewMode(): bool;

    /**
     * @phpstan-return T
     */
    public function getNewInstance(): object;

    public function setUniqId(string $uniqId): void;

    public function getUniqId(): string;

    public function getClassnameLabel(): string;

    /**
     * @param string|int|null $id
     *
     * @phpstan-return T|null
     */
    public function getObject($id): ?object;

    /**
     * @phpstan-param T|null $subject
     */
    public function setSubject(?object $subject): void;

    /**
     * @phpstan-return T
     */
    public function getSubject(): object;

    /**
     * Return true if the Admin is related to a subject.
     */
    public function hasSubject(): bool;

    /**
     * Returns the array of allowed export formats.
     *
     * @return string[]
     */
    public function getExportFormats(): array;

    /**
     * Retuns a list of exported fields.
     *
     * @return string[]
     */
    public function getExportFields(): array;

    public function getDataSourceIterator(): SourceIteratorInterface;

    /**
     * Call before the batch action, allow you to alter the query and the idx.
     *
     * @param mixed[] $idx
     */
    public function preBatchAction(string $actionName, ProxyQueryInterface $query, array &$idx, bool $allElements = false): void;

    /**
     * Return array of default filter parameters.
     *
     * @return array<string, mixed>
     */
    public function getDefaultFilterParameters(): array;

    /**
     * Return array of filter parameters.
     *
     * @return array<string, mixed>
     */
    public function getFilterParameters(): array;

    public function showIn(string $context): bool;

    /**
     * Add object security, fe. make the current user owner of the object.
     *
     * @phpstan-param T $object
     */
    public function createObjectSecurity(object $object): void;

    /**
     * @return AdminInterface<object>
     */
    public function getParent(): self;

    /**
     * @param AdminInterface<object> $parent
     */
    public function setParent(self $parent, string $parentAssociationMapping): void;

    /**
     * Returns true if the Admin class has an Parent Admin defined.
     */
    public function isChild(): bool;

    public function setTranslationDomain(string $translationDomain): void;

    public function getTranslationDomain(): string;

    /**
     * @return array<string, mixed>
     */
    public function getFormGroups(): array;

    /**
     * @param array<string, mixed> $formGroups
     */
    public function setFormGroups(array $formGroups): void;

    public function removeFieldFromFormGroup(string $key): void;

    /**
     * @param string[] $keys
     */
    public function reorderFormGroup(string $group, array $keys): void;

    /**
     * @return array<string, mixed>
     */
    public function getFormTabs(): array;

    /**
     * @param array<string, mixed> $formTabs
     */
    public function setFormTabs(array $formTabs): void;

    /**
     * @return array<string, mixed>
     */
    public function getShowTabs(): array;

    /**
     * @param array<string, mixed> $showTabs
     */
    public function setShowTabs(array $showTabs): void;

    /**
     * Returns the show groups.
     *
     * @return array<string, mixed>
     */
    public function getShowGroups(): array;

    /**
     * Set the show groups.
     *
     * @param array<string, mixed> $showGroups
     */
    public function setShowGroups(array $showGroups): void;

    public function removeFieldFromShowGroup(string $key): void;

    /**
     * Reorder items in showGroup.
     *
     * @param string[] $keys
     */
    public function reorderShowGroup(string $group, array $keys): void;

    /**
     * Returns true if this admin uses ACL.
     */
    public function isAclEnabled(): bool;

    /**
     * Returns list of supported sub classes.
     *
     * @return array<string, string>
     *
     * @phpstan-return array<string, class-string<T>>
     */
    public function getSubClasses(): array;

    /**
     * Sets the list of supported sub classes.
     *
     * @param array<string, string> $subClasses
     *
     * @phpstan-param array<string, class-string<T>> $subClasses
     */
    public function setSubClasses(array $subClasses): void;

    /**
     * Returns true if the admin has the sub classes.
     */
    public function hasSubClass(string $name): bool;

    /**
     * Returns true if a subclass is currently active.
     */
    public function hasActiveSubClass(): bool;

    /**
     * Returns the currently active sub class.
     *
     * @phpstan-return class-string
     */
    public function getActiveSubClass(): string;

    /**
     * Returns the currently active sub class code.
     */
    public function getActiveSubclassCode(): string;

    /**
     * Returns the list of batchs actions.
     *
     * @return array<string, array<string, mixed>> the list of batchs actions
     */
    public function getBatchActions(): array;

    /**
     * Returns an array of persistent parameters.
     *
     * @return array<string, mixed>
     */
    public function getPersistentParameters(): array;

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getPersistentParameter(string $name, $default = null);

    /**
     * Set the current child status.
     */
    public function setCurrentChild(bool $currentChild): void;

    /**
     * Returns the current child status.
     */
    public function isCurrentChild(): bool;

    /**
     * Get translation label using the current TranslationStrategy.
     */
    public function getTranslationLabel(string $label, string $context = '', string $type = ''): string;

    /**
     * @phpstan-param T $object
     */
    public function getObjectMetadata(object $object): MetadataInterface;

    public function setListMode(string $mode): void;

    /**
     * return the list mode.
     */
    public function getListMode(): string;

    /**
     * Configure buttons for an action.
     *
     * @phpstan-param T|null $object
     *
     * @return array<string, array<string, mixed>>
     */
    public function getActionButtons(string $action, ?object $object = null): array;

    /**
     * Get the list of actions that can be accessed directly from the dashboard.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getDashboardActions(): array;

    /**
     * Check the current request is given route or not.
     */
    public function isCurrentRoute(string $name, ?string $adminCode = null): bool;

    /**
     * Returns the master admin.
     *
     * @return AdminInterface<object>
     */
    public function getRoot(): self;

    /**
     * Returns the root code.
     */
    public function getRootCode(): string;

    /**
     * Returns the baseRoutePattern used to generate the routing information.
     *
     * @throws \RuntimeException if a default baseRoutePattern is required for the admin class
     */
    public function getBaseRoutePattern(): string;

    /**
     * Returns the baseRouteName used to generate the routing information.
     *
     * @throws \RuntimeException if a default baseRouteName is required for the admin class
     */
    public function getBaseRouteName(): string;

    /**
     * @param AdminInterface<object>|null $childAdmin
     */
    public function getSideMenu(string $action, ?self $childAdmin = null): ItemInterface;

    /**
     * Returns the name of the parent related field, so the field can be use to set the default
     * value (ie the parent object) or to filter the object.
     */
    public function getParentAssociationMapping(): ?string;

    /**
     * Returns the current child admin instance.
     *
     * @return AdminInterface<object>
     */
    public function getCurrentChildAdmin(): ?self;

    /**
     * This method is being called by the main admin class and the child class,
     * the getFormBuilder is only call by the main admin class.
     */
    public function defineFormBuilder(FormBuilderInterface $formBuilder): void;

    /**
     * @phpstan-param FieldDescriptionOptions $options
     */
    public function createFieldDescription(string $propertyName, array $options = []): FieldDescriptionInterface;
}
