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

namespace Sonata\AdminBundle\DependencyInjection\Admin;

use Knp\Menu\FactoryInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Exporter\DataSourceInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @phpstan-template T of object
 */
abstract class AbstractTaggedAdmin implements TaggedAdminInterface
{
    /** @deprecated since sonata-project/sonata-admin 3.9, will be removed in 4.0 */
    public const MOSAIC_ICON_CLASS = 'fa fa-th-large fa-fw';

    public const DEFAULT_LIST_MODES = [
        'list' => ['class' => 'fa fa-list fa-fw'],
        'mosaic' => ['class' => 'fa fa-th-large fa-fw'],
    ];

    /**
     * The code related to the admin.
     *
     * @var string
     */
    protected $code;

    /**
     * The class name managed by the admin class.
     *
     * @var string
     *
     * @phpstan-var class-string<T>
     */
    protected $class;

    /**
     * The base name controller used to generate the routing information.
     *
     * @var string
     */
    protected $baseControllerName;

    /**
     * @var string|null
     */
    protected $label;

    /**
     * @var array<string, array<string, string>>
     *
     * @phpstan-var array{list: array{class: string}, mosaic: array{class: string}}
     */
    protected $listModes = self::DEFAULT_LIST_MODES;

    /**
     * @var string
     */
    protected $pagerType = Pager::TYPE_DEFAULT;

    /**
     * The manager type to use for the admin.
     *
     * @var string|null
     */
    protected $managerType;

    /**
     * Roles and permissions per role.
     *
     * @var array<string, string[]> 'role' => ['permission1', 'permission2']
     */
    protected $securityInformation = [];

    /**
     * Whether or not to persist the filters in the session.
     *
     * NEXT_MAJOR: remove this property
     *
     * @var bool
     *
     * @deprecated since sonata-project/admin-bundle 3.34, to be removed in 4.0.
     */
    protected $persistFilters = false;

    /**
     * Component responsible for persisting filters.
     *
     * @var FilterPersisterInterface|null
     */
    protected $filterPersister;

    /**
     * The Entity or Document manager.
     *
     * @var ModelManagerInterface|null
     */
    protected $modelManager;

    /**
     * @var DataSourceInterface|null
     */
    protected $dataSource;

    /**
     * The related form contractor.
     *
     * @var FormContractorInterface|null
     */
    protected $formContractor;

    /**
     * The related view builder.
     *
     * @var ShowBuilderInterface|null
     */
    protected $showBuilder;

    /**
     * The related list builder.
     *
     * @var ListBuilderInterface|null
     */
    protected $listBuilder;

    /**
     * The related datagrid builder.
     *
     * @var DatagridBuilderInterface|null
     */
    protected $datagridBuilder;

    /**
     * The translator component.
     *
     * @var TranslatorInterface|null
     */
    protected $translator;

    /**
     * The configuration pool.
     *
     * @var Pool|null
     */
    protected $configurationPool;

    /**
     * The router instance.
     *
     * @var RouteGeneratorInterface|null
     */
    protected $routeGenerator;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.83 and will be removed in 4.0
     *
     * @var ValidatorInterface|null
     */
    protected $validator;

    /**
     * @var SecurityHandlerInterface|null
     */
    protected $securityHandler;

    /**
     * @var FactoryInterface|null
     */
    protected $menuFactory;

    /**
     * @var RouteBuilderInterface|null
     */
    protected $routeBuilder;

    /**
     * @var LabelTranslatorStrategyInterface|null
     */
    protected $labelTranslatorStrategy;

    /**
     * @var FieldDescriptionFactoryInterface|null
     */
    private $fieldDescriptionFactory;

