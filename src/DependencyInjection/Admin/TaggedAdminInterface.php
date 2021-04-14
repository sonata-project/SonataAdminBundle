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
 * This interface should be implemented to work with the AddDependencyCallsCompilerPass.
 * All the setter are called by this compiler pass.
 *
 * Note that the constructor should also have the following signature
 * ```
 * public function __construct(string $code, string $class, string $controller, ...);
 * ```
 * so that the admin class works correctly with the AddDependencyCallsCompilerPass. Indeed:
 *     - The first and third argument are automatically injected by the AddDependencyCallsCompilerPass.
 *     - The second one is used as a reference of the Admin in the Pool, with the `setAdminClasses` call.
 *
 * @method void                             initialize()
 * @method void                             setLabel(?string $label)
 * @method void                             showMosaicButton(bool $isShown)
 * @method void                             setPagerType(string $pagerType)
 * @method string                           getPagerType()
 * @method void                             setManagerType(string $managerType)
 * @method void                             setSecurityInformation(array $information)
 * @method void                             setFilterPersister(?FilterPersisterInterface $filterPersister = null)
 * @method FilterPersisterInterface|null    getFilterPersister()
 * @method bool                             hasFilterPersister()
 * @method void                             setModelManager(ModelManagerInterface $modelManager)
 * @method void                             setDataSource(DataSourceInterface $dataSource)
 * @method DataSourceInterface              getDataSource()
 * @method void                             setFieldDescriptionFactory(FieldDescriptionFactoryInterface $fieldDescriptionFactory)
 * @method FieldDescriptionFactoryInterface getFieldDescriptionFactory()
 * @method FormContractorInterface          getFormContractor()
 * @method void                             setShowBuilder(ShowBuilderInterface $showBuilder)
 * @method ShowBuilderInterface             getShowBuilder()
 * @method Pool                             getConfigurationPool()
 * @method void                             setRouteGenerator(RouteGeneratorInterface $routeGenerator)
 * @method RouteGeneratorInterface          getRouteGenerator()
 *
 * @phpstan-template T of object
 */
interface TaggedAdminInterface
{
    public const ADMIN_TAG = 'sonata.admin';

    /**
     * NEXT_MAJOR: Uncomment this method.
     *
     * Define custom variable.
     */
//    public function initialize(): void;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function setLabel(?string $label): void;

    /**
     * @return string|null
     */
    public function getLabel();

    /**
     * NEXT_MAJOR: Uncomment this method.
     *
     * Enable/Disable mosaic button for the admin screen.
     */
//    public function showMosaicButton(bool $isShown): void;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getListModes();

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function setPagerType(string $pagerType): void;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function getPagerType(): string;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function setManagerType(string $managerType): void;

    /**
     * @return string
     */
    public function getManagerType();

    /**
     * NEXT_MAJOR: Uncomment this method.
     *
     * Set the roles and permissions per role.
     *
     * @param array<string, string[]> $information
     */
//    public function setSecurityInformation(array $information): void;

    /**
     * Return the roles and permissions per role
     * - different permissions per role for the acl handler
     * - one permission that has the same name as the role for the role handler
     * This should be used by experimented users.
     *
     * @return array<string, string[]> 'role' => ['permission', 'permission']
     */
    public function getSecurityInformation();

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function setFilterPersister(?FilterPersisterInterface $filterPersister = null): void;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function getFilterPersister(): ?FilterPersisterInterface;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function hasFilterPersister(): bool;

    /**
     * NEXT_MAJOR: Uncomment this method.
     *
     * @phpstan-param ModelManagerInterface<T>
     */
//    public function setModelManager(ModelManagerInterface $modelManager): void;

    /**
     * @return ModelManagerInterface
     * @phpstan-return ModelManagerInterface<T>
     */
    public function getModelManager();

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function setDataSource(DataSourceInterface $dataSource): void;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function getDataSource(): DataSourceInterface;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function setFieldDescriptionFactory(FieldDescriptionFactoryInterface $fieldDescriptionFactory): void;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function getFieldDescriptionFactory(): FieldDescriptionFactoryInterface;

    /**
     * @return void
     */
    public function setFormContractor(FormContractorInterface $formContractor);

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function getFormContractor(): FormContractorInterface;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function setShowBuilder(ShowBuilderInterface $showBuilder): void;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function getShowBuilder(): ShowBuilderInterface;

    /**
     * @return void
     */
    public function setListBuilder(ListBuilderInterface $listBuilder);

    /**
     * @return ListBuilderInterface
     */
    public function getListBuilder();

    /**
     * @return void
     */
    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder);

    /**
     * @return DatagridBuilderInterface
     */
    public function getDatagridBuilder();

    /**
     * @return void
     */
    public function setTranslator(TranslatorInterface $translator);

    /**
     * @return TranslatorInterface
     */
    public function getTranslator();

    /**
     * @return void
     */
    public function setConfigurationPool(Pool $pool);

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function getConfigurationPool(): Pool;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator): void;

    /**
     * NEXT_MAJOR: Uncomment this method.
     */
//    public function getRouteGenerator(): RouteGeneratorInterface;

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @param ValidatorInterface $validator
     *
     * @return void
     *
     * @deprecated since sonata-project/admin-bundle 3.83 and will be removed in 4.0
     */
    public function setValidator($validator);

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @return ValidatorInterface
     *
     * @deprecated since sonata-project/admin-bundle 3.83 and will be removed in 4.0
     */
    public function getValidator();

    /**
     * @return void
     */
    public function setSecurityHandler(SecurityHandlerInterface $securityHandler);

    /**
     * @return SecurityHandlerInterface
     */
    public function getSecurityHandler();

    /**
     * @return void
     */
    public function setMenuFactory(FactoryInterface $menuFactory);

    /**
     * @return FactoryInterface
     */
    public function getMenuFactory();

    /**
     * @return void
     */
    public function setRouteBuilder(RouteBuilderInterface $routeBuilder);

    /**
     * @return RouteBuilderInterface
     */
    public function getRouteBuilder();

    /**
     * @return void
     */
    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy);

    /**
     * @return LabelTranslatorStrategyInterface
     */
    public function getLabelTranslatorStrategy();
}
