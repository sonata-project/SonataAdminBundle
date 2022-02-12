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
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exporter\DataSourceInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryAwareInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This interface should be implemented to work with the AddDependencyCallsCompilerPass.
 * All the setter are called by this compiler pass.
 *
 * @method void   setCode(string $code)
 * @method string getCode()
 * @method void   setModelClass(string $modelClass)
 * @method string getModelClass()
 * @method void   setBaseControllerName(string $baseControllerName)
 * @method string getBaseControllerName()
 *
 * @phpstan-template T of object
 */
interface TaggedAdminInterface extends MutableTemplateRegistryAwareInterface
{
    public const ADMIN_TAG = 'sonata.admin';

    public const DEFAULT_LIST_MODES = [
        'list' => [
            'icon' => '<i class="fas fa-list fa-fw" aria-hidden="true"></i>',
            // NEXT_MAJOR: Remove the class part.
            'class' => 'fas fa-list fa-fw',
        ],
        'mosaic' => [
            'icon' => '<i class="fas fa-th-large fa-fw" aria-hidden="true"></i>',
            // NEXT_MAJOR: Remove the class part.
            'class' => 'fas fa-th-large fa-fw',
        ],
    ];

    /**
     * Define custom variable.
     */
    public function initialize(): void;

    public function setLabel(?string $label): void;

    public function getLabel(): ?string;

    /**
     * @param non-empty-array<string, array<string, mixed>> $listModes
     */
    public function setListModes(array $listModes): void;

    /**
     * @return non-empty-array<string, array<string, mixed>>
     */
    public function getListModes(): array;

    public function setPagerType(string $pagerType): void;

    public function getPagerType(): string;

    public function setManagerType(string $managerType): void;

    public function getManagerType(): string;

    /**
     * Set the roles and permissions per role.
     *
     * @param array<string, string[]> $information
     */
    public function setSecurityInformation(array $information): void;

    /**
     * Return the roles and permissions per role
     * - different permissions per role for the acl handler
     * - one permission that has the same name as the role for the role handler
     * This should be used by experimented users.
     *
     * @return array<string, string[]> 'role' => ['permission', 'permission']
     */
    public function getSecurityInformation(): array;

    public function setFilterPersister(?FilterPersisterInterface $filterPersister = null): void;

    public function getFilterPersister(): FilterPersisterInterface;

    public function hasFilterPersister(): bool;

    /**
     * @phpstan-param ModelManagerInterface<T> $modelManager
     */
    public function setModelManager(ModelManagerInterface $modelManager): void;

    /**
     * @phpstan-return ModelManagerInterface<T>
     */
    public function getModelManager(): ModelManagerInterface;

    public function setDataSource(DataSourceInterface $dataSource): void;

    public function getDataSource(): DataSourceInterface;

    public function setFieldDescriptionFactory(FieldDescriptionFactoryInterface $fieldDescriptionFactory): void;

    public function getFieldDescriptionFactory(): FieldDescriptionFactoryInterface;

    public function setFormContractor(FormContractorInterface $formContractor): void;

    public function getFormContractor(): FormContractorInterface;

    public function setShowBuilder(ShowBuilderInterface $showBuilder): void;

    public function getShowBuilder(): ShowBuilderInterface;

    public function setListBuilder(ListBuilderInterface $listBuilder): void;

    public function getListBuilder(): ListBuilderInterface;

    /**
     * @param DatagridBuilderInterface<ProxyQueryInterface> $datagridBuilder
     */
    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder): void;

    /**
     * @return DatagridBuilderInterface<ProxyQueryInterface>
     */
    public function getDatagridBuilder(): DatagridBuilderInterface;

    public function setTranslator(TranslatorInterface $translator): void;

    public function getTranslator(): TranslatorInterface;

    public function setConfigurationPool(Pool $configurationPool): void;

    public function getConfigurationPool(): Pool;

    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator): void;

    public function getRouteGenerator(): RouteGeneratorInterface;

    public function setSecurityHandler(SecurityHandlerInterface $securityHandler): void;

    public function getSecurityHandler(): SecurityHandlerInterface;

    public function setMenuFactory(FactoryInterface $menuFactory): void;

    public function getMenuFactory(): FactoryInterface;

    public function setRouteBuilder(RouteBuilderInterface $routeBuilder): void;

    public function getRouteBuilder(): RouteBuilderInterface;

    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy): void;

    public function getLabelTranslatorStrategy(): LabelTranslatorStrategyInterface;
}