    /**
     * NEXT_MAJOR: Change signature to __construct(string $code, string $class, string $baseControllerName).
     *
     * @param string      $code
     * @param string      $class
     * @param string|null $baseControllerName
     */
    public function __construct($code, $class, $baseControllerName = null)
    {
        if (!\is_string($code)) {
            @trigger_error(sprintf(
                'Passing other type than string as argument 1 for method %s() is deprecated since'
                .' sonata-project/admin-bundle 3.65. It will accept only string in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }
        $this->code = $code;

        if (!\is_string($class)) {
            @trigger_error(sprintf(
                'Passing other type than string as argument 2 for method %s() is deprecated since'
                .' sonata-project/admin-bundle 3.65. It will accept only string in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }
        $this->class = $class;

        if (!\is_string($baseControllerName)) {
            @trigger_error(sprintf(
                'Passing other type than string as argument 3 for method %s() is deprecated since'
                .' sonata-project/admin-bundle 3.84. It will accept only string in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }
        $this->baseControllerName = $baseControllerName;
    }

    abstract public function initialize();

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @deprecated since sonata-project/sonata-admin 3.9, use setListModes in 4.x
     */
    final public function showMosaicButton($isShown)
    {
        if ($isShown) {
            $this->listModes['mosaic'] = ['class' => static::MOSAIC_ICON_CLASS];
        } else {
            unset($this->listModes['mosaic']);
        }
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getListModes()
    {
        return $this->listModes;
    }

    /**
     * @param array $listModes
     *
     * @return void
     */
    public function setListModes(array $listModes): void
    {
        $this->listModes = $listModes;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setPagerType($pagerType)
    {
        $this->pagerType = $pagerType;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     *
     * @return string
     */
    public function getPagerType()
    {
        return $this->pagerType;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setManagerType($type)
    {
        $this->managerType = $type;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getManagerType()
    {
        if (null === $this->managerType) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no manager type is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no manager type.',
//                static::class
//            ));
        }

        return $this->managerType;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     *
     * @param array<string, string[]> $information
     */
    public function setSecurityInformation(array $information)
    {
        $this->securityInformation = $information;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     *
     * @return array<string, string[]>
     */
    public function getSecurityInformation()
    {
        return $this->securityInformation;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setFilterPersister(?FilterPersisterInterface $filterPersister = null)
    {
        $this->filterPersister = $filterPersister;
        // NEXT_MAJOR remove the deprecated property will be removed. Needed for persisted filter condition.
        $this->persistFilters = true;
    }

    final public function getFilterPersister(): FilterPersisterInterface
    {
        if (!$this->hasFilterPersister()) {
            throw new \LogicException(sprintf('Admin "%s" has no filter persister.', static::class));
        }

        return $this->filterPersister;
    }

    final public function hasFilterPersister(): bool
    {
        return null !== $this->filterPersister;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setModelManager(ModelManagerInterface $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getModelManager()
    {
        if (null === $this->modelManager) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no model manager is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no model manager.',
//                static::class
//            ));
        }

        return $this->modelManager;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setDataSource(DataSourceInterface $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * NEXT_MAJOR: Change typehint for DataSourceInterface.
     */
    public function getDataSource(): ?DataSourceInterface
    {
        if (null === $this->dataSource) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no data source is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no data source.',
//                static::class
//            ));
        }

        return $this->dataSource;
    }

    final public function setFieldDescriptionFactory(FieldDescriptionFactoryInterface $fieldDescriptionFactory): void
    {
        $this->fieldDescriptionFactory = $fieldDescriptionFactory;
    }

    /**
     * NEXT_MAJOR: Change typehint for FieldDescriptionFactoryInterface.
     */
    public function getFieldDescriptionFactory(): ?FieldDescriptionFactoryInterface
    {
        if (null === $this->fieldDescriptionFactory) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no field description factory is set is deprecated since sonata-project/admin-bundle 3.92'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no field description factory.',
//                static::class
//            ));
        }

        return $this->fieldDescriptionFactory;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setFormContractor(FormContractorInterface $formBuilder)
    {
        $this->formContractor = $formBuilder;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getFormContractor()
    {
        if (null === $this->formContractor) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no form contractor is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no form contractor.',
//                static::class
//            ));
        }

        return $this->formContractor;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setShowBuilder(ShowBuilderInterface $showBuilder)
    {
        $this->showBuilder = $showBuilder;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getShowBuilder()
    {
        if (null === $this->showBuilder) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no show builder is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no show builder.',
//                static::class
//            ));
        }

        return $this->showBuilder;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setListBuilder(ListBuilderInterface $listBuilder)
    {
        $this->listBuilder = $listBuilder;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getListBuilder()
    {
        if (null === $this->listBuilder) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no list build is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no list builder.',
//                static::class
//            ));
        }

        return $this->listBuilder;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder)
    {
        $this->datagridBuilder = $datagridBuilder;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getDatagridBuilder()
    {
        if (null === $this->datagridBuilder) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no datagrid builder is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no datagrid builder.',
//                static::class
//            ));
        }

        return $this->datagridBuilder;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.83
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.83
     */
    public function getTranslator()
    {
        if (null === $this->translator) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no translator is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no translator.',
//                static::class
//            ));
        }

        return $this->translator;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setConfigurationPool(Pool $configurationPool)
    {
        $this->configurationPool = $configurationPool;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getConfigurationPool()
    {
        if (null === $this->configurationPool) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            if ('sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
                @trigger_error(sprintf(
                    'Calling %s() when no pool is set is deprecated since sonata-project/admin-bundle 3.84'
                    .' and will throw a LogicException in 4.0',
                    __METHOD__,
                ), \E_USER_DEPRECATED);
            }
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no pool.',
//                static::class
//            ));
        }

        return $this->configurationPool;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator)
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getRouteGenerator()
    {
        if (null === $this->routeGenerator) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no route generator is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no route generator.',
//                static::class
//            ));
        }

        return $this->routeGenerator;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.83 and will be removed in 4.0
     */
    public function setValidator($validator)
    {
        // NEXT_MAJOR: Move ValidatorInterface check to method signature
        if (!$validator instanceof ValidatorInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 must be an instance of %s',
                ValidatorInterface::class
            ));
        }

        $this->validator = $validator;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.83 and will be removed in 4.0
     */
    public function getValidator()
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since version 3.83 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->validator;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setSecurityHandler(SecurityHandlerInterface $securityHandler)
    {
        $this->securityHandler = $securityHandler;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getSecurityHandler()
    {
        if (null === $this->securityHandler) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no security handler is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no security handler.',
//                static::class
//            ));
        }

        return $this->securityHandler;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setMenuFactory(FactoryInterface $menuFactory)
    {
        $this->menuFactory = $menuFactory;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getMenuFactory()
    {
        if (null === $this->menuFactory) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no security handler is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no security handler.',
//                static::class
//            ));
        }

        return $this->menuFactory;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setRouteBuilder(RouteBuilderInterface $routeBuilder)
    {
        $this->routeBuilder = $routeBuilder;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getRouteBuilder()
    {
        if (null === $this->routeBuilder) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no route builder is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no route builder.',
//                static::class
//            ));
        }

        return $this->routeBuilder;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy)
    {
        $this->labelTranslatorStrategy = $labelTranslatorStrategy;
    }

    /**
     * @final since sonata-admin/admin-bundle 3.84
     */
    public function getLabelTranslatorStrategy()
    {
        if (null === $this->labelTranslatorStrategy) {
            // NEXT_MAJOR: Remove this deprecation and uncomment the following exception
            @trigger_error(sprintf(
                'Calling %s() when no label translator strategy is set is deprecated since sonata-project/admin-bundle 3.84'
                .' and will throw a LogicException in 4.0',
                __METHOD__,
            ), \E_USER_DEPRECATED);
//            throw new \LogicException(sprintf(
//                'Admin "%s" has no label translator strategy.',
//                static::class
//            ));
        }

        return $this->labelTranslatorStrategy;
    }
}
