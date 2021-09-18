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

use Doctrine\Common\Util\ClassUtils;
use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\DependencyInjection\Admin\AbstractTaggedAdmin;
use Sonata\AdminBundle\Exception\AdminClassNotFoundException;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Manipulator\ObjectManipulator;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Util\Instantiator;
use Sonata\AdminBundle\Util\ParametersManipulator;
use Sonata\Exporter\Source\SourceIteratorInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as RoutingUrlGeneratorInterface;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 * @phpstan-extends AbstractTaggedAdmin<T>
 * @phpstan-implements AdminInterface<T>
 */
abstract class AbstractAdmin extends AbstractTaggedAdmin implements AdminInterface, DomainObjectInterface, AdminTreeInterface
{
    public const CONTEXT_MENU = 'menu';
    public const CONTEXT_DASHBOARD = 'dashboard';

    public const CLASS_REGEX =
        '@
        (?:([A-Za-z0-9]*)\\\)?        # vendor name / app name
        (Bundle\\\)?                  # optional bundle directory
        ([A-Za-z0-9]+?)(?:Bundle)?\\\ # bundle name, with optional suffix
        (
            Entity|Document|Model|PHPCR|CouchDocument|Phpcr|
            Doctrine\\\Orm|Doctrine\\\Phpcr|Doctrine\\\MongoDB|Doctrine\\\CouchDB
        )\\\(.*)@x';

    private const ACTION_TREE = 1;
    private const ACTION_SHOW = 2;
    private const ACTION_EDIT = 4;
    private const ACTION_DELETE = 8;
    private const ACTION_ACL = 16;
    private const ACTION_HISTORY = 32;
    private const ACTION_LIST = 64;
    private const ACTION_BATCH = 128;
    private const INTERNAL_ACTIONS = [
        'tree' => self::ACTION_TREE,
        'show' => self::ACTION_SHOW,
        'edit' => self::ACTION_EDIT,
        'delete' => self::ACTION_DELETE,
        'acl' => self::ACTION_ACL,
        'history' => self::ACTION_HISTORY,
        'list' => self::ACTION_LIST,
        'batch' => self::ACTION_BATCH,
    ];
    private const MASK_OF_ACTION_CREATE = self::ACTION_TREE | self::ACTION_SHOW | self::ACTION_EDIT | self::ACTION_DELETE | self::ACTION_LIST | self::ACTION_BATCH;
    private const MASK_OF_ACTION_SHOW = self::ACTION_EDIT | self::ACTION_HISTORY | self::ACTION_ACL;
    private const MASK_OF_ACTION_EDIT = self::ACTION_SHOW | self::ACTION_DELETE | self::ACTION_ACL | self::ACTION_HISTORY;
    private const MASK_OF_ACTION_HISTORY = self::ACTION_SHOW | self::ACTION_EDIT | self::ACTION_ACL;
    private const MASK_OF_ACTION_ACL = self::ACTION_EDIT | self::ACTION_HISTORY;
    private const MASK_OF_ACTION_LIST = self::ACTION_SHOW | self::ACTION_EDIT | self::ACTION_DELETE | self::ACTION_ACL | self::ACTION_BATCH;
    private const MASK_OF_ACTIONS_USING_OBJECT = self::MASK_OF_ACTION_SHOW | self::MASK_OF_ACTION_EDIT | self::MASK_OF_ACTION_HISTORY | self::MASK_OF_ACTION_ACL;

    private const DEFAULT_LIST_PER_PAGE_RESULTS = 25;
    private const DEFAULT_LIST_PER_PAGE_OPTIONS = [10, 25, 50, 100, 250];

    /**
     * The base route name used to generate the routing information.
     *
     * @var string|null
     */
    protected $baseRouteName;

    /**
     * The base route pattern used to generate the routing information.
     *
     * @var string|null
     */
    protected $baseRoutePattern;

    /**
     * The label class name  (used in the title/breadcrumb ...).
     *
     * @var string|null
     */
    protected $classnameLabel;

    /**
     * Setting to true will enable preview mode for
     * the entity and show a preview button in the
     * edit/create forms.
     *
     * @var bool
     */
    protected $supportsPreviewMode = false;

    /**
     * The list FieldDescription constructed from the configureListField method.
     *
     * @var array<string, FieldDescriptionInterface>
     */
    private $listFieldDescriptions = [];

    /**
     * The show FieldDescription constructed from the configureShowFields method.
     *
     * @var FieldDescriptionInterface[]
     */
    private $showFieldDescriptions = [];

    /**
     * The list FieldDescription constructed from the configureFormField method.
     *
     * @var FieldDescriptionInterface[]
     */
    private $formFieldDescriptions = [];

    /**
     * The filter FieldDescription constructed from the configureFilterField method.
     *
     * @var FieldDescriptionInterface[]
     */
    private $filterFieldDescriptions = [];

    /**
     * The maximum number of page numbers to display in the list.
     *
     * @var int
     */
    private $maxPageLinks = 25;

    /**
     * The translation domain to be used to translate messages.
     *
     * @var string
     */
    private $translationDomain = 'messages';

    /**
     * Array of routes related to this admin.
     *
     * @var RouteCollectionInterface|null
     */
    private $routes;

    /**
     * The subject only set in edit/update/create mode.
     *
     * @var object|null
     *
     * @phpstan-var T|null
     */
    private $subject;

    /**
     * Define a Collection of child admin, ie /admin/order/{id}/order-element/{childId}.
     *
     * @var array<string, AdminInterface<object>>
     */
    private $children = [];

    /**
     * Reference the parent admin.
     *
     * @var AdminInterface<object>|null
     */
    private $parent;

    /**
     * Reference the parent FieldDescription related to this admin
     * only set for FieldDescription which is associated to an Sub Admin instance.
     *
     * @var FieldDescriptionInterface|null
     */
    private $parentFieldDescription;

    /**
     * If true then the current admin is part of the nested admin set (from the url).
     *
     * @var bool
     */
    private $currentChild = false;

    /**
     * The uniqId is used to avoid clashing with 2 admin related to the code
     * ie: a Block linked to a Block.
     *
     * @var string|null
     */
    private $uniqId;

    /**
     * The current request object.
     *
     * @var Request|null
     */
    private $request;

    /**
     * The datagrid instance.
     *
     * @var DatagridInterface<ProxyQueryInterface>|null
     */
    private $datagrid;

    /**
     * @var ItemInterface|null
     */
    private $menu;

    /**
     * @var string[]
     */
    private $formTheme = [];

    /**
     * @var string[]
     */
    private $filterTheme = [];

    /**
     * @var AdminExtensionInterface[]
     * @phpstan-var array<AdminExtensionInterface<T>>
     */
    private $extensions = [];

    /**
     * @var array<string, bool>
     */
    private $cacheIsGranted = [];

    /**
     * @var array<string, string>
     */
    private $parentAssociationMapping = [];

    /**
     * The subclasses supported by the admin class.
     *
     * @var string[]
     * @phpstan-var array<string, class-string<T>>
     */
    private $subClasses = [];

    /**
     * The list collection.
     *
     * @var FieldDescriptionCollection<FieldDescriptionInterface>|null
     */
    private $list;

    /**
     * @var FieldDescriptionCollection<FieldDescriptionInterface>|null
     */
    private $show;

    /**
     * @var FormInterface|null
     */
    private $form;

    /**
     * The cached base route name.
     *
     * @var string|null
     */
    private $cachedBaseRouteName;

