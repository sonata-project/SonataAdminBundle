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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @phpstan-template T of object
 */
abstract class AbstractTaggedAdmin implements TaggedAdminInterface
{
    public const MOSAIC_ICON_CLASS = 'fa fa-th-large fa-fw';

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
    protected $listModes = [
        'list' => ['class' => 'fa fa-list fa-fw'],
        'mosaic' => ['class' => self::MOSAIC_ICON_CLASS],
    ];

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

    public function __construct(string $code, string $class, string $baseControllerName)
    {
        $this->code = $code;
        $this->class = $class;
        $this->baseControllerName = $baseControllerName;
    }

    abstract public function initialize(): void;

    final public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    final public function getLabel(): ?string
    {
        return $this->label;
    }

    final public function showMosaicButton(bool $isShown): void
    {
        if ($isShown) {
            $this->listModes['mosaic'] = ['class' => static::MOSAIC_ICON_CLASS];
        } else {
            unset($this->listModes['mosaic']);
        }
    }

    final public function getListModes(): array
    {
        return $this->listModes;
    }

    final public function setPagerType(string $pagerType): void
    {
        $this->pagerType = $pagerType;
    }

    final public function getPagerType(): string
    {
        return $this->pagerType;
    }

    final public function setManagerType($type): void
    {
        $this->managerType = $type;
    }

    final public function getManagerType(): string
    {
        if (null === $this->managerType) {
            throw new \LogicException(sprintf('Admin "%s" has no manager type.', static::class));
        }

        return $this->managerType;
    }

    /**
     * @param array<string, string[]> $information
     */
    final public function setSecurityInformation(array $information): void
    {
        $this->securityInformation = $information;
    }

    /**
     * @return array<string, string[]>
     */
    final public function getSecurityInformation(): array
    {
        return $this->securityInformation;
    }

    final public function setFilterPersister(?FilterPersisterInterface $filterPersister = null): void
    {
        $this->filterPersister = $filterPersister;
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

    final public function setModelManager(ModelManagerInterface $modelManager): void
    {
        $this->modelManager = $modelManager;
    }

    final public function getModelManager(): ModelManagerInterface
    {
        if (null === $this->modelManager) {
            throw new \LogicException(sprintf('Admin "%s" has no model manager.', static::class));
        }

        return $this->modelManager;
    }

    final public function setDataSource(DataSourceInterface $dataSource): void
    {
        $this->dataSource = $dataSource;
    }

    final public function getDataSource(): DataSourceInterface
    {
        if (null === $this->dataSource) {
            throw new \LogicException(sprintf('Admin "%s" has no data source.', static::class));
        }

        return $this->dataSource;
    }

    final public function setFieldDescriptionFactory(FieldDescriptionFactoryInterface $fieldDescriptionFactory): void
    {
        $this->fieldDescriptionFactory = $fieldDescriptionFactory;
    }

    public function getFieldDescriptionFactory(): FieldDescriptionFactoryInterface
    {
        if (null === $this->fieldDescriptionFactory) {
            throw new \LogicException(sprintf(
                'Admin "%s" has no field description factory.',
                static::class
            ));
        }

        return $this->fieldDescriptionFactory;
    }

    final public function setFormContractor(FormContractorInterface $formBuilder): void
    {
        $this->formContractor = $formBuilder;
    }

    final public function getFormContractor(): FormContractorInterface
    {
        if (null === $this->formContractor) {
            throw new \LogicException(sprintf('Admin "%s" has no form contractor.', static::class));
        }

        return $this->formContractor;
    }

    final public function setShowBuilder(ShowBuilderInterface $showBuilder): void
    {
        $this->showBuilder = $showBuilder;
    }

    final public function getShowBuilder(): ShowBuilderInterface
    {
        if (null === $this->showBuilder) {
            throw new \LogicException(sprintf('Admin "%s" has no show builder.', static::class));
        }

        return $this->showBuilder;
    }

    final public function setListBuilder(ListBuilderInterface $listBuilder): void
    {
        $this->listBuilder = $listBuilder;
    }

    final public function getListBuilder(): ListBuilderInterface
    {
        if (null === $this->listBuilder) {
            throw new \LogicException(sprintf('Admin "%s" has no list builder.', static::class));
        }

        return $this->listBuilder;
    }

    final public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder): void
    {
        $this->datagridBuilder = $datagridBuilder;
    }

    final public function getDatagridBuilder(): DatagridBuilderInterface
    {
        if (null === $this->datagridBuilder) {
            throw new \LogicException(sprintf('Admin "%s" has no datagrid builder.', static::class));
        }

        return $this->datagridBuilder;
    }

    final public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    final public function getTranslator(): TranslatorInterface
    {
        if (null === $this->translator) {
            throw new \LogicException(sprintf('Admin "%s" has no translator.', static::class));
        }

        return $this->translator;
    }

    final public function setConfigurationPool(Pool $configurationPool): void
    {
        $this->configurationPool = $configurationPool;
    }

    final public function getConfigurationPool(): Pool
    {
        if (null === $this->configurationPool) {
            throw new \LogicException(sprintf('Admin "%s" has no pool.', static::class));
        }

        return $this->configurationPool;
    }

    final public function setRouteGenerator(RouteGeneratorInterface $routeGenerator): void
    {
        $this->routeGenerator = $routeGenerator;
    }

    final public function getRouteGenerator(): RouteGeneratorInterface
    {
        if (null === $this->routeGenerator) {
            throw new \LogicException(sprintf('Admin "%s" has no route generator.', static::class));
        }

        return $this->routeGenerator;
    }

    final public function setSecurityHandler(SecurityHandlerInterface $securityHandler): void
    {
        $this->securityHandler = $securityHandler;
    }

    final public function getSecurityHandler(): SecurityHandlerInterface
    {
        if (null === $this->securityHandler) {
            throw new \LogicException(sprintf('Admin "%s" has no security handler.', static::class));
        }

        return $this->securityHandler;
    }

    final public function setMenuFactory(FactoryInterface $menuFactory): void
    {
        $this->menuFactory = $menuFactory;
    }

    final public function getMenuFactory(): FactoryInterface
    {
        if (null === $this->menuFactory) {
            throw new \LogicException(sprintf('Admin "%s" has no security handler.', static::class));
        }

        return $this->menuFactory;
    }

    final public function setRouteBuilder(RouteBuilderInterface $routeBuilder): void
    {
        $this->routeBuilder = $routeBuilder;
    }

    final public function getRouteBuilder(): RouteBuilderInterface
    {
        if (null === $this->routeBuilder) {
            throw new \LogicException(sprintf('Admin "%s" has no route builder.', static::class));
        }

        return $this->routeBuilder;
    }

    final public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy): void
    {
        $this->labelTranslatorStrategy = $labelTranslatorStrategy;
    }

    final public function getLabelTranslatorStrategy(): LabelTranslatorStrategyInterface
    {
        if (null === $this->labelTranslatorStrategy) {
            throw new \LogicException(sprintf('Admin "%s" has no label translator strategy.', static::class));
        }

        return $this->labelTranslatorStrategy;
    }
}
