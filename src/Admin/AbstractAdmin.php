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
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Manipulator\ObjectManipulator;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\Exporter\Source\SourceIteratorInterface;
use Sonata\Form\Validator\Constraints\InlineConstraint;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as RoutingUrlGeneratorInterface;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Mapping\GenericMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class AbstractAdmin implements AdminInterface, DomainObjectInterface, AdminTreeInterface
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

    public const MOSAIC_ICON_CLASS = 'fa fa-th-large fa-fw';

    /**
     * The list FieldDescription constructed from the configureListField method.
     *
     * @var FieldDescriptionInterface[]
     */
    protected $listFieldDescriptions = [];

    /**
     * The show FieldDescription constructed from the configureShowFields method.
     *
     * @var FieldDescriptionInterface[]
     */
    protected $showFieldDescriptions = [];

    /**
     * The list FieldDescription constructed from the configureFormField method.
     *
     * @var FieldDescriptionInterface[]
     */
    protected $formFieldDescriptions = [];

    /**
     * The filter FieldDescription constructed from the configureFilterField method.
     *
     * @var FieldDescriptionInterface[]
     */
    protected $filterFieldDescriptions = [];

    /**
     * The maximum number of page numbers to display in the list.
     *
     * @var int
     */
    protected $maxPageLinks = 25;

    /**
     * The base route name used to generate the routing information.
     *
     * @var string
     */
    protected $baseRouteName;

    /**
     * The base route pattern used to generate the routing information.
     *
     * @var string
     */
    protected $baseRoutePattern;

    /**
     * The base name controller used to generate the routing information.
     *
     * @var string
     */
    protected $baseControllerName;

    /**
     * The label class name  (used in the title/breadcrumb ...).
     *
     * @var string
     */
    protected $classnameLabel;

    /**
     * The translation domain to be used to translate messages.
     *
     * @var string
     */
    protected $translationDomain = 'messages';

    /**
     * Options to set to the form (ie, validation_groups).
     *
     * @var array
     */
    protected $formOptions = [];

    /**
     * Pager type.
     *
     * @var string
     */
    protected $pagerType = Pager::TYPE_DEFAULT;

    /**
     * The code related to the admin.
     *
     * @var string
     */
    protected $code;

    /**
     * The label.
     *
     * @var string
     */
    protected $label;

    /**
     * Array of routes related to this admin.
     *
     * @var RouteCollectionInterface
     */
    protected $routes;

    /**
     * The subject only set in edit/update/create mode.
     *
     * @var object|null
     */
    protected $subject;

    /**
     * Define a Collection of child admin, ie /admin/order/{id}/order-element/{childId}.
     *
     * @var array
     */
    protected $children = [];

    /**
     * Reference the parent admin.
     *
     * @var AdminInterface|null
     */
    protected $parent;

    /**
     * Reference the parent FieldDescription related to this admin
     * only set for FieldDescription which is associated to an Sub Admin instance.
     *
     * @var FieldDescriptionInterface
     */
    protected $parentFieldDescription;

    /**
     * If true then the current admin is part of the nested admin set (from the url).
     *
     * @var bool
     */
    protected $currentChild = false;

    /**
     * The uniqid is used to avoid clashing with 2 admin related to the code
     * ie: a Block linked to a Block.
     *
     * @var string
     */
    protected $uniqid;

    /**
     * The Entity or Document manager.
     *
     * @var ModelManagerInterface
     */
    protected $modelManager;

    /**
     * The current request object.
     *
     * @var Request|null
     */
    protected $request;

    /**
     * The translator component.
     *
     * NEXT_MAJOR: remove this property
     *
     * @var TranslatorInterface
     *
     * @deprecated since sonata-project/admin-bundle 3.9, to be removed with 4.0
     */
    protected $translator;

    /**
     * The related form contractor.
     *
     * @var FormContractorInterface
     */
    protected $formContractor;

    /**
     * The related list builder.
     *
     * @var ListBuilderInterface
     */
    protected $listBuilder;

    /**
     * The related view builder.
     *
     * @var ShowBuilderInterface
     */
    protected $showBuilder;

    /**
     * The related datagrid builder.
     *
     * @var DatagridBuilderInterface
     */
    protected $datagridBuilder;

    /**
     * @var RouteBuilderInterface
     */
    protected $routeBuilder;

    /**
     * The datagrid instance.
     *
     * @var DatagridInterface|null
     */
    protected $datagrid;

    /**
     * The router instance.
     *
     * @var RouteGeneratorInterface|null
     */
    protected $routeGenerator;

    /**
     * @var SecurityHandlerInterface
     */
    protected $securityHandler;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * The configuration pool.
     *
     * @var Pool
     */
    protected $configurationPool;

    /**
     * @var ItemInterface
     */
    protected $menu;

    /**
     * @var FactoryInterface
     */
    protected $menuFactory;

    /**
     * @var array<string, bool>
     */
    protected $loaded = [
        'routes' => false,
        'tab_menu' => false,
        'show' => false,
        'list' => false,
        'form' => false,
        'datagrid' => false,
    ];

    /**
     * @var string[]
     */
    protected $formTheme = [];

    /**
     * @var string[]
     */
    protected $filterTheme = [];

    /**
     * @var AdminExtensionInterface[]
     */
    protected $extensions = [];

    /**
     * @var LabelTranslatorStrategyInterface
     */
    protected $labelTranslatorStrategy;

    /**
     * Setting to true will enable preview mode for
     * the entity and show a preview button in the
     * edit/create forms.
     *
     * @var bool
     */
    protected $supportsPreviewMode = false;

    /**
     * Roles and permissions per role.
     *
     * @var array 'role' => ['permission', 'permission']
     */
    protected $securityInformation = [];

    protected $cacheIsGranted = [];

    /**
     * Action list for the search result.
     *
     * @var string[]
     */
    protected $searchResultActions = ['edit', 'show'];

    protected $listModes = [
        'list' => [
            'class' => 'fa fa-list fa-fw',
        ],
        'mosaic' => [
            'class' => self::MOSAIC_ICON_CLASS,
        ],
    ];

    /**
     * The Access mapping.
     *
     * @var array [action1 => requiredRole1, action2 => [requiredRole2, requiredRole3]]
     */
    protected $accessMapping = [];

    /**
     * @var array
     */
    private $parentAssociationMapping = [];

    /**
     * @var MutableTemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * The class name managed by the admin class.
     *
     * @var string
     */
    private $class;

    /**
     * The subclasses supported by the admin class.
     *
     * @var array<string, string>
     */
    private $subClasses = [];

    /**
     * The list collection.
     *
     * @var FieldDescriptionCollection|null
     */
    private $list;

    /**
     * @var FieldDescriptionCollection|null
     */
    private $show;

    /**
     * @var FormInterface|null
     */
    private $form;

    /**
     * The cached base route name.
     *
     * @var string
     */
    private $cachedBaseRouteName;

    /**
     * The cached base route pattern.
     *
     * @var string
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
     * The manager type to use for the admin.
     *
     * @var string
     */
    private $managerType;

    /**
     * Component responsible for persisting filters.
     *
     * @var FilterPersisterInterface|null
     */
    private $filterPersister;

    public function __construct(string $code, string $class, ?string $baseControllerName = null)
    {
        $this->code = $code;
        $this->class = $class;
        $this->baseControllerName = $baseControllerName;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFormats(): array
    {
        return [
            'json', 'xml', 'csv', 'xls',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFields(): array
    {
        $fields = $this->getModelManager()->getExportFields($this->getClass());

        foreach ($this->getExtensions() as $extension) {
            if (method_exists($extension, 'configureExportFields')) {
                $fields = $extension->configureExportFields($this, $fields);
            }
        }

        return $fields;
    }

    public function getDataSourceIterator(): SourceIteratorInterface
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $fields = [];

        foreach ($this->getExportFields() as $key => $field) {
            $label = $this->getTranslationLabel($field, 'export', 'label');

            // NEXT_MAJOR: We have to find another way to have a translated label or stop deprecating the translator.
            $transLabel = $this->translator->trans($label, [], $this->getTranslationDomain());

            // NEXT_MAJOR: Remove the following code in favor of the commented one.
            // If a key is provided we use it otherwise we use the generated label.
            // $fieldKey = \is_string($key) ? $key : $transLabel;
            // $fields[$fieldKey] = $field;
            if ($transLabel === $label) {
                $fields[$key] = $field;
            } else {
                $fields[$transLabel] = $field;
            }
        }

        return $this->getModelManager()->getDataSourceIterator($datagrid, $fields);
    }

    public function validate(ErrorElement $errorElement, $object): void
    {
    }

    /**
     * define custom variable.
     */
    public function initialize(): void
    {
        if (!$this->classnameLabel) {
            $this->classnameLabel = substr($this->getClass(), strrpos($this->getClass(), '\\') + 1);
        }

        $this->configure();
    }

    public function update(object $object): object
    {
        $this->preUpdate($object);
        foreach ($this->extensions as $extension) {
            $extension->preUpdate($this, $object);
        }

        $this->getModelManager()->update($object);

        $this->postUpdate($object);
        foreach ($this->extensions as $extension) {
            $extension->postUpdate($this, $object);
        }

        return $object;
    }

    public function create(object $object): object
    {
        $this->prePersist($object);
        foreach ($this->extensions as $extension) {
            $extension->prePersist($this, $object);
        }

        $this->getModelManager()->create($object);

        $this->postPersist($object);
        foreach ($this->extensions as $extension) {
            $extension->postPersist($this, $object);
        }

        $this->createObjectSecurity($object);

        return $object;
    }

    public function delete(object $object): void
    {
        $this->preRemove($object);
        foreach ($this->extensions as $extension) {
            $extension->preRemove($this, $object);
        }

        $this->getSecurityHandler()->deleteObjectSecurity($this, $object);
        $this->getModelManager()->delete($object);

        $this->postRemove($object);
        foreach ($this->extensions as $extension) {
            $extension->postRemove($this, $object);
        }
    }

    public function preValidate(object $object): void
    {
    }

    public function preUpdate(object $object): void
    {
    }

    public function postUpdate(object $object): void
    {
    }

    public function prePersist(object $object): void
    {
    }

    public function postPersist(object $object): void
    {
    }

    public function preRemove(object $object): void
    {
    }

    public function postRemove(object $object): void
    {
    }

    public function preBatchAction(string $actionName, ProxyQueryInterface $query, array &$idx, bool $allElements = false): void
    {
    }

    public function getFilterParameters(): array
    {
        $parameters = [];

        // build the values array
        if ($this->hasRequest()) {
            $filters = $this->request->query->get('filter', []);
            if (isset($filters['_page'])) {
                $filters['_page'] = (int) $filters['_page'];
            }
            if (isset($filters['_per_page'])) {
                $filters['_per_page'] = (int) $filters['_per_page'];
            }

            // if filter persistence is configured
            if (null !== $this->filterPersister) {
                // if reset filters is asked, remove from storage
                if ('reset' === $this->request->query->get('filters')) {
                    $this->filterPersister->reset($this->getCode());
                }

                // if no filters, fetch from storage
                // otherwise save to storage
                if (empty($filters)) {
                    $filters = $this->filterPersister->get($this->getCode());
                } else {
                    $this->filterPersister->set($this->getCode(), $filters);
                }
            }

            $parameters = array_merge(
                $this->getModelManager()->getDefaultSortValues($this->getClass()),
                $this->getDefaultSortValues(),
                $this->getDefaultFilterValues(),
                $filters
            );

            if (!isset($parameters['_per_page']) || !$this->determinedPerPageValue($parameters['_per_page'])) {
                $parameters['_per_page'] = $this->getMaxPerPage();
            }

            // always force the parent value
            if ($this->isChild() && $this->getParentAssociationMapping()) {
                $name = str_replace('.', '__', $this->getParentAssociationMapping());
                $parameters[$name] = ['value' => $this->request->get($this->getParent()->getIdParameter())];
            }
        }

        return $parameters;
    }

    /**
     * Returns the name of the parent related field, so the field can be use to set the default
     * value (ie the parent object) or to filter the object.
     *
     * @throws \InvalidArgumentException
     */
    public function getParentAssociationMapping(): ?string
    {
        if ($this->isChild()) {
            $parent = $this->getParent()->getCode();

            if (\array_key_exists($parent, $this->parentAssociationMapping)) {
                return $this->parentAssociationMapping[$parent];
            }

            throw new \InvalidArgumentException(sprintf(
                'There\'s no association between %s and %s.',
                $this->getCode(),
                $this->getParent()->getCode()
            ));
        }

        return null;
    }

    final public function addParentAssociationMapping(string $code, string $value): void
    {
        $this->parentAssociationMapping[$code] = $value;
    }

    /**
     * Returns the baseRoutePattern used to generate the routing information.
     *
     * @throws \RuntimeException
     *
     * @return string the baseRoutePattern used to generate the routing information
     */
    public function getBaseRoutePattern(): string
    {
        if (null !== $this->cachedBaseRoutePattern) {
            return $this->cachedBaseRoutePattern;
        }

        if ($this->isChild()) { // the admin class is a child, prefix it with the parent route pattern
            $baseRoutePattern = $this->baseRoutePattern;
            if (!$this->baseRoutePattern) {
                preg_match(self::CLASS_REGEX, $this->class, $matches);

                if (!$matches) {
                    throw new \RuntimeException(sprintf(
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
        } elseif ($this->baseRoutePattern) {
            $this->cachedBaseRoutePattern = $this->baseRoutePattern;
        } else {
            preg_match(self::CLASS_REGEX, $this->class, $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf(
                    'Please define a default `baseRoutePattern` value for the admin class `%s`',
                    static::class
                ));
            }

            $this->cachedBaseRoutePattern = sprintf(
                '/%s%s/%s',
                empty($matches[1]) ? '' : $this->urlize($matches[1], '-').'/',
                $this->urlize($matches[3], '-'),
                $this->urlize($matches[5], '-')
            );
        }

        return $this->cachedBaseRoutePattern;
    }

    /**
     * Returns the baseRouteName used to generate the routing information.
     *
     * @throws \RuntimeException
     *
     * @return string the baseRouteName used to generate the routing information
     */
    public function getBaseRouteName(): string
    {
        if (null !== $this->cachedBaseRouteName) {
            return $this->cachedBaseRouteName;
        }

        if ($this->isChild()) { // the admin class is a child, prefix it with the parent route name
            $baseRouteName = $this->baseRouteName;
            if (!$this->baseRouteName) {
                preg_match(self::CLASS_REGEX, $this->class, $matches);

                if (!$matches) {
                    throw new \RuntimeException(sprintf(
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
        } elseif ($this->baseRouteName) {
            $this->cachedBaseRouteName = $this->baseRouteName;
        } else {
            preg_match(self::CLASS_REGEX, $this->class, $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf(
                    'Cannot automatically determine base route name,'
                    .' please define a default `baseRouteName` value for the admin class `%s`',
                    static::class
                ));
            }

            $this->cachedBaseRouteName = sprintf(
                'admin_%s%s_%s',
                empty($matches[1]) ? '' : $this->urlize($matches[1]).'_',
                $this->urlize($matches[3]),
                $this->urlize($matches[5])
            );
        }

        return $this->cachedBaseRouteName;
    }

    public function getClass(): string
    {
        if ($this->hasActiveSubClass()) {
            if ($this->hasParentFieldDescription()) {
                throw new \RuntimeException('Feature not implemented: an embedded admin cannot have subclass');
            }

            $subClass = $this->getRequest()->query->get('subclass');

            if (!$this->hasSubClass($subClass)) {
                throw new \RuntimeException(sprintf('Subclass "%s" is not defined.', $subClass));
            }

            return $this->getSubClass($subClass);
        }

        // see https://github.com/sonata-project/SonataCoreBundle/commit/247eeb0a7ca7211142e101754769d70bc402a5b4
        if ($this->subject && \is_object($this->subject)) {
            return ClassUtils::getClass($this->subject);
        }

        return $this->class;
    }

    public function getSubClasses(): array
    {
        return $this->subClasses;
    }

    public function setSubClasses(array $subClasses): void
    {
        $this->subClasses = $subClasses;
    }

    public function hasSubClass(string $name): bool
    {
        return isset($this->subClasses[$name]);
    }

    public function hasActiveSubClass(): bool
    {
        if (\count($this->subClasses) > 0 && $this->request) {
            return null !== $this->getRequest()->query->get('subclass');
        }

        return false;
    }

    public function getActiveSubClass(): string
    {
        if (!$this->hasActiveSubClass()) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no active subclass.',
                static::class
            ));
        }

        return $this->getSubClass($this->getActiveSubclassCode());
    }

    public function getActiveSubclassCode(): string
    {
        if (!$this->hasActiveSubClass()) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no active subclass.',
                static::class
            ));
        }

        $subClass = $this->getRequest()->query->get('subclass');

        if (!$this->hasSubClass($subClass)) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no active subclass.',
                static::class
            ));
        }

        return $subClass;
    }

    public function getBatchActions(): array
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

        foreach ($actions  as $name => &$action) {
            if (!\array_key_exists('label', $action)) {
                $action['label'] = $this->getTranslationLabel($name, 'batch', 'label');
            }

            if (!\array_key_exists('translation_domain', $action)) {
                $action['translation_domain'] = $this->getTranslationDomain();
            }
        }

        return $actions;
    }

    public function getRoutes(): RouteCollectionInterface
    {
        $this->buildRoutes();

        return $this->routes;
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

    public function hasRoute(string $name): bool
    {
        if (!$this->routeGenerator) {
            throw new \RuntimeException('RouteGenerator cannot be null');
        }

        return $this->routeGenerator->hasAdminRoute($this, $name);
    }

    public function isCurrentRoute(string $name, ?string $adminCode = null): bool
    {
        if (!$this->hasRequest()) {
            return false;
        }

        $request = $this->getRequest();
        $route = $request->get('_route');

        if ($adminCode) {
            $pool = $this->getConfigurationPool();

            if ($pool->hasAdminByAdminCode($adminCode)) {
                $admin = $pool->getAdminByAdminCode($adminCode);
            } else {
                return false;
            }
        } else {
            $admin = $this;
        }

        return sprintf('%s_%s', $admin->getBaseRouteName(), $name) === $route;
    }

    public function generateObjectUrl(string $name, object $object, array $parameters = [], int $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        $parameters['id'] = $this->getUrlSafeIdentifier($object);

        return $this->generateUrl($name, $parameters, $referenceType);
    }

    public function generateUrl(string $name, array $parameters = [], int $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->routeGenerator->generateUrl($this, $name, $parameters, $referenceType);
    }

    public function generateMenuUrl(string $name, array $parameters = [], int $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH): array
    {
        return $this->routeGenerator->generateMenuUrl($this, $name, $parameters, $referenceType);
    }

    final public function setTemplateRegistry(MutableTemplateRegistryInterface $templateRegistry): void
    {
        $this->templateRegistry = $templateRegistry;
    }

    /**
     * @param array<string, string> $templates
     */
    public function setTemplates(array $templates): void
    {
        $this->getTemplateRegistry()->setTemplates($templates);
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplate(string $name, string $template): void
    {
        $this->getTemplateRegistry()->setTemplate($name, $template);
    }

    public function getNewInstance(): object
    {
        $object = $this->getModelManager()->getModelInstance($this->getClass());

        $this->appendParentObject($object);

        foreach ($this->getExtensions() as $extension) {
            $extension->alterNewInstance($this, $object);
        }

        return $object;
    }

    public function getFormBuilder(): FormBuilderInterface
    {
        $this->formOptions['data_class'] = $this->getClass();

        $formBuilder = $this->getFormContractor()->getFormBuilder(
            $this->getUniqid(),
            $this->formOptions
        );

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    /**
     * This method is being called by the main admin class and the child class,
     * the getFormBuilder is only call by the main admin class.
     */
    public function defineFormBuilder(FormBuilderInterface $formBuilder): void
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

        $this->attachInlineValidator();
    }

    public function attachAdminClass(FieldDescriptionInterface $fieldDescription): void
    {
        $pool = $this->getConfigurationPool();

        $adminCode = $fieldDescription->getOption('admin_code');

        if (null !== $adminCode) {
            if (!$pool->hasAdminByAdminCode($adminCode)) {
                return;
            }

            $admin = $pool->getAdminByAdminCode($adminCode);
        } else {
            $targetModel = $fieldDescription->getTargetModel();

            if (!$pool->hasAdminByClass($targetModel)) {
                return;
            }

            $admin = $pool->getAdminByClass($targetModel);
        }

        if ($this->hasRequest()) {
            $admin->setRequest($this->getRequest());
        }

        $fieldDescription->setAssociationAdmin($admin);
    }

    public function getObject($id): ?object
    {
        $object = $this->getModelManager()->find($this->getClass(), $id);
        foreach ($this->getExtensions() as $extension) {
            $extension->alterObject($this, $object);
        }

        return $object;
    }

    public function getForm(): ?FormInterface
    {
        $this->buildForm();

        return $this->form;
    }

    public function getList(): ?FieldDescriptionCollection
    {
        $this->buildList();

        return $this->list;
    }

    final public function createQuery(): ProxyQueryInterface
    {
        $query = $this->getModelManager()->createQuery($this->getClass());

        $query = $this->configureQuery($query);
        foreach ($this->extensions as $extension) {
            $extension->configureQuery($this, $query);
        }

        return $query;
    }

    public function getDatagrid(): DatagridInterface
    {
        $this->buildDatagrid();

        return $this->datagrid;
    }

    public function buildTabMenu(string $action, ?AdminInterface $childAdmin = null): ItemInterface
    {
        if ($this->loaded['tab_menu']) {
            return $this->menu;
        }

        $this->loaded['tab_menu'] = true;

        $menu = $this->menuFactory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        $menu->setExtra('translation_domain', $this->translationDomain);

        // Prevents BC break with KnpMenuBundle v1.x
        if (method_exists($menu, 'setCurrentUri')) {
            $menu->setCurrentUri($this->getRequest()->getBaseUrl().$this->getRequest()->getPathInfo());
        }

        $this->configureTabMenu($menu, $action, $childAdmin);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureTabMenu($this, $menu, $action, $childAdmin);
        }

        $this->menu = $menu;

        return $this->menu;
    }

    public function getSideMenu(string $action, ?AdminInterface $childAdmin = null): ItemInterface
    {
        if ($this->isChild()) {
            return $this->getParent()->getSideMenu($action, $this);
        }

        $this->buildTabMenu($action, $childAdmin);

        return $this->menu;
    }

    public function getRootCode(): string
    {
        return $this->getRoot()->getCode();
    }

    public function getRoot(): AdminInterface
    {
        if (!$this->hasParentFieldDescription()) {
            return $this;
        }

        return $this->getParentFieldDescription()->getAdmin()->getRoot();
    }

    public function setBaseControllerName(string $baseControllerName): void
    {
        $this->baseControllerName = $baseControllerName;
    }

    public function getBaseControllerName(): string
    {
        return $this->baseControllerName;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setFilterPersister(?FilterPersisterInterface $filterPersister = null): void
    {
        $this->filterPersister = $filterPersister;
    }

    public function getMaxPerPage(): int
    {
        $sortValues = $this->getModelManager()->getDefaultSortValues($this->class);

        return $sortValues['_per_page'] ?? 25;
    }

    public function setMaxPageLinks(int $maxPageLinks): void
    {
        $this->maxPageLinks = $maxPageLinks;
    }

    public function getMaxPageLinks(): int
    {
        return $this->maxPageLinks;
    }

    public function getFormGroups(): array
    {
        return $this->formGroups;
    }

    public function setFormGroups(array $formGroups): void
    {
        $this->formGroups = $formGroups;
    }

    public function removeFieldFromFormGroup(string $key): void
    {
        foreach ($this->formGroups as $name => $formGroup) {
            unset($this->formGroups[$name]['fields'][$key]);

            if (empty($this->formGroups[$name]['fields'])) {
                unset($this->formGroups[$name]);
            }
        }
    }

    public function reorderFormGroup(string $group, array $keys): void
    {
        $formGroups = $this->getFormGroups();
        $formGroups[$group]['fields'] = array_merge(array_flip($keys), $formGroups[$group]['fields']);
        $this->setFormGroups($formGroups);
    }

    public function getFormTabs(): array
    {
        return $this->formTabs;
    }

    public function setFormTabs(array $formTabs): void
    {
        $this->formTabs = $formTabs;
    }

    public function getShowTabs(): array
    {
        return $this->showTabs;
    }

    public function setShowTabs(array $showTabs): void
    {
        $this->showTabs = $showTabs;
    }

    public function getShowGroups(): array
    {
        return $this->showGroups;
    }

    public function setShowGroups(array $showGroups): void
    {
        $this->showGroups = $showGroups;
    }

    public function reorderShowGroup(string $group, array $keys): void
    {
        $showGroups = $this->getShowGroups();
        $showGroups[$group]['fields'] = array_merge(array_flip($keys), $showGroups[$group]['fields']);
        $this->setShowGroups($showGroups);
    }

    public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription): void
    {
        $this->parentFieldDescription = $parentFieldDescription;
    }

    public function getParentFieldDescription(): FieldDescriptionInterface
    {
        if (!$this->hasParentFieldDescription()) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no parent field description.',
                static::class
            ));
        }

        return $this->parentFieldDescription;
    }

    public function hasParentFieldDescription(): bool
    {
        return $this->parentFieldDescription instanceof FieldDescriptionInterface;
    }

    public function setSubject(?object $subject): void
    {
        if (\is_object($subject) && !is_a($subject, $this->getClass(), true)) {
            throw new \LogicException(sprintf(
                'Admin "%s" does not allow this subject: %s, use the one register with this admin class %s',
                static::class,
                \get_class($subject),
                $this->getClass()
            ));
        }

        $this->subject = $subject;
    }

    public function getSubject(): object
    {
        if (!$this->hasSubject()) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no subject.',
                static::class
            ));
        }

        return $this->subject;
    }

    public function hasSubject(): bool
    {
        if (null === $this->subject && $this->hasRequest() && !$this->hasParentFieldDescription()) {
            $id = $this->request->get($this->getIdParameter());

            if (null !== $id) {
                $this->subject = $this->getObject($id);
            }
        }

        return null !== $this->subject;
    }

    public function getFormFieldDescriptions(): array
    {
        $this->buildForm();

        return $this->formFieldDescriptions;
    }

    public function getFormFieldDescription(string $name): FieldDescriptionInterface
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
    public function hasFormFieldDescription(string $name): bool
    {
        $this->buildForm();

        return \array_key_exists($name, $this->formFieldDescriptions) ? true : false;
    }

    public function addFormFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->formFieldDescriptions[$name] = $fieldDescription;
    }

    /**
     * remove a FieldDescription.
     */
    public function removeFormFieldDescription(string $name): void
    {
        unset($this->formFieldDescriptions[$name]);
    }

    /**
     * build and return the collection of form FieldDescription.
     *
     * @return FieldDescriptionInterface[] collection of form FieldDescription
     */
    public function getShowFieldDescriptions(): array
    {
        $this->buildShow();

        return $this->showFieldDescriptions;
    }

    /**
     * Returns the form FieldDescription with the given $name.
     */
    public function getShowFieldDescription(string $name): FieldDescriptionInterface
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

    public function hasShowFieldDescription(string $name): bool
    {
        $this->buildShow();

        return \array_key_exists($name, $this->showFieldDescriptions);
    }

    public function addShowFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->showFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeShowFieldDescription(string $name): void
    {
        unset($this->showFieldDescriptions[$name]);
    }

    public function getListFieldDescriptions(): array
    {
        $this->buildList();

        return $this->listFieldDescriptions;
    }

    public function getListFieldDescription(string $name): FieldDescriptionInterface
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

    public function hasListFieldDescription(string $name): bool
    {
        $this->buildList();

        return \array_key_exists($name, $this->listFieldDescriptions) ? true : false;
    }

    public function addListFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->listFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeListFieldDescription(string $name): void
    {
        unset($this->listFieldDescriptions[$name]);
    }

    public function getFilterFieldDescription(string $name): FieldDescriptionInterface
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

    public function hasFilterFieldDescription(string $name): bool
    {
        $this->buildDatagrid();

        return \array_key_exists($name, $this->filterFieldDescriptions) ? true : false;
    }

    public function addFilterFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->filterFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeFilterFieldDescription(string $name): void
    {
        unset($this->filterFieldDescriptions[$name]);
    }

    public function getFilterFieldDescriptions(): array
    {
        $this->buildDatagrid();

        return $this->filterFieldDescriptions;
    }

    public function addChild(AdminInterface $child, string $field): void
    {
        $parentAdmin = $this;
        while ($parentAdmin->isChild() && $parentAdmin->getCode() !== $child->getCode()) {
            $parentAdmin = $parentAdmin->getParent();
        }

        if ($parentAdmin->getCode() === $child->getCode()) {
            throw new \RuntimeException(sprintf(
                'Circular reference detected! The child admin `%s` is already in the parent tree of the `%s` admin.',
                $child->getCode(),
                $this->getCode()
            ));
        }

        $this->children[$child->getCode()] = $child;

        $child->setParent($this);
        $child->addParentAssociationMapping($this->getCode(), $field);
    }

    public function hasChild(string $code): bool
    {
        return isset($this->children[$code]);
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getChild(string $code): AdminInterface
    {
        if (!$this->hasChild($code)) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no child for the code %s.',
                static::class,
                $code
            ));
        }

        return $this->children[$code];
    }

    public function setParent(AdminInterface $parent): void
    {
        $this->parent = $parent;
    }

    public function getParent(): AdminInterface
    {
        if (!$this->isChild()) {
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

    public function isChild(): bool
    {
        return $this->parent instanceof AdminInterface;
    }

    /**
     * Returns true if the admin has children, false otherwise.
     */
    public function hasChildren(): bool
    {
        return \count($this->children) > 0;
    }

    public function setUniqid(string $uniqid): void
    {
        $this->uniqid = $uniqid;
    }

    public function getUniqid(): string
    {
        if (!$this->uniqid) {
            $this->uniqid = sprintf('s%s', uniqid());
        }

        return $this->uniqid;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassnameLabel(): string
    {
        return $this->classnameLabel;
    }

    public function getPersistentParameters(): array
    {
        $parameters = [];

        foreach ($this->getExtensions() as $extension) {
            $params = $extension->getPersistentParameters($this);

            $parameters = array_merge($parameters, $params);
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameter(string $name)
    {
        $parameters = $this->getPersistentParameters();

        return $parameters[$name] ?? null;
    }

    public function setCurrentChild(bool $currentChild): void
    {
        $this->currentChild = $currentChild;
    }

    public function isCurrentChild(): bool
    {
        return $this->currentChild;
    }

    /**
     * Returns the current child admin instance.
     *
     * @return AdminInterface|null the current child admin instance
     */
    public function getCurrentChildAdmin(): ?AdminInterface
    {
        foreach ($this->children as $children) {
            if ($children->isCurrentChild()) {
                return $children;
            }
        }

        return null;
    }

    public function setTranslationDomain(string $translationDomain): void
    {
        $this->translationDomain = $translationDomain;
    }

    public function getTranslationDomain(): string
    {
        return $this->translationDomain;
    }

    /**
     * {@inheritdoc}
     *
     * NEXT_MAJOR: remove this method
     *
     * @deprecated since sonata-project/admin-bundle 3.9, to be removed with 4.0
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $args = \func_get_args();
        if (isset($args[1]) && $args[1]) {
            @trigger_error(sprintf(
                'The %s method is deprecated since version 3.9 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $this->translator = $translator;
    }

    public function getTranslationLabel(string $label, string $context = '', string $type = ''): string
    {
        return $this->getLabelTranslatorStrategy()->getLabel($label, $context, $type);
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;

        foreach ($this->getChildren() as $children) {
            $children->setRequest($request);
        }
    }

    public function getRequest(): Request
    {
        if (!$this->request) {
            throw new \LogicException('The Request object has not been set');
        }

        return $this->request;
    }

    public function hasRequest(): bool
    {
        return null !== $this->request;
    }

    public function setFormContractor(FormContractorInterface $formBuilder): void
    {
        $this->formContractor = $formBuilder;
    }

    public function getFormContractor(): ?FormContractorInterface
    {
        return $this->formContractor;
    }

    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder): void
    {
        $this->datagridBuilder = $datagridBuilder;
    }

    public function getDatagridBuilder(): ?DatagridBuilderInterface
    {
        return $this->datagridBuilder;
    }

    public function setListBuilder(ListBuilderInterface $listBuilder): void
    {
        $this->listBuilder = $listBuilder;
    }

    public function getListBuilder(): ?ListBuilderInterface
    {
        return $this->listBuilder;
    }

    public function setShowBuilder(?ShowBuilderInterface $showBuilder): void
    {
        $this->showBuilder = $showBuilder;
    }

    public function getShowBuilder(): ?ShowBuilderInterface
    {
        return $this->showBuilder;
    }

    public function setConfigurationPool(Pool $configurationPool): void
    {
        $this->configurationPool = $configurationPool;
    }

    public function getConfigurationPool(): ?Pool
    {
        return $this->configurationPool;
    }

    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator): void
    {
        $this->routeGenerator = $routeGenerator;
    }

    public function getRouteGenerator(): ?RouteGeneratorInterface
    {
        return $this->routeGenerator;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getBaseCodeRoute(): string
    {
        if ($this->isChild()) {
            return $this->getParent()->getBaseCodeRoute().'|'.$this->getCode();
        }

        return $this->getCode();
    }

    public function getModelManager(): ?ModelManagerInterface
    {
        return $this->modelManager;
    }

    public function setModelManager(?ModelManagerInterface $modelManager): void
    {
        $this->modelManager = $modelManager;
    }

    public function getManagerType(): ?string
    {
        return $this->managerType;
    }

    public function setManagerType(?string $type): void
    {
        $this->managerType = $type;
    }

    public function getObjectIdentifier()
    {
        return $this->getCode();
    }

    /**
     * Set the roles and permissions per role.
     */
    public function setSecurityInformation(array $information): void
    {
        $this->securityInformation = $information;
    }

    public function getSecurityInformation(): array
    {
        return $this->securityInformation;
    }

    /**
     * Return the list of permissions the user should have in order to display the admin.
     */
    public function getPermissionsShow(string $context): array
    {
        return ['LIST'];
    }

    public function showIn(string $context): bool
    {
        return $this->isGranted($this->getPermissionsShow($context));
    }

    public function createObjectSecurity(object $object): void
    {
        $this->getSecurityHandler()->createObjectSecurity($this, $object);
    }

    public function setSecurityHandler(SecurityHandlerInterface $securityHandler): void
    {
        $this->securityHandler = $securityHandler;
    }

    public function getSecurityHandler(): ?SecurityHandlerInterface
    {
        return $this->securityHandler;
    }

    /**
     * NEXT_MAJOR: Decide the type declaration for the $name argument, since it is
     * passed as argument 1 for `SecurityHandlerInterface::isGranted()`, which
     * accepts string and array.
     */
    public function isGranted($name, ?object $object = null): bool
    {
        $objectRef = $object ? sprintf('/%s#%s', spl_object_hash($object), $this->id($object)) : '';
        $key = md5(json_encode($name).$objectRef);

        if (!\array_key_exists($key, $this->cacheIsGranted)) {
            $this->cacheIsGranted[$key] = $this->securityHandler->isGranted($this, $name, $object ?: $this);
        }

        return $this->cacheIsGranted[$key];
    }

    /**
     * NEXT_MAJOR: Decide the type declaration for the $model argument, since it is
     * passed as argument 1 for `ModelManagerInterface::getUrlSafeIdentifier()`, which
     * accepts null.
     */
    public function getUrlSafeIdentifier($model): ?string
    {
        return $this->getModelManager()->getUrlSafeIdentifier($model);
    }

    /**
     * NEXT_MAJOR: Decide the type declaration for the $model argument, since it is
     * passed as argument 1 for `ModelManagerInterface::getNormalizedIdentifier()`, which
     * accepts null.
     */
    public function getNormalizedIdentifier($model): ?string
    {
        return $this->getModelManager()->getNormalizedIdentifier($model);
    }

    /**
     * NEXT_MAJOR: Decide the type declaration for the $model argument, since it is
     * passed as argument 1 for `ModelManagerInterface::getNormalizedIdentifier()`, which
     * accepts null.
     */
    public function id($model): ?string
    {
        return $this->getNormalizedIdentifier($model);
    }

    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    public function getValidator(): ?ValidatorInterface
    {
        return $this->validator;
    }

    public function getShow(): ?FieldDescriptionCollection
    {
        $this->buildShow();

        return $this->show;
    }

    public function setFormTheme(array $formTheme): void
    {
        $this->formTheme = $formTheme;
    }

    public function getFormTheme(): array
    {
        return $this->formTheme;
    }

    public function setFilterTheme(array $filterTheme): void
    {
        $this->filterTheme = $filterTheme;
    }

    public function getFilterTheme(): array
    {
        return $this->filterTheme;
    }

    public function addExtension(AdminExtensionInterface $extension): void
    {
        $this->extensions[] = $extension;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function setMenuFactory(FactoryInterface $menuFactory): void
    {
        $this->menuFactory = $menuFactory;
    }

    public function getMenuFactory(): ?FactoryInterface
    {
        return $this->menuFactory;
    }

    public function setRouteBuilder(RouteBuilderInterface $routeBuilder): void
    {
        $this->routeBuilder = $routeBuilder;
    }

    public function getRouteBuilder(): ?RouteBuilderInterface
    {
        return $this->routeBuilder;
    }

    /**
     * NEXT_MAJOR: Decide the type declaration for the $object argument, since there
     * are tests ensuring to accept null (`GetShortObjectDescriptionActionTest::testGetShortObjectDescriptionActionEmptyObjectIdAsJson()`).
     */
    public function toString($object): string
    {
        if (!\is_object($object)) {
            return '';
        }

        if (method_exists($object, '__toString') && null !== $object->__toString()) {
            return (string) $object;
        }

        return sprintf('%s:%s', ClassUtils::getClass($object), spl_object_hash($object));
    }

    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy): void
    {
        $this->labelTranslatorStrategy = $labelTranslatorStrategy;
    }

    public function getLabelTranslatorStrategy(): ?LabelTranslatorStrategyInterface
    {
        return $this->labelTranslatorStrategy;
    }

    public function supportsPreviewMode(): bool
    {
        return $this->supportsPreviewMode;
    }

    /**
     * Returns predefined per page options.
     */
    public function getPerPageOptions(): array
    {
        $perPageOptions = $this->getModelManager()->getDefaultPerPageOptions($this->class);
        $perPageOptions[] = $this->getMaxPerPage();

        $perPageOptions = array_unique($perPageOptions);
        sort($perPageOptions);

        return $perPageOptions;
    }

    /**
     * Set pager type.
     */
    public function setPagerType(string $pagerType): void
    {
        $this->pagerType = $pagerType;
    }

    /**
     * Get pager type.
     */
    public function getPagerType(): string
    {
        return $this->pagerType;
    }

    /**
     * Returns true if the per page value is allowed, false otherwise.
     */
    public function determinedPerPageValue(int $perPage): bool
    {
        return \in_array($perPage, $this->getPerPageOptions(), true);
    }

    public function isAclEnabled(): bool
    {
        return $this->getSecurityHandler() instanceof AclSecurityHandlerInterface;
    }

    /**
     * NEXT_MAJOR: Decide the type declaration for the $object argument, since it is
     * passed as argument 1 to `toString()` method, which currently accepts null.
     */
    public function getObjectMetadata($object): MetadataInterface
    {
        return new Metadata($this->toString($object));
    }

    public function getListModes(): array
    {
        return $this->listModes;
    }

    public function setListMode(string $mode): void
    {
        if (!$this->hasRequest()) {
            throw new \RuntimeException(sprintf('No request attached to the current admin: %s', $this->getCode()));
        }

        $this->getRequest()->getSession()->set(sprintf('%s.list_mode', $this->getCode()), $mode);
    }

    public function getListMode(): string
    {
        if (!$this->hasRequest()) {
            return 'list';
        }

        return $this->getRequest()->getSession()->get(sprintf('%s.list_mode', $this->getCode()), 'list');
    }

    public function getAccessMapping(): array
    {
        return $this->accessMapping;
    }

    public function checkAccess(string $action, ?object $object = null): void
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

    /**
     * {@inheritdoc}
     */
    public function hasAccess(string $action, ?object $object = null): bool
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

    final public function getActionButtons(string $action, ?object $object = null): array
    {
        $buttonList = [];

        if (\in_array($action, ['tree', 'show', 'edit', 'delete', 'list', 'batch'], true)
            && $this->hasRoute('create')
            && $this->hasAccess('create')
        ) {
            $buttonList['create'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_create'),
            ];
        }

        if (\in_array($action, ['show', 'delete', 'acl', 'history'], true)
            && $this->hasRoute('edit')
            && $this->canAccessObject('edit', $object)
        ) {
            $buttonList['edit'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_edit'),
            ];
        }

        if (\in_array($action, ['show', 'edit', 'acl'], true)
            && $this->hasRoute('history')
            && $this->canAccessObject('history', $object)
        ) {
            $buttonList['history'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_history'),
            ];
        }

        if (\in_array($action, ['edit', 'history'], true)
            && $this->isAclEnabled()
            && $this->hasRoute('acl')
            && $this->canAccessObject('acl', $object)
        ) {
            $buttonList['acl'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_acl'),
            ];
        }

        if (\in_array($action, ['edit', 'history', 'acl'], true)
            && $this->hasRoute('show')
            && $this->canAccessObject('show', $object)
            && \count($this->getShow()) > 0
        ) {
            $buttonList['show'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_show'),
            ];
        }

        if (\in_array($action, ['show', 'edit', 'delete', 'acl', 'batch'], true)
            && $this->hasRoute('list')
            && $this->hasAccess('list')
        ) {
            $buttonList['list'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_list'),
            ];
        }

        $buttonList = $this->configureActionButtons($buttonList, $action, $object);

        foreach ($this->getExtensions() as $extension) {
            $buttonList = $extension->configureActionButtons($this, $buttonList, $action, $object);
        }

        return $buttonList;
    }

    /**
     * {@inheritdoc}
     */
    public function getDashboardActions(): array
    {
        $actions = [];

        if ($this->hasRoute('create') && $this->hasAccess('create')) {
            $actions['create'] = [
                'label' => 'link_add',
                'translation_domain' => 'SonataAdminBundle',
                'template' => $this->getTemplateRegistry()->getTemplate('action_create'),
                'url' => $this->generateUrl('create'),
                'icon' => 'plus-circle',
            ];
        }

        if ($this->hasRoute('list') && $this->hasAccess('list')) {
            $actions['list'] = [
                'label' => 'link_list',
                'translation_domain' => 'SonataAdminBundle',
                'url' => $this->generateUrl('list'),
                'icon' => 'list',
            ];
        }

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    final public function showMosaicButton($isShown): void
    {
        if ($isShown) {
            $this->listModes['mosaic'] = ['class' => static::MOSAIC_ICON_CLASS];
        } else {
            unset($this->listModes['mosaic']);
        }
    }

    final public function getSearchResultLink(object $object): ?string
    {
        foreach ($this->searchResultActions as $action) {
            if ($this->hasRoute($action) && $this->hasAccess($action, $object)) {
                return $this->generateObjectUrl($action, $object);
            }
        }

        return null;
    }

    public function canAccessObject(string $action, ?object $object = null): bool
    {
        if (!\is_object($object)) {
            return false;
        }
        if (!$this->id($object)) {
            return false;
        }

        return $this->hasAccess($action, $object);
    }

    public function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        return $buttonList;
    }

    /**
     * Hook to run after initialization.
     */
    protected function configure(): void
    {
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
        return strtolower(preg_replace('/[^a-z0-9_]/i', $sep.'$1', $word));
    }

    final protected function getTemplateRegistry(): MutableTemplateRegistryInterface
    {
        return $this->templateRegistry;
    }

    /**
     * Returns a list of default sort values.
     *
     * @return array{_page?: int, _per_page?: int, _sort_by?: string, _sort_order?: string}
     */
    final protected function getDefaultSortValues(): array
    {
        $defaultSortValues = [];

        $this->configureDefaultSortValues($defaultSortValues);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureDefaultSortValues($this, $defaultSortValues);
        }

        return $defaultSortValues;
    }

    /**
     * Returns a list of default filters.
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

    protected function configureFormFields(FormMapper $form): void
    {
    }

    protected function configureListFields(ListMapper $list): void
    {
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
    }

    protected function configureShowFields(ShowMapper $show): void
    {
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
    }

    /**
     * Allows you to customize batch actions.
     *
     * @param array $actions List of actions
     */
    protected function configureBatchActions(array $actions): array
    {
        return $actions;
    }

    /**
     * Configures the tab menu in your admin.
     */
    protected function configureTabMenu(ItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
    }

    /**
     * build the view FieldDescription array.
     */
    protected function buildShow(): void
    {
        if ($this->loaded['show']) {
            return;
        }

        $this->loaded['show'] = true;

        $this->show = $this->getShowBuilder()->getBaseList();
        $mapper = new ShowMapper($this->getShowBuilder(), $this->show, $this);

        $this->configureShowFields($mapper);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureShowFields($mapper);
        }
    }

    /**
     * build the list FieldDescription array.
     */
    protected function buildList(): void
    {
        if ($this->loaded['list']) {
            return;
        }

        $this->loaded['list'] = true;

        $this->list = $this->getListBuilder()->getBaseList();
        $mapper = new ListMapper($this->getListBuilder(), $this->list, $this);

        if (\count($this->getBatchActions()) > 0 && $this->hasRequest() && !$this->getRequest()->isXmlHttpRequest()) {
            $fieldDescription = $this->getModelManager()->getNewFieldDescriptionInstance(
                $this->getClass(),
                'batch',
                [
                    'label' => 'batch',
                    'code' => '_batch',
                    'sortable' => false,
                    'virtual_field' => true,
                ]
            );

            $fieldDescription->setAdmin($this);
            $fieldDescription->setTemplate($this->getTemplateRegistry()->getTemplate('batch'));

            $mapper->add($fieldDescription, ListMapper::TYPE_BATCH);
        }

        $this->configureListFields($mapper);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureListFields($mapper);
        }

        if ($this->hasRequest() && $this->getRequest()->isXmlHttpRequest()) {
            $fieldDescription = $this->getModelManager()->getNewFieldDescriptionInstance(
                $this->getClass(),
                'select',
                [
                    'label' => false,
                    'code' => '_select',
                    'sortable' => false,
                    'virtual_field' => false,
                ]
            );

            $fieldDescription->setAdmin($this);
            $fieldDescription->setTemplate($this->getTemplateRegistry()->getTemplate('select'));

            $mapper->add($fieldDescription, ListMapper::TYPE_SELECT);
        }
    }

    /**
     * Build the form FieldDescription collection.
     */
    protected function buildForm(): void
    {
        if ($this->loaded['form']) {
            return;
        }

        $this->loaded['form'] = true;

        $formBuilder = $this->getFormBuilder();
        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $this->preValidate($event->getData());
        }, 100);

        $this->form = $formBuilder->getForm();
    }

    /**
     * Gets the subclass corresponding to the given name.
     *
     * @param string $name The name of the sub class
     *
     * @return string the subclass
     */
    protected function getSubClass(string $name): string
    {
        if ($this->hasSubClass($name)) {
            return $this->subClasses[$name];
        }

        throw new \LogicException(sprintf('Unable to find the subclass `%s` for admin `%s`', $name, static::class));
    }

    /**
     * Attach the inline validator to the model metadata, this must be done once per admin.
     */
    protected function attachInlineValidator(): void
    {
        $admin = $this;

        // add the custom inline validation option
        $metadata = $this->validator->getMetadataFor($this->getClass());
        if (!$metadata instanceof GenericMetadata) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Cannot add inline validator for %s because its metadata is an instance of %s instead of %s',
                    $this->getClass(),
                    \get_class($metadata),
                    GenericMetadata::class
                )
            );
        }

        $metadata->addConstraint(new InlineConstraint([
            'service' => $this,
            'method' => static function (ErrorElement $errorElement, $object) use ($admin): void {
                /* @var \Sonata\AdminBundle\Admin\AdminInterface $admin */

                // This avoid the main validation to be cascaded to children
                // The problem occurs when a model Page has a collection of Page as property
                if ($admin->hasSubject() && spl_object_hash($object) !== spl_object_hash($admin->getSubject())) {
                    return;
                }

                $admin->validate($errorElement, $object);

                foreach ($admin->getExtensions() as $extension) {
                    $extension->validate($admin, $errorElement, $object);
                }
            },
            'serializingWarning' => true,
        ]));
    }

    /**
     * Return list routes with permissions name.
     *
     * @return array<string, string|array>
     */
    protected function getAccess(): array
    {
        $access = array_merge([
            'acl' => 'MASTER',
            'export' => 'EXPORT',
            'historyCompareRevisions' => 'EDIT',
            'historyViewRevision' => 'EDIT',
            'history' => 'EDIT',
            'edit' => 'EDIT',
            'show' => 'VIEW',
            'create' => 'CREATE',
            'delete' => 'DELETE',
            'batchDelete' => 'DELETE',
            'list' => 'LIST',
        ], $this->getAccessMapping());

        foreach ($this->extensions as $extension) {
            $access = array_merge($access, $extension->getAccessMapping($this));
        }

        return $access;
    }

    /**
     * Configures a list of default filters.
     */
    protected function configureDefaultFilterValues(array &$filterValues): void
    {
    }

    /**
     * Configures a list of default sort values.
     *
     * Example:
     *   $sortValues['_sort_by'] = 'foo'
     *   $sortValues['_sort_order'] = 'DESC'
     */
    protected function configureDefaultSortValues(array &$sortValues)
    {
    }

    /**
     * Set the parent object, if any, to the provided object.
     */
    final protected function appendParentObject(object $object): void
    {
        if ($this->isChild() && $this->getParentAssociationMapping()) {
            $parentAdmin = $this->getParent();
            $parentObject = $parentAdmin->getObject($this->request->get($parentAdmin->getIdParameter()));

            if (null !== $parentObject) {
                $propertyAccessor = $this->getConfigurationPool()->getPropertyAccessor();
                $propertyPath = new PropertyPath($this->getParentAssociationMapping());

                $value = $propertyAccessor->getValue($object, $propertyPath);

                if (\is_array($value) || $value instanceof \ArrayAccess) {
                    $value[] = $parentObject;
                    $propertyAccessor->setValue($object, $propertyPath, $value);
                } else {
                    $propertyAccessor->setValue($object, $propertyPath, $parentObject);
                }
            }
        } elseif ($this->hasParentFieldDescription()) {
            $parentAdmin = $this->getParentFieldDescription()->getAdmin();
            $parentObject = $parentAdmin->getObject($this->request->get($parentAdmin->getIdParameter()));

            if (null !== $parentObject) {
                ObjectManipulator::setObject($object, $parentObject, $this->getParentFieldDescription());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    private function buildDatagrid(): void
    {
        if ($this->loaded['datagrid']) {
            return;
        }

        $this->loaded['datagrid'] = true;

        $filterParameters = $this->getFilterParameters();

        // transform _sort_by from a string to a FieldDescriptionInterface for the datagrid.
        if (isset($filterParameters['_sort_by']) && \is_string($filterParameters['_sort_by'])) {
            if ($this->hasListFieldDescription($filterParameters['_sort_by'])) {
                $filterParameters['_sort_by'] = $this->getListFieldDescription($filterParameters['_sort_by']);
            } else {
                $filterParameters['_sort_by'] = $this->getModelManager()->getNewFieldDescriptionInstance(
                    $this->getClass(),
                    $filterParameters['_sort_by'],
                    []
                );

                $this->getListBuilder()->buildField(null, $filterParameters['_sort_by'], $this);
            }
        }

        // initialize the datagrid
        $this->datagrid = $this->getDatagridBuilder()->getBaseDatagrid($this, $filterParameters);

        $this->datagrid->getPager()->setMaxPageLinks($this->maxPageLinks);

        $mapper = new DatagridMapper($this->getDatagridBuilder(), $this->datagrid, $this);

        // build the datagrid filter
        $this->configureDatagridFilters($mapper);

        // ok, try to limit to add parent filter
        if ($this->isChild() && $this->getParentAssociationMapping() && !$mapper->has($this->getParentAssociationMapping())) {
            $mapper->add($this->getParentAssociationMapping(), null, [
                'show_filter' => false,
                'label' => false,
                'field_type' => ModelHiddenType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                ],
                'operator_type' => HiddenType::class,
            ], null, null, [
                'admin_code' => $this->getParent()->getCode(),
            ]);
        }

        foreach ($this->getExtensions() as $extension) {
            $extension->configureDatagridFilters($mapper);
        }
    }

    /**
     * Build all the related urls to the current admin.
     */
    private function buildRoutes(): void
    {
        if ($this->loaded['routes']) {
            return;
        }

        $this->loaded['routes'] = true;

        $this->routes = new RouteCollection(
            $this->getBaseCodeRoute(),
            $this->getBaseRouteName(),
            $this->getBaseRoutePattern(),
            $this->getBaseControllerName()
        );

        $this->routeBuilder->build($this, $this->routes);

        $this->configureRoutes($this->routes);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureRoutes($this, $this->routes);
        }
    }
}