    /**
     * The cached base route pattern.
     *
     * @var string|null
     */
    private $cachedBaseRoutePattern;

    /**
     * The form group disposition.
     *
     * @var array<string, mixed>
     */
    private $formGroups = [];

    /**
     * The form tabs disposition.
     *
     * @var array<string, mixed>
     */
    private $formTabs = [];

    /**
     * The view group disposition.
     *
     * @var array<string, mixed>
     */
    private $showGroups = [];

    /**
     * The view tab disposition.
     *
     * @var array<string, mixed>
     */
    private $showTabs = [];

    /**
     * @var array<string, bool>
     */
    private $loaded = [
        'routes' => false,
        'tab_menu' => false,
        'show' => false,
        'list' => false,
        'form' => false,
        'datagrid' => false,
    ];

    public function getExportFormats(): array
    {
        return [
            'json', 'xml', 'csv', 'xls',
        ];
    }

    final public function getExportFields(): array
    {
        $fields = $this->configureExportFields();

        foreach ($this->getExtensions() as $extension) {
            $fields = $extension->configureExportFields($this, $fields);
        }

        return $fields;
    }

    final public function getDataSourceIterator(): SourceIteratorInterface
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $fields = [];

        foreach ($this->getExportFields() as $key => $field) {
            if (!\is_string($key)) {
                $label = $this->getTranslationLabel($field, 'export', 'label');
                $key = $this->getTranslator()->trans($label, [], $this->getTranslationDomain());
            }

            $fields[$key] = $field;
        }

        $query = $datagrid->getQuery();

        return $this->getDataSource()->createIterator($query, $fields);
    }

    final public function initialize(): void
    {
        if (null === $this->classnameLabel) {
            $namespaceSeparatorPos = strrpos($this->getClass(), '\\');
            $this->classnameLabel = false !== $namespaceSeparatorPos
                ? substr($this->getClass(), $namespaceSeparatorPos + 1)
                : $this->getClass();
        }

        $this->configure();

        foreach ($this->getExtensions() as $extension) {
            $extension->configure($this);
        }
    }

    final public function update(object $object): object
    {
        $this->preUpdate($object);
        foreach ($this->getExtensions() as $extension) {
            $extension->preUpdate($this, $object);
        }

        $this->getModelManager()->update($object);

        $this->postUpdate($object);
        foreach ($this->getExtensions() as $extension) {
            $extension->postUpdate($this, $object);
        }

        return $object;
    }

    final public function create(object $object): object
    {
        $this->prePersist($object);
        foreach ($this->getExtensions() as $extension) {
            $extension->prePersist($this, $object);
        }

        $this->getModelManager()->create($object);

        $this->postPersist($object);
        foreach ($this->getExtensions() as $extension) {
            $extension->postPersist($this, $object);
        }

        $this->createObjectSecurity($object);

        return $object;
    }

    final public function delete(object $object): void
    {
        $this->preRemove($object);
        foreach ($this->getExtensions() as $extension) {
            $extension->preRemove($this, $object);
        }

        $this->getSecurityHandler()->deleteObjectSecurity($this, $object);
        $this->getModelManager()->delete($object);

        $this->postRemove($object);
        foreach ($this->getExtensions() as $extension) {
            $extension->postRemove($this, $object);
        }
    }

    public function preBatchAction(string $actionName, ProxyQueryInterface $query, array &$idx, bool $allElements = false): void
    {
    }

    final public function getDefaultFilterParameters(): array
    {
        return array_merge(
            $this->getDefaultSortValues(),
            $this->getDefaultFilterValues()
        );
    }

    final public function getFilterParameters(): array
    {
        $parameters = $this->getDefaultFilterParameters();

        // build the values array
        if ($this->hasRequest()) {
            /** @var InputBag|ParameterBag $bag */
            $bag = $this->getRequest()->query;
            if ($bag instanceof InputBag) {
                // symfony 5.1+
                $filters = $bag->all('filter');
            } else {
                $filters = $bag->get('filter', []);
            }
            if (isset($filters[DatagridInterface::PAGE])) {
                $filters[DatagridInterface::PAGE] = (int) $filters[DatagridInterface::PAGE];
            }
            if (isset($filters[DatagridInterface::PER_PAGE])) {
                $filters[DatagridInterface::PER_PAGE] = (int) $filters[DatagridInterface::PER_PAGE];
            }

            // if filter persistence is configured
            if ($this->hasFilterPersister()) {
                // if reset filters is asked, remove from storage
                if ('reset' === $this->getRequest()->query->get('filters')) {
                    $this->getFilterPersister()->reset($this->getCode());
                }

                // if no filters, fetch from storage
                // otherwise save to storage
                if ([] === $filters) {
                    $filters = $this->getFilterPersister()->get($this->getCode());
                } else {
                    $this->getFilterPersister()->set($this->getCode(), $filters);
                }
            }

            $parameters = ParametersManipulator::merge($parameters, $filters);

            // always force the parent value
            if ($this->isChild()) {
                $parentAssociationMapping = $this->getParentAssociationMapping();
                if (null !== $parentAssociationMapping) {
                    $name = str_replace('.', '__', $parentAssociationMapping);
                    $parameters[$name] = ['value' => $this->getRequest()->get($this->getParent()->getIdParameter())];
                }
            }
        }

        if (
            !isset($parameters[DatagridInterface::PER_PAGE])
            || !\is_int($parameters[DatagridInterface::PER_PAGE])
            || !$this->determinedPerPageValue($parameters[DatagridInterface::PER_PAGE])
        ) {
            $parameters[DatagridInterface::PER_PAGE] = $this->getMaxPerPage();
        }

        $parameters = $this->configureFilterParameters($parameters);

        foreach ($this->getExtensions() as $extension) {
            $parameters = $extension->configureFilterParameters($this, $parameters);
        }

        return $parameters;
    }

    /**
     * Returns the name of the parent related field, so the field can be use to set the default
     * value (ie the parent object) or to filter the object.
     *
     * @throws \LogicException
     */
    final public function getParentAssociationMapping(): ?string
    {
        if (!$this->isChild()) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no parent.',
                static::class
            ));
        }

        $parent = $this->getParent()->getCode();

        return $this->parentAssociationMapping[$parent];
    }

    final public function getBaseRoutePattern(): string
    {
        if (null !== $this->cachedBaseRoutePattern) {
            return $this->cachedBaseRoutePattern;
        }

        if ($this->isChild()) { // the admin class is a child, prefix it with the parent route pattern
            $baseRoutePattern = $this->baseRoutePattern;
            if (null === $baseRoutePattern) {
                preg_match(self::CLASS_REGEX, $this->class, $matches);

                if (!$matches) {
                    throw new \LogicException(sprintf(
                        'Please define a default `baseRoutePattern` value for the admin class `%s`',
                        static::class
                    ));
                }
                $baseRoutePattern = $this->urlize($matches[5], '-');
            }

            $this->cachedBaseRoutePattern = sprintf(
                '%s/%s/%s',
                $this->getParent()->getBaseRoutePattern(),
                $this->getParent()->getRouterIdParameter(),
                $baseRoutePattern
            );
        } elseif (null !== $this->baseRoutePattern) {
            $this->cachedBaseRoutePattern = $this->baseRoutePattern;
        } else {
            preg_match(self::CLASS_REGEX, $this->class, $matches);

            if (!$matches) {
                throw new \LogicException(sprintf(
                    'Please define a default `baseRoutePattern` value for the admin class `%s`',
                    static::class
                ));
            }

            $this->cachedBaseRoutePattern = sprintf(
                '/%s%s/%s',
                '' === $matches[1] ? '' : $this->urlize($matches[1], '-').'/',
                $this->urlize($matches[3], '-'),
                $this->urlize($matches[5], '-')
            );
        }

        return $this->cachedBaseRoutePattern;
    }

    /**
     * Returns the baseRouteName used to generate the routing information.
     *
     * @return string the baseRouteName used to generate the routing information
     */
    final public function getBaseRouteName(): string
    {
        if (null !== $this->cachedBaseRouteName) {
            return $this->cachedBaseRouteName;
        }

        if ($this->isChild()) { // the admin class is a child, prefix it with the parent route name
            $baseRouteName = $this->baseRouteName;
            if (null === $baseRouteName) {
                preg_match(self::CLASS_REGEX, $this->class, $matches);

                if (!$matches) {
                    throw new \LogicException(sprintf(
                        'Cannot automatically determine base route name,'
                        .' please define a default `baseRouteName` value for the admin class `%s`',
                        static::class
                    ));
                }
                $baseRouteName = $this->urlize($matches[5]);
            }

            $this->cachedBaseRouteName = sprintf(
                '%s_%s',
                $this->getParent()->getBaseRouteName(),
                $baseRouteName
            );
        } elseif (null !== $this->baseRouteName) {
            $this->cachedBaseRouteName = $this->baseRouteName;
        } else {
            preg_match(self::CLASS_REGEX, $this->class, $matches);

            if (!$matches) {
                throw new \LogicException(sprintf(
                    'Cannot automatically determine base route name,'
                    .' please define a default `baseRouteName` value for the admin class `%s`',
                    static::class
                ));
            }

            $this->cachedBaseRouteName = sprintf(
                'admin_%s%s_%s',
                '' === $matches[1] ? '' : $this->urlize($matches[1]).'_',
                $this->urlize($matches[3]),
                $this->urlize($matches[5])
            );
        }

        return $this->cachedBaseRouteName;
    }

    final public function getClass(): string
    {
        if ($this->hasActiveSubClass()) {
            if ($this->hasParentFieldDescription()) {
                throw new \LogicException('Feature not implemented: an embedded admin cannot have subclass');
            }

            $subClass = $this->getRequest()->query->get('subclass');
            \assert(\is_string($subClass));

            if (!$this->hasSubClass($subClass)) {
                throw new \LogicException(sprintf('Subclass "%s" is not defined.', $subClass));
            }

            return $this->getSubClass($subClass);
        }

        // Do not use `$this->hasSubject()` and `$this->getSubject()` here to avoid infinite loop.
        // `getSubject` use `hasSubject()` which use `getObject()` which use `getClass()`.
        if (null !== $this->subject) {
            /** @phpstan-var class-string<T> $class */
            $class = ClassUtils::getClass($this->subject);

            return $class;
        }

        return $this->class;
    }

    final public function getSubClasses(): array
    {
        return $this->subClasses;
    }

    final public function setSubClasses(array $subClasses): void
    {
        $this->subClasses = $subClasses;
    }

    final public function hasSubClass(string $name): bool
    {
        return isset($this->subClasses[$name]);
    }

    final public function hasActiveSubClass(): bool
    {
        if (\count($this->subClasses) > 0 && $this->hasRequest()) {
            return \is_string($this->getRequest()->query->get('subclass'));
        }

        return false;
    }

    final public function getActiveSubClass(): string
    {
        if (!$this->hasActiveSubClass()) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no active subclass.',
                static::class
            ));
        }

        return $this->getSubClass($this->getActiveSubclassCode());
    }

    final public function getActiveSubclassCode(): string
    {
        if (!$this->hasActiveSubClass()) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no active subclass.',
                static::class
            ));
        }

        $subClass = (string) $this->getRequest()->query->get('subclass');

        if (!$this->hasSubClass($subClass)) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no active subclass.',
                static::class
            ));
        }

        return $subClass;
    }

    final public function getBatchActions(): array
    {
        $actions = [];

        if ($this->hasRoute('delete') && $this->hasAccess('delete')) {
            $actions['delete'] = [
                'label' => 'action_delete',
                'translation_domain' => 'SonataAdminBundle',
                'ask_confirmation' => true, // by default always true
            ];
        }

        $actions = $this->configureBatchActions($actions);

        foreach ($this->getExtensions() as $extension) {
            $actions = $extension->configureBatchActions($this, $actions);
        }

        foreach ($actions as $name => &$action) {
            if (!\array_key_exists('label', $action)) {
                $action['label'] = $this->getTranslationLabel($name, 'batch', 'label');
            }

            if (!\array_key_exists('translation_domain', $action)) {
                $action['translation_domain'] = $this->getTranslationDomain();
            }
        }

        return $actions;
    }

    final public function getRoutes(): RouteCollectionInterface
    {
        $routes = $this->buildRoutes();
        if (null === $routes) {
            throw new \LogicException('Cannot access routes during the building process.');
        }

        return $routes;
    }

    public function getRouterIdParameter(): string
    {
        return sprintf('{%s}', $this->getIdParameter());
    }

    public function getIdParameter(): string
    {
        $parameter = 'id';

        for ($i = 0; $i < $this->getChildDepth(); ++$i) {
            $parameter = sprintf('child%s', ucfirst($parameter));
        }

        return $parameter;
    }

    final public function hasRoute(string $name): bool
    {
        return $this->getRouteGenerator()->hasAdminRoute($this, $name);
    }

    final public function isCurrentRoute(string $name, ?string $adminCode = null): bool
    {
        if (!$this->hasRequest()) {
            return false;
        }

        $request = $this->getRequest();
        $route = $request->get('_route');

        if (null !== $adminCode) {
            $pool = $this->getConfigurationPool();

            if ($pool->hasAdminByAdminCode($adminCode)) {
                $admin = $pool->getAdminByAdminCode($adminCode);
            } else {
                return false;
            }
        } else {
            $admin = $this;
        }

        return $admin->getRoutes()->getRouteName($name) === $route;
    }

    final public function generateObjectUrl(string $name, object $object, array $parameters = [], int $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        $parameters[$this->getIdParameter()] = $this->getUrlSafeIdentifier($object);

        return $this->generateUrl($name, $parameters, $referenceType);
    }

    final public function generateUrl(string $name, array $parameters = [], int $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->getRouteGenerator()->generateUrl($this, $name, $parameters, $referenceType);
    }

    final public function generateMenuUrl(string $name, array $parameters = [], int $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH): array
    {
        return $this->getRouteGenerator()->generateMenuUrl($this, $name, $parameters, $referenceType);
    }

    final public function getNewInstance(): object
    {
        $object = $this->createNewInstance();

        $this->appendParentObject($object);
        $this->alterNewInstance($object);

        foreach ($this->getExtensions() as $extension) {
            $extension->alterNewInstance($this, $object);
        }

        return $object;
    }

    final public function getFormBuilder(): FormBuilderInterface
    {
        $formBuilder = $this->getFormContractor()->getFormBuilder(
            $this->getUniqId(),
            ['data_class' => $this->getClass()] + $this->getFormOptions(),
        );

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    /**
     * This method is being called by the main admin class and the child class,
     * the getFormBuilder is only call by the main admin class.
     */
    final public function defineFormBuilder(FormBuilderInterface $formBuilder): void
    {
        if (!$this->hasSubject()) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no subject.',
                static::class
            ));
        }

        $mapper = new FormMapper($this->getFormContractor(), $formBuilder, $this);

        $this->configureFormFields($mapper);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureFormFields($mapper);
        }
    }

    final public function attachAdminClass(FieldDescriptionInterface $fieldDescription): void
    {
        $pool = $this->getConfigurationPool();

        try {
            $admin = $pool->getAdminByFieldDescription($fieldDescription);
        } catch (AdminClassNotFoundException $exception) {
            // Using a fieldDescription with no admin class for the target model is a valid case.
            // Since there is no easy way to check for this case, we catch the exception instead.
            return;
        }

        if ($this->hasRequest()) {
            $admin->setRequest($this->getRequest());
        }

        $fieldDescription->setAssociationAdmin($admin);
    }

    /**
     * @param string|int|null $id
     *
     * @phpstan-return T|null
     */
    final public function getObject($id): ?object
    {
        if (null === $id) {
            return null;
        }

        $object = $this->getModelManager()->find($this->getClass(), $id);
        if (null === $object) {
            return null;
        }

        $this->alterObject($object);
        foreach ($this->getExtensions() as $extension) {
            $extension->alterObject($this, $object);
        }

        return $object;
    }

    final public function getForm(): FormInterface
    {
        $form = $this->buildForm();
        if (null === $form) {
            throw new \LogicException('Cannot access form during the building process.');
        }

        return $form;
    }

    final public function getList(): FieldDescriptionCollection
    {
        $list = $this->buildList();
        if (null === $list) {
            throw new \LogicException('Cannot access list during the building process.');
        }

        return $list;
    }

    final public function createQuery(): ProxyQueryInterface
    {
        $query = $this->getModelManager()->createQuery($this->getClass());

        $query = $this->configureQuery($query);
        foreach ($this->getExtensions() as $extension) {
            $extension->configureQuery($this, $query);
        }

        return $query;
    }

    final public function getDatagrid(): DatagridInterface
    {
        $datagrid = $this->buildDatagrid();
        if (null === $datagrid) {
            throw new \LogicException('Cannot access datagrid during the building process.');
        }

        return $datagrid;
    }

    final public function getSideMenu(string $action, ?AdminInterface $childAdmin = null): ItemInterface
    {
        if ($this->isChild()) {
            return $this->getParent()->getSideMenu($action, $this);
        }

        $menu = $this->buildTabMenu($action, $childAdmin);
        if (null === $menu) {
            throw new \LogicException('Cannot access menu during the building process.');
        }

        return $menu;
    }

    final public function getRootCode(): string
    {
        return $this->getRoot()->getCode();
    }

    final public function getRoot(): AdminInterface
    {
        if (!$this->hasParentFieldDescription()) {
            return $this;
        }

        return $this->getParentFieldDescription()->getAdmin()->getRoot();
    }

    final public function setBaseControllerName(string $baseControllerName): void
    {
        $this->baseControllerName = $baseControllerName;
    }

    final public function getBaseControllerName(): string
    {
        return $this->baseControllerName;
    }

    final public function getMaxPerPage(): int
    {
        $sortValues = $this->getDefaultSortValues();

        return $sortValues[DatagridInterface::PER_PAGE] ?? self::DEFAULT_LIST_PER_PAGE_RESULTS;
    }

    final public function setMaxPageLinks(int $maxPageLinks): void
    {
        $this->maxPageLinks = $maxPageLinks;
    }

    final public function getMaxPageLinks(): int
    {
        return $this->maxPageLinks;
    }

    final public function getFormGroups(): array
    {
        return $this->formGroups;
    }

    final public function setFormGroups(array $formGroups): void
    {
        $this->formGroups = $formGroups;
    }

    final public function removeFieldFromFormGroup(string $key): void
    {
        foreach ($this->formGroups as $name => $_formGroup) {
            unset($this->formGroups[$name]['fields'][$key]);

            if ([] === $this->formGroups[$name]['fields']) {
                unset($this->formGroups[$name]);
            }
        }
    }

    final public function reorderFormGroup(string $group, array $keys): void
    {
        $formGroups = $this->getFormGroups();
        $formGroups[$group]['fields'] = array_merge(array_flip($keys), $formGroups[$group]['fields']);
        $this->setFormGroups($formGroups);
    }

    final public function getFormTabs(): array
    {
        return $this->formTabs;
    }

    final public function setFormTabs(array $formTabs): void
    {
        $this->formTabs = $formTabs;
    }

    final public function getShowTabs(): array
    {
        return $this->showTabs;
    }

    final public function setShowTabs(array $showTabs): void
    {
        $this->showTabs = $showTabs;
    }

    final public function getShowGroups(): array
    {
        return $this->showGroups;
    }

    final public function setShowGroups(array $showGroups): void
    {
        $this->showGroups = $showGroups;
    }

    final public function removeFieldFromShowGroup(string $key): void
    {
        foreach ($this->showGroups as $name => $_showGroup) {
            unset($this->showGroups[$name]['fields'][$key]);

            if ([] === $this->showGroups[$name]['fields']) {
                unset($this->showGroups[$name]);
            }
        }
    }

    final public function reorderShowGroup(string $group, array $keys): void
    {
        $showGroups = $this->getShowGroups();
        $showGroups[$group]['fields'] = array_merge(array_flip($keys), $showGroups[$group]['fields']);
        $this->setShowGroups($showGroups);
    }

    final public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription): void
    {
        $this->parentFieldDescription = $parentFieldDescription;
    }

    final public function getParentFieldDescription(): FieldDescriptionInterface
    {
        if (!$this->hasParentFieldDescription()) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no parent field description.',
                static::class
            ));
        }
        \assert(null !== $this->parentFieldDescription);

        return $this->parentFieldDescription;
    }

    final public function hasParentFieldDescription(): bool
    {
        return $this->parentFieldDescription instanceof FieldDescriptionInterface;
    }

    final public function setSubject(?object $subject): void
    {
        if (null !== $subject && !is_a($subject, $this->getClass(), true)) {
            throw new \LogicException(sprintf(
                'Admin "%s" does not allow this subject: %s, use the one register with this admin class %s',
                static::class,
                \get_class($subject),
                $this->getClass()
            ));
        }

        $this->subject = $subject;
    }

    final public function getSubject(): object
    {
        if (!$this->hasSubject()) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no subject.',
                static::class
            ));
        }
        \assert(null !== $this->subject);

        return $this->subject;
    }

    final public function hasSubject(): bool
    {
        if (null === $this->subject && $this->hasRequest() && !$this->hasParentFieldDescription()) {
            $id = $this->getRequest()->get($this->getIdParameter());

            if (null !== $id) {
                $this->subject = $this->getObject($id);
            }
        }

        return null !== $this->subject;
    }

    final public function getFormFieldDescriptions(): array
    {
        $this->buildForm();

        return $this->formFieldDescriptions;
    }

    final public function getFormFieldDescription(string $name): FieldDescriptionInterface
    {
        $this->buildForm();

        if (!$this->hasFormFieldDescription($name)) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no form field description for the field %s.',
                static::class,
                $name
            ));
        }

        return $this->formFieldDescriptions[$name];
    }

    /**
     * Returns true if the admin has a FieldDescription with the given $name.
     */
    final public function hasFormFieldDescription(string $name): bool
    {
        $this->buildForm();

        return \array_key_exists($name, $this->formFieldDescriptions);
    }

    final public function addFormFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->formFieldDescriptions[$name] = $fieldDescription;
    }

    /**
     * remove a FieldDescription.
     */
    final public function removeFormFieldDescription(string $name): void
    {
        unset($this->formFieldDescriptions[$name]);
    }

    /**
     * build and return the collection of form FieldDescription.
     *
     * @return FieldDescriptionInterface[] collection of form FieldDescription
     */
    final public function getShowFieldDescriptions(): array
    {
        $this->buildShow();

        return $this->showFieldDescriptions;
    }

    /**
     * Returns the form FieldDescription with the given $name.
     */
    final public function getShowFieldDescription(string $name): FieldDescriptionInterface
    {
        $this->buildShow();

        if (!$this->hasShowFieldDescription($name)) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no show field description for the field %s.',
                static::class,
                $name
            ));
        }

        return $this->showFieldDescriptions[$name];
    }

    final public function hasShowFieldDescription(string $name): bool
    {
        $this->buildShow();

        return \array_key_exists($name, $this->showFieldDescriptions);
    }

    final public function addShowFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->showFieldDescriptions[$name] = $fieldDescription;
    }

    final public function removeShowFieldDescription(string $name): void
    {
        unset($this->showFieldDescriptions[$name]);
    }

    final public function getListFieldDescriptions(): array
    {
        $this->buildList();

        return $this->listFieldDescriptions;
    }

    final public function getListFieldDescription(string $name): FieldDescriptionInterface
    {
        $this->buildList();

        if (!$this->hasListFieldDescription($name)) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no list field description for %s.',
                static::class,
                $name
            ));
        }

        return $this->listFieldDescriptions[$name];
    }

    final public function hasListFieldDescription(string $name): bool
    {
        $this->buildList();

        return \array_key_exists($name, $this->listFieldDescriptions);
    }

    final public function addListFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->listFieldDescriptions[$name] = $fieldDescription;
    }

    final public function removeListFieldDescription(string $name): void
    {
        unset($this->listFieldDescriptions[$name]);
    }

    final public function getFilterFieldDescription(string $name): FieldDescriptionInterface
    {
        $this->buildDatagrid();

        if (!$this->hasFilterFieldDescription($name)) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no filter field description for the field %s.',
                static::class,
                $name
            ));
        }

        return $this->filterFieldDescriptions[$name];
    }

    final public function hasFilterFieldDescription(string $name): bool
    {
        $this->buildDatagrid();

        return \array_key_exists($name, $this->filterFieldDescriptions);
    }

    final public function addFilterFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->filterFieldDescriptions[$name] = $fieldDescription;
    }

    final public function removeFilterFieldDescription(string $name): void
    {
        unset($this->filterFieldDescriptions[$name]);
    }

    final public function getFilterFieldDescriptions(): array
    {
        $this->buildDatagrid();

        return $this->filterFieldDescriptions;
    }

    final public function addChild(AdminInterface $child, string $field): void
    {
        $parentAdmin = $this;
        while ($parentAdmin->isChild() && $parentAdmin->getCode() !== $child->getCode()) {
            $parentAdmin = $parentAdmin->getParent();
        }

        if ($parentAdmin->getCode() === $child->getCode()) {
            throw new \LogicException(sprintf(
                'Circular reference detected! The child admin `%s` is already in the parent tree of the `%s` admin.',
                $child->getCode(),
                $this->getCode()
            ));
        }

        $this->children[$child->getCode()] = $child;

        $child->setParent($this, $field);
    }

    final public function hasChild(string $code): bool
    {
        return isset($this->children[$code]);
    }

    final public function getChildren(): array
    {
        return $this->children;
    }

    final public function getChild(string $code): AdminInterface
    {
        if (!$this->hasChild($code)) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no child for the code %s.',
                static::class,
                $code
            ));
        }

        return $this->getChildren()[$code];
    }

    final public function setParent(AdminInterface $parent, string $parentAssociationMapping): void
    {
        $this->parent = $parent;
        $this->parentAssociationMapping[$parent->getCode()] = $parentAssociationMapping;
    }

    final public function getParent(): AdminInterface
    {
        if (null === $this->parent) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no parent.',
                static::class
            ));
        }

        return $this->parent;
    }

    final public function getRootAncestor(): AdminInterface
    {
        $parent = $this;

        while ($parent->isChild()) {
            $parent = $parent->getParent();
        }

        return $parent;
    }

    final public function getChildDepth(): int
    {
        $parent = $this;
        $depth = 0;

        while ($parent->isChild()) {
            $parent = $parent->getParent();
            ++$depth;
        }

        return $depth;
    }

    final public function getCurrentLeafChildAdmin(): ?AdminInterface
    {
        $child = $this->getCurrentChildAdmin();

        if (null === $child) {
            return null;
        }

        for ($c = $child; null !== $c; $c = $child->getCurrentChildAdmin()) {
            $child = $c;
        }

        return $child;
    }

    final public function isChild(): bool
    {
        return $this->parent instanceof AdminInterface;
    }

    /**
     * Returns true if the admin has children, false otherwise.
     */
    final public function hasChildren(): bool
    {
        return \count($this->children) > 0;
    }

    final public function setUniqId(string $uniqId): void
    {
        $this->uniqId = $uniqId;
    }

    final public function getUniqId(): string
    {
        if (null === $this->uniqId) {
            $this->uniqId = sprintf('s%s', uniqid());
        }

        return $this->uniqId;
    }

    final public function getClassnameLabel(): string
    {
        if (null === $this->classnameLabel) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no classname label. Did you forgot to initialize the admin ?',
                static::class
            ));
        }

        return $this->classnameLabel;
    }

    final public function getPersistentParameters(): array
    {
        $parameters = $this->configurePersistentParameters();
        foreach ($this->getExtensions() as $extension) {
            $parameters = $extension->configurePersistentParameters($this, $parameters);
        }

        return $parameters;
    }

    final public function getPersistentParameter(string $name, $default = null)
    {
        $parameters = $this->getPersistentParameters();

        return $parameters[$name] ?? $default;
    }

    final public function setCurrentChild(bool $currentChild): void
    {
        $this->currentChild = $currentChild;
    }

    final public function isCurrentChild(): bool
    {
        return $this->currentChild;
    }

    final public function getCurrentChildAdmin(): ?AdminInterface
    {
        foreach ($this->getChildren() as $child) {
            if ($child->isCurrentChild()) {
                return $child;
            }
        }

        return null;
    }

    final public function setTranslationDomain(string $translationDomain): void
    {
        $this->translationDomain = $translationDomain;
    }

    final public function getTranslationDomain(): string
    {
        return $this->translationDomain;
    }

    final public function getTranslationLabel(string $label, string $context = '', string $type = ''): string
    {
        return $this->getLabelTranslatorStrategy()->getLabel($label, $context, $type);
    }

    final public function setRequest(Request $request): void
    {
        $this->request = $request;

        foreach ($this->getChildren() as $children) {
            $children->setRequest($request);
        }
    }

    final public function getRequest(): Request
    {
        if (null === $this->request) {
            throw new \LogicException('The Request object has not been set');
        }

        return $this->request;
    }

    final public function hasRequest(): bool
    {
        return null !== $this->request;
    }

    final public function getCode(): string
    {
        return $this->code;
    }

    final public function getBaseCodeRoute(): string
    {
        if ($this->isChild()) {
            return $this->getParent()->getBaseCodeRoute().'|'.$this->getCode();
        }

        return $this->getCode();
    }

    /**
     * @return string
     */
    public function getObjectIdentifier()
    {
        return $this->getCode();
    }

    final public function showIn(string $context): bool
    {
        return $this->isGranted($this->getPermissionsShow($context));
    }

    final public function createObjectSecurity(object $object): void
    {
        $this->getSecurityHandler()->createObjectSecurity($this, $object);
    }

    final public function isGranted($name, ?object $object = null): bool
    {
        $objectRef = null !== $object ? sprintf('/%s#%s', spl_object_hash($object), $this->id($object) ?? '') : '';
        $key = md5(json_encode($name).$objectRef);

        if (!\array_key_exists($key, $this->cacheIsGranted)) {
            $this->cacheIsGranted[$key] = $this->getSecurityHandler()->isGranted($this, $name, $object ?? $this);
        }

        return $this->cacheIsGranted[$key];
    }

    final public function getUrlSafeIdentifier(object $model): ?string
    {
        return $this->getModelManager()->getUrlSafeIdentifier($model);
    }

    final public function getNormalizedIdentifier(object $model): ?string
    {
        return $this->getModelManager()->getNormalizedIdentifier($model);
    }

    public function id(object $model): ?string
    {
        return $this->getNormalizedIdentifier($model);
    }

    final public function getShow(): FieldDescriptionCollection
    {
        $show = $this->buildShow();
        if (null === $show) {
            throw new \LogicException('Cannot access show during the building process.');
        }

        return $show;
    }

    final public function setFormTheme(array $formTheme): void
    {
        $this->formTheme = $formTheme;
    }

    final public function getFormTheme(): array
    {
        return $this->formTheme;
    }

    final public function setFilterTheme(array $filterTheme): void
    {
        $this->filterTheme = $filterTheme;
    }

    final public function getFilterTheme(): array
    {
        return $this->filterTheme;
    }

    final public function addExtension(AdminExtensionInterface $extension): void
    {
        $this->extensions[] = $extension;
    }

    final public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function toString(object $object): string
    {
        if (method_exists($object, '__toString') && null !== $object->__toString()) {
            return $object->__toString();
        }

        return sprintf('%s:%s', ClassUtils::getClass($object), spl_object_hash($object));
    }

    final public function supportsPreviewMode(): bool
    {
        return $this->supportsPreviewMode;
    }

    /**
     * Returns predefined per page options.
     *
     * @return list<int>
     */
    public function getPerPageOptions(): array
    {
        $perPageOptions = self::DEFAULT_LIST_PER_PAGE_OPTIONS;
        $perPageOptions[] = $this->getMaxPerPage();

        $perPageOptions = array_unique($perPageOptions);
        sort($perPageOptions);

        return $perPageOptions;
    }

    /**
     * Returns true if the per page value is allowed, false otherwise.
     */
    final public function determinedPerPageValue(int $perPage): bool
    {
        return \in_array($perPage, $this->getPerPageOptions(), true);
    }

    final public function isAclEnabled(): bool
    {
        return $this->getSecurityHandler() instanceof AclSecurityHandlerInterface;
    }

    public function getObjectMetadata(object $object): MetadataInterface
    {
        return new Metadata($this->toString($object));
    }

    final public function setListMode(string $mode): void
    {
        $this->getRequest()->getSession()->set(sprintf('%s.list_mode', $this->getCode()), $mode);
    }

    final public function getListMode(): string
    {
        if (!$this->hasRequest() || !$this->getRequest()->hasSession()) {
            return 'list';
        }

        return $this->getRequest()->getSession()->get(sprintf('%s.list_mode', $this->getCode()), 'list');
    }

    final public function checkAccess(string $action, ?object $object = null): void
    {
        $access = $this->getAccess();

        if (!\array_key_exists($action, $access)) {
            throw new \InvalidArgumentException(sprintf(
                'Action "%s" could not be found in access mapping.'
                .' Please make sure your action is defined into your admin class accessMapping property.',
                $action
            ));
        }

        if (!\is_array($access[$action])) {
            $access[$action] = [$access[$action]];
        }

        foreach ($access[$action] as $role) {
            if (false === $this->isGranted($role, $object)) {
                throw new AccessDeniedException(sprintf('Access Denied to the action %s and role %s', $action, $role));
            }
        }
    }

    final public function hasAccess(string $action, ?object $object = null): bool
    {
        $access = $this->getAccess();

        if (!\array_key_exists($action, $access)) {
            return false;
        }

        if (!\is_array($access[$action])) {
            $access[$action] = [$access[$action]];
        }

        foreach ($access[$action] as $role) {
            if (false === $this->isGranted($role, $object)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, array<string, mixed>>
     *
     * @phpstan-param T|null $object
     */
    final public function getActionButtons(string $action, ?object $object = null): array
    {
        $defaultButtonList = $this->getDefaultActionButtons($action, $object);
        $buttonList = $this->configureActionButtons($defaultButtonList, $action, $object);

        foreach ($this->getExtensions() as $extension) {
            $buttonList = $extension->configureActionButtons($this, $buttonList, $action, $object);
        }

        return $buttonList;
    }

    /**
     * Get the list of actions that can be accessed directly from the dashboard.
     *
     * @return array<string, array<string, mixed>>
     */
    final public function getDashboardActions(): array
    {
        $actions = [];

        if ($this->hasRoute('create') && $this->hasAccess('create')) {
            $actions['create'] = [
                'label' => 'link_add',
                'translation_domain' => 'SonataAdminBundle',
                'template' => $this->getTemplateRegistry()->getTemplate('action_create'),
                'url' => $this->generateUrl('create'),
                'icon' => 'fas fa-plus-circle',
            ];
        }

        if ($this->hasRoute('list') && $this->hasAccess('list')) {
            $actions['list'] = [
                'label' => 'link_list',
                'translation_domain' => 'SonataAdminBundle',
                'url' => $this->generateUrl('list'),
                'icon' => 'fas fa-list',
            ];
        }

        $actions = $this->configureDashboardActions($actions);

        foreach ($this->getExtensions() as $extension) {
            $actions = $extension->configureDashboardActions($this, $actions);
        }

        return $actions;
    }

    final public function createFieldDescription(string $propertyName, array $options = []): FieldDescriptionInterface
    {
        $fieldDescriptionFactory = $this->getFieldDescriptionFactory();

        $fieldDescription = $fieldDescriptionFactory->create($this->getClass(), $propertyName, $options);

        $fieldDescription->setAdmin($this);

        return $fieldDescription;
    }

    /**
     * Hook to run after initialization.
     */
    protected function configure(): void
    {
    }

    /**
     * @phpstan-return T
     */
    protected function createNewInstance(): object
    {
        return Instantiator::instantiate($this->getClass());
    }

    /**
     * @phpstan-param T $object
     */
    protected function alterNewInstance(object $object): void
    {
    }

    /**
     * @phpstan-param T $object
     */
    protected function alterObject(object $object): void
    {
    }

    /**
     * @phpstan-param T $object
     */
    protected function preValidate(object $object): void
    {
    }

    /**
     * @phpstan-param T $object
     */
    protected function preUpdate(object $object): void
    {
    }

    /**
     * @phpstan-param T $object
     */
    protected function postUpdate(object $object): void
    {
    }

    /**
     * @phpstan-param T $object
     */
    protected function prePersist(object $object): void
    {
    }

    /**
     * @phpstan-param T $object
     */
    protected function postPersist(object $object): void
    {
    }

    /**
     * @phpstan-param T $object
     */
    protected function preRemove(object $object): void
    {
    }

    /**
     * @phpstan-param T $object
     */
    protected function postRemove(object $object): void
    {
    }

    /**
     * @return array<string, mixed>
     */
    protected function configurePersistentParameters(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    protected function configureExportFields(): array
    {
        return $this->getModelManager()->getExportFields($this->getClass());
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        return $query;
    }

    /**
     * urlize the given word.
     *
     * @param string $sep the separator
     */
    final protected function urlize(string $word, string $sep = '_'): string
    {
        return strtolower(preg_replace('/[^a-z0-9_]/i', $sep.'$1', $word) ?? '');
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    protected function configureFilterParameters(array $parameters): array
    {
        return $parameters;
    }

    /**
     * Returns a list of default sort values.
     *
     * @phpstan-return array{
     *     _page?: int,
     *     _per_page?: int,
     *     _sort_by?: string,
     *     _sort_order?: string
     * }
     */
    final protected function getDefaultSortValues(): array
    {
        $defaultSortValues = [DatagridInterface::PAGE => 1, DatagridInterface::PER_PAGE => self::DEFAULT_LIST_PER_PAGE_RESULTS];

        $this->configureDefaultSortValues($defaultSortValues);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureDefaultSortValues($this, $defaultSortValues);
        }

        return $defaultSortValues;
    }

    /**
     * Returns a list of default filters.
     *
     * @return array<string, array<string, mixed>>
     */
    final protected function getDefaultFilterValues(): array
    {
        $defaultFilterValues = [];

        $this->configureDefaultFilterValues($defaultFilterValues);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureDefaultFilterValues($this, $defaultFilterValues);
        }

        return $defaultFilterValues;
    }

    /**
     * @return array<string, mixed>
     */
    final protected function getFormOptions(): array
    {
        $formOptions = [];

        $this->configureFormOptions($formOptions);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureFormOptions($this, $formOptions);
        }

        return $formOptions;
    }

    /**
     * @phpstan-param FormMapper<T> $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
    }

    /**
     * @phpstan-param ListMapper<T> $list
     */
    protected function configureListFields(ListMapper $list): void
    {
    }

    /**
     * @phpstan-param DatagridMapper<T> $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
    }

    /**
     * @phpstan-param ShowMapper<T> $show
     */
    protected function configureShowFields(ShowMapper $show): void
    {
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
    }

    /**
     * @param array<string, array<string, mixed>> $buttonList
     *
     * @return array<string, array<string, mixed>>
     *
     * @phpstan-param T|null $object
     */
    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        return $buttonList;
    }

    /**
     * @param array<string, array<string, mixed>> $actions
     *
     * @return array<string, array<string, mixed>>
     */
    protected function configureDashboardActions(array $actions): array
    {
        return $actions;
    }

    /**
     * Allows you to customize batch actions.
     *
     * @param array<string, array<string, mixed>> $actions
     *
     * @return array<string, array<string, mixed>>
     */
    protected function configureBatchActions(array $actions): array
    {
        return $actions;
    }

    /**
     * Configures the tab menu in your admin.
     *
     * @phpstan-param AdminInterface<object>|null $childAdmin
     */
    protected function configureTabMenu(ItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
    }

    /**
     * Gets the subclass corresponding to the given name.
     *
     * @phpstan-return class-string<T>
     */
    protected function getSubClass(string $name): string
    {
        if ($this->hasSubClass($name)) {
            return $this->subClasses[$name];
        }

        throw new \LogicException(sprintf('Unable to find the subclass `%s` for admin `%s`', $name, static::class));
    }

    /**
     * Return list routes with permissions name.
     *
     * @return array<string, string|string[]>
     */
    final protected function getAccess(): array
    {
        $access = array_merge([
            'acl' => AdminPermissionMap::PERMISSION_MASTER,
            'export' => AdminPermissionMap::PERMISSION_EXPORT,
            'historyCompareRevisions' => AdminPermissionMap::PERMISSION_HISTORY,
            'historyViewRevision' => AdminPermissionMap::PERMISSION_HISTORY,
            'history' => AdminPermissionMap::PERMISSION_HISTORY,
            'edit' => AdminPermissionMap::PERMISSION_EDIT,
            'show' => AdminPermissionMap::PERMISSION_VIEW,
            'create' => AdminPermissionMap::PERMISSION_CREATE,
            'delete' => AdminPermissionMap::PERMISSION_DELETE,
            'batchDelete' => AdminPermissionMap::PERMISSION_DELETE,
            'list' => AdminPermissionMap::PERMISSION_LIST,
        ], $this->getAccessMapping());

        foreach ($this->getExtensions() as $extension) {
            $access = array_merge($access, $extension->getAccessMapping($this));
        }

        return $access;
    }

    /**
     * @return array<string, string|string[]> [action1 => requiredRole1, action2 => [requiredRole2, requiredRole3]]
     */
    protected function getAccessMapping(): array
    {
        return [];
    }

    /**
     * Return the list of permissions the user should have in order to display the admin.
     *
     * @return string[]
     */
    protected function getPermissionsShow(string $context): array
    {
        return ['LIST'];
    }

    /**
     * Configures a list of default filters.
     *
     * @param array<string, array<string, mixed>> $filterValues
     */
    protected function configureDefaultFilterValues(array &$filterValues): void
    {
    }

    /**
     * Configures a list of form options.
     *
     * @param array<string, mixed> $formOptions
     */
    protected function configureFormOptions(array &$formOptions): void
    {
    }

    /**
     * Configures a list of default sort values.
     *
     * Example:
     *   $sortValues[DatagridInterface::SORT_BY] = 'foo'
     *   $sortValues[DatagridInterface::SORT_ORDER] = 'DESC'
     *
     * @param array<string, string|int> $sortValues
     * @phpstan-param array{
     *     _page?: int,
     *     _per_page?: int,
     *     _sort_by?: string,
     *     _sort_order?: string
     * } $sortValues
     */
    protected function configureDefaultSortValues(array &$sortValues): void
    {
    }

    /**
     * Set the parent object, if any, to the provided object.
     *
     * @phpstan-param T $object
     */
    final protected function appendParentObject(object $object): void
    {
        if ($this->isChild()) {
            $parentAssociationMapping = $this->getParentAssociationMapping();

            if (null !== $parentAssociationMapping) {
                $parentAdmin = $this->getParent();
                $parentObject = $parentAdmin->getObject($this->getRequest()->get($parentAdmin->getIdParameter()));

                if (null !== $parentObject) {
                    $propertyAccessor = PropertyAccess::createPropertyAccessor();
                    $value = $propertyAccessor->getValue($object, $parentAssociationMapping);

                    if (\is_array($value) || $value instanceof \ArrayAccess) {
                        $value[] = $parentObject;
                        $propertyAccessor->setValue($object, $parentAssociationMapping, $value);
                    } else {
                        $propertyAccessor->setValue($object, $parentAssociationMapping, $parentObject);
                    }
                }

                return;
            }
        }

        if ($this->hasParentFieldDescription()) {
            $parentAdmin = $this->getParentFieldDescription()->getAdmin();
            $parentObject = $parentAdmin->getObject($this->getRequest()->get($parentAdmin->getIdParameter()));

            if (null !== $parentObject) {
                ObjectManipulator::setObject($object, $parentObject, $this->getParentFieldDescription());
            }
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     *
     * @phpstan-param T|null $object
     */
    private function getDefaultActionButtons(string $action, ?object $object = null): array
    {
        // nothing to do for non-internal actions
        if (!isset(self::INTERNAL_ACTIONS[$action])) {
            return [];
        }

        $buttonList = [];

        $actionBit = self::INTERNAL_ACTIONS[$action];

        if (0 !== (self::MASK_OF_ACTION_CREATE & $actionBit)
            && $this->hasRoute('create')
            && $this->hasAccess('create')
        ) {
            $buttonList['create'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_create'),
            ];
        }

        $canAccessObject = 0 !== (self::MASK_OF_ACTIONS_USING_OBJECT & $actionBit)
            && null !== $object
            && null !== $this->id($object);

        if ($canAccessObject
            && 0 !== (self::MASK_OF_ACTION_EDIT & $actionBit)
            && $this->hasRoute('edit')
            && $this->hasAccess('edit', $object)
        ) {
            $buttonList['edit'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_edit'),
            ];
        }

        if ($canAccessObject
            && 0 !== (self::MASK_OF_ACTION_HISTORY & $actionBit)
            && $this->hasRoute('history')
            && $this->hasAccess('history', $object)
        ) {
            $buttonList['history'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_history'),
            ];
        }

        if ($canAccessObject
            && 0 !== (self::MASK_OF_ACTION_ACL & $actionBit)
            && $this->isAclEnabled()
            && $this->hasRoute('acl')
            && $this->hasAccess('acl', $object)
        ) {
            $buttonList['acl'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_acl'),
            ];
        }

        if ($canAccessObject
            && 0 !== (self::MASK_OF_ACTION_SHOW & $actionBit)
            && $this->hasRoute('show')
            && $this->hasAccess('show', $object)
            && \count($this->getShow()) > 0
        ) {
            $buttonList['show'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_show'),
            ];
        }

        if (0 !== (self::MASK_OF_ACTION_LIST & $actionBit)
            && $this->hasRoute('list')
            && $this->hasAccess('list')
        ) {
            $buttonList['list'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_list'),
            ];
        }

        return $buttonList;
    }

    /**
     * @return DatagridInterface<ProxyQueryInterface>|null
     */
    private function buildDatagrid(): ?DatagridInterface
    {
        if ($this->loaded['datagrid']) {
            return $this->datagrid;
        }

        $this->loaded['datagrid'] = true;

        $filterParameters = $this->getFilterParameters();

        // transform DatagridInterface::SORT_BY filter parameter from a string to a FieldDescriptionInterface for the datagrid.
        if (isset($filterParameters[DatagridInterface::SORT_BY]) && \is_string($filterParameters[DatagridInterface::SORT_BY])) {
            if ($this->hasListFieldDescription($filterParameters[DatagridInterface::SORT_BY])) {
                $filterParameters[DatagridInterface::SORT_BY] = $this->getListFieldDescription($filterParameters[DatagridInterface::SORT_BY]);
            } else {
                $filterParameters[DatagridInterface::SORT_BY] = $this->createFieldDescription(
                    $filterParameters[DatagridInterface::SORT_BY]
                );

                $this->getListBuilder()->buildField(null, $filterParameters[DatagridInterface::SORT_BY]);
            }
        }

        // initialize the datagrid
        $this->datagrid = $this->getDatagridBuilder()->getBaseDatagrid($this, $filterParameters);

        $this->datagrid->getPager()->setMaxPageLinks($this->getMaxPageLinks());

        $mapper = new DatagridMapper($this->getDatagridBuilder(), $this->datagrid, $this);

        // build the datagrid filter
        $this->configureDatagridFilters($mapper);

        // ok, try to limit to add parent filter
        if ($this->isChild()) {
            $parentAssociationMapping = $this->getParentAssociationMapping();

            if (null !== $parentAssociationMapping && !$mapper->has($parentAssociationMapping)) {
                $mapper->add($parentAssociationMapping, null, [
                    'show_filter' => false,
                    'label' => false,
                    'field_type' => ModelHiddenType::class,
                    'field_options' => [
                        'model_manager' => $this->getModelManager(),
                    ],
                    'operator_type' => HiddenType::class,
                ], [
                    'admin_code' => $this->getParent()->getCode(),
                ]);
            }
        }

        foreach ($this->getExtensions() as $extension) {
            $extension->configureDatagridFilters($mapper);
        }

        return $this->datagrid;
    }

    /**
     * @return FieldDescriptionCollection<FieldDescriptionInterface>|null
     */
    private function buildShow(): ?FieldDescriptionCollection
    {
        if ($this->loaded['show']) {
            return $this->show;
        }

        $this->loaded['show'] = true;

        $this->show = $this->getShowBuilder()->getBaseList();
        $mapper = new ShowMapper($this->getShowBuilder(), $this->show, $this);

        $this->configureShowFields($mapper);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureShowFields($mapper);
        }

        return $this->show;
    }

    /**
     * @return FieldDescriptionCollection<FieldDescriptionInterface>|null
     */
    private function buildList(): ?FieldDescriptionCollection
    {
        if ($this->loaded['list']) {
            return $this->list;
        }

        $this->loaded['list'] = true;

        $this->list = $this->getListBuilder()->getBaseList();
        $mapper = new ListMapper($this->getListBuilder(), $this->list, $this);

        if (\count($this->getBatchActions()) > 0 && $this->hasRequest() && !$this->getRequest()->isXmlHttpRequest()) {
            $mapper->add(ListMapper::NAME_BATCH, ListMapper::TYPE_BATCH, [
                'label' => 'batch',
                'sortable' => false,
                'virtual_field' => true,
                'template' => $this->getTemplateRegistry()->getTemplate('batch'),
            ]);
        }

        $this->configureListFields($mapper);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureListFields($mapper);
        }

        if ($this->hasRequest() && $this->getRequest()->isXmlHttpRequest()) {
            $mapper->add(ListMapper::NAME_SELECT, ListMapper::TYPE_SELECT, [
                'label' => false,
                'sortable' => false,
                'virtual_field' => false,
                'template' => $this->getTemplateRegistry()->getTemplate('select'),
            ]);
        }

        return $this->list;
    }

    private function buildForm(): ?FormInterface
    {
        if ($this->loaded['form']) {
            return $this->form;
        }

        $this->loaded['form'] = true;

        $formBuilder = $this->getFormBuilder();
        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            /** @phpstan-var T $data */
            $data = $event->getData();
            $this->preValidate($data);
        }, 100);

        $this->form = $formBuilder->getForm();

        return $this->form;
    }

    private function buildRoutes(): ?RouteCollectionInterface
    {
        if ($this->loaded['routes']) {
            return $this->routes;
        }

        $this->loaded['routes'] = true;

        $routes = new RouteCollection(
            $this->getBaseCodeRoute(),
            $this->getBaseRouteName(),
            $this->getBaseRoutePattern(),
            $this->getBaseControllerName()
        );

        $this->getRouteBuilder()->build($this, $routes);

        $this->configureRoutes($routes);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureRoutes($this, $routes);
        }

        $this->routes = $routes;

        return $this->routes;
    }

    /**
     * @phpstan-param AdminInterface<object>|null $childAdmin
     */
    private function buildTabMenu(string $action, ?AdminInterface $childAdmin = null): ?ItemInterface
    {
        if ($this->loaded['tab_menu']) {
            return $this->menu;
        }

        $this->loaded['tab_menu'] = true;

        $menu = $this->getMenuFactory()->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        $menu->setExtra('translation_domain', $this->getTranslationDomain());

        $this->configureTabMenu($menu, $action, $childAdmin);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureTabMenu($this, $menu, $action, $childAdmin);
        }

        $this->menu = $menu;

        return $this->menu;
    }
}
