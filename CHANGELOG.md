# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.85.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.84.0...3.85.0) - 2021-01-02
### Added
- [[#6576](https://github.com/sonata-project/SonataAdminBundle/pull/6576)] Added generics to `AdminExtensionInterface`. ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#6727](https://github.com/sonata-project/SonataAdminBundle/pull/6727)] Variable "admin" does not exist in `CRUD/Association/edit_many_script.html.twig` ([@ggabrovski](https://github.com/ggabrovski))

## [3.84.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.83.0...3.84.0) - 2021-01-02
### Added
- [[#6724](https://github.com/sonata-project/SonataAdminBundle/pull/6724)] Added support for fieldName with dot for `BaseFieldDescription::getFieldValue()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6713](https://github.com/sonata-project/SonataAdminBundle/pull/6713)] Added `AbstractTaggedAdmin::getListModes()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6713](https://github.com/sonata-project/SonataAdminBundle/pull/6713)] Added `TaggedAdminInterface::getListModes()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6699](https://github.com/sonata-project/SonataAdminBundle/pull/6699)] Added `RouteCollectionInterface` imported from `4.x` to ease upgrading process ([@franmomu](https://github.com/franmomu))
- [[#6720](https://github.com/sonata-project/SonataAdminBundle/pull/6720)] One argument to `BaseFieldDescription::construct()` to set the field name ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `getPage()` to the PagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `getFirstPage()` to the PagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `getLastPage()` to the PagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `getNextPage()` to the PagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `getPreviousPage()` to the PagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `isFirstPage()` to the PagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `isLastPage()` to the PagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `getNbResults()` to the PagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `getLinks()` to the PagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `haveToPaginate()` to the PagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `getQuery()` to the PagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Added `TaggedAdminInterface` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Added `AbstractTaggedAdmin` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Added `AbstractAdmin::hasFilterPersister()` method ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6654](https://github.com/sonata-project/SonataAdminBundle/pull/6654)] Added `Sonata\AdminBundle\Filter\Filter::active` property ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#6716](https://github.com/sonata-project/SonataAdminBundle/pull/6716)] `FieldDescriptionInterface::getFieldValue()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6720](https://github.com/sonata-project/SonataAdminBundle/pull/6720)] `FieldDescriptionInterface::setFieldMapping()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6720](https://github.com/sonata-project/SonataAdminBundle/pull/6720)] `BaseFieldDescription::setFieldMapping()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::getCurrentMaxLink()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::getMaxRecordLimit()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::setMaxRecordLimit()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::getCursor()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::setCursor()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::getObjectByCursor()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::getCurrent()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::getNext()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::getPrevious()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::getFirstIndex()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::getLastIndex()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::getParameters()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::getParameter()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::hasParameter()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6541](https://github.com/sonata-project/SonataAdminBundle/pull/6541)] `AbstractPager::setParameter()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate instantiating a new `AbstractAdmin` class with `null` as third argument ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getManagerType()` when no manager type is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getFilterPersister()` when no filter persister is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getModelManager()` when no model manager is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getDataSource()` when no data source is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getFormContractor()` when no form contractor is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getShowBuilder()` when no show builder is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getListBuilder()` when no list builder is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getDatagridBuilder()` when no datagrid builder is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getTranslator()` when no translator is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getConfigurationPool()` when no pool is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate `AbstractAdmin::setValidator()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate `AbstractAdmin::getValidator()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getSecurityHandler()` when no security handler is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getMenuFactory()` when no menu factory is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getRouteBuilder()` when no route builder is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6614](https://github.com/sonata-project/SonataAdminBundle/pull/6614)] Deprecate calling `AbstractAdmin::getLabelTranslatorStrategy()` when no label translator strategy is set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6694](https://github.com/sonata-project/SonataAdminBundle/pull/6694)] Deprecated passing other type than `string` or `null` as argument 2 constructing `FormBuilderIterator` ([@franmomu](https://github.com/franmomu))
- [[#6654](https://github.com/sonata-project/SonataAdminBundle/pull/6654)] `Sonata\AdminBundle\Filter\Filter::getValue()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6654](https://github.com/sonata-project/SonataAdminBundle/pull/6654)] `Sonata\AdminBundle\Filter\Filter::setValue()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#6702](https://github.com/sonata-project/SonataAdminBundle/pull/6702)] Stop throwing an exception when the `_per_page`  filter parameter is not set ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6722](https://github.com/sonata-project/SonataAdminBundle/pull/6722)] Replaced 'array[0]' by 'array|first' to fix edit_many_to_many template when children index does not start with 0 ([@nws-jstorm](https://github.com/nws-jstorm))
- [[#6677](https://github.com/sonata-project/SonataAdminBundle/pull/6677)] Fixed using `admin_pool` to fetch an option instead of `sonata_config` ([@franmomu](https://github.com/franmomu))
- [[#6694](https://github.com/sonata-project/SonataAdminBundle/pull/6694)] Fixed iterating over children names in `AdminHelper::getChildFormBuilder` recursively ([@franmomu](https://github.com/franmomu))
- [[#6675](https://github.com/sonata-project/SonataAdminBundle/pull/6675)] Fixed disallowing sorting in a field defined with a closure in `associated_property` ([@franmomu](https://github.com/franmomu))

## [3.83.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.82.0...3.83.0) - 2020-12-08
### Added
- [[#6651](https://github.com/sonata-project/SonataAdminBundle/pull/6651)] Add support for global admin extensions via config ([@core23](https://github.com/core23))
- [[#6640](https://github.com/sonata-project/SonataAdminBundle/pull/6640)] Added `SonataConfiguration` class to handle sonata global configuration. ([@franmomu](https://github.com/franmomu))
- [[#6640](https://github.com/sonata-project/SonataAdminBundle/pull/6640)] Added global Twig `sonata_config` to access global configuration from Twig. ([@franmomu](https://github.com/franmomu))
- [[#6648](https://github.com/sonata-project/SonataAdminBundle/pull/6648)] Added 3 new arguments to `BaseFieldDescription::__construct()` to do create a FieldDescription with the mapping. ([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#6659](https://github.com/sonata-project/SonataAdminBundle/pull/6659)] Deprecated `Pool::getGroups()` method. ([@franmomu](https://github.com/franmomu))
- [[#6659](https://github.com/sonata-project/SonataAdminBundle/pull/6659)] Deprecated `Pool::hasGroup()` method. ([@franmomu](https://github.com/franmomu))
- [[#6659](https://github.com/sonata-project/SonataAdminBundle/pull/6659)] Deprecated `Pool::getAdminsByGroup()` method. ([@franmomu](https://github.com/franmomu))
- [[#6633](https://github.com/sonata-project/SonataAdminBundle/pull/6633)] `AbstractAdmin::$validator` property ([@tambait](https://github.com/tambait))
- [[#6633](https://github.com/sonata-project/SonataAdminBundle/pull/6633)] `AdminInterface::getValidator` method specification ([@tambait](https://github.com/tambait))
- [[#6633](https://github.com/sonata-project/SonataAdminBundle/pull/6633)] `AdminInterface::setValidator` method specification ([@tambait](https://github.com/tambait))
- [[#6633](https://github.com/sonata-project/SonataAdminBundle/pull/6633)] `AbstractAdmin::getValidator` method implementation ([@tambait](https://github.com/tambait))
- [[#6633](https://github.com/sonata-project/SonataAdminBundle/pull/6633)] `AbstractAdmin::setValidator` method implementation ([@tambait](https://github.com/tambait))
- [[#6633](https://github.com/sonata-project/SonataAdminBundle/pull/6633)] `AddDependencyCallsCompilerPass` - validator related keys ([@tambait](https://github.com/tambait))
- [[#6633](https://github.com/sonata-project/SonataAdminBundle/pull/6633)] `DependencyInjection/Configuration` - validator related config option ([@tambait](https://github.com/tambait))
- [[#6640](https://github.com/sonata-project/SonataAdminBundle/pull/6640)] Deprecated `Pool::getTitle()` method. ([@franmomu](https://github.com/franmomu))
- [[#6640](https://github.com/sonata-project/SonataAdminBundle/pull/6640)] Deprecated `Pool::getTitleLogo()` method. ([@franmomu](https://github.com/franmomu))
- [[#6640](https://github.com/sonata-project/SonataAdminBundle/pull/6640)] Deprecated `Pool::getOption()` method. ([@franmomu](https://github.com/franmomu))
- [[#6640](https://github.com/sonata-project/SonataAdminBundle/pull/6640)] Deprecated global Twig `sonata_admin` variable. ([@franmomu](https://github.com/franmomu))
- [[#6648](https://github.com/sonata-project/SonataAdminBundle/pull/6648)] Deprecated `FieldDescriptionInterface::setFieldMapping()` method. ([@franmomu](https://github.com/franmomu))
- [[#6648](https://github.com/sonata-project/SonataAdminBundle/pull/6648)] Deprecated `FieldDescriptionInterface::setAssociationMapping()` method. ([@franmomu](https://github.com/franmomu))
- [[#6648](https://github.com/sonata-project/SonataAdminBundle/pull/6648)] Deprecated `FieldDescriptionInterface::setParentAssociationMappings()` method. ([@franmomu](https://github.com/franmomu))
- [[#6648](https://github.com/sonata-project/SonataAdminBundle/pull/6648)] Deprecated `FieldDescriptionInterface::setMappingType()` method. ([@franmomu](https://github.com/franmomu))
- [[#6648](https://github.com/sonata-project/SonataAdminBundle/pull/6648)] Deprecated `BaseFieldDescription::setMappingType()` method. ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#6665](https://github.com/sonata-project/SonataAdminBundle/pull/6665)] Fixed triggering a deprecation in a check. ([@franmomu](https://github.com/franmomu))
- [[#6662](https://github.com/sonata-project/SonataAdminBundle/pull/6662)] Incorrect object being passed to the view when rendering the history compare action ([@tamcy](https://github.com/tamcy))

## [3.82.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.81.1...3.82.0) - 2020-12-05
### Added
- [[#6643](https://github.com/sonata-project/SonataAdminBundle/pull/6643)] Added generics to `CRUDController` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6631](https://github.com/sonata-project/SonataAdminBundle/pull/6631)] Option to globally override the data source of all the admin ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6547](https://github.com/sonata-project/SonataAdminBundle/pull/6547)] Added `row_attr` to the form_row container. ([@nieuwenhuisen](https://github.com/nieuwenhuisen))
- [[#6609](https://github.com/sonata-project/SonataAdminBundle/pull/6609)] `AdminSearchCompilerPass` in order to configure which admins must support search. ([@phansys](https://github.com/phansys))
- [[#6609](https://github.com/sonata-project/SonataAdminBundle/pull/6609)] Support for `global_search` attribute in `sonata.admin` tags, which admits boolean values. ([@phansys](https://github.com/phansys))

### Changed
- [[#6559](https://github.com/sonata-project/SonataAdminBundle/pull/6559)] Removed alias from `ModelManagerInterface::createQuery` method ([@neok](https://github.com/neok))
- [[#6214](https://github.com/sonata-project/SonataAdminBundle/pull/6214)] `CRUDController::handleXmlHttpRequestSuccessResponse` method is now protected ([@nieuwenhuisen](https://github.com/nieuwenhuisen))
- [[#6214](https://github.com/sonata-project/SonataAdminBundle/pull/6214)] `CRUDController::handleXmlHttpRequestErrorResponse` method is now protected ([@nieuwenhuisen](https://github.com/nieuwenhuisen))
- [[#6630](https://github.com/sonata-project/SonataAdminBundle/pull/6630)] Replaced jQuery "load()" with "on()" in Admin.js ([@timwentzell](https://github.com/timwentzell))
- [[#6632](https://github.com/sonata-project/SonataAdminBundle/pull/6632)] Twig templates: "list_percent", "show_percent", "list_currency" and "show_currency" ([@willemverspyck](https://github.com/willemverspyck))

### Deprecated
- [[#6618](https://github.com/sonata-project/SonataAdminBundle/pull/6618)] `AdminInterface::validate` method specification ([@tambait](https://github.com/tambait))
- [[#6618](https://github.com/sonata-project/SonataAdminBundle/pull/6618)] `AbstractAdmin:validate` method implementation ([@tambait](https://github.com/tambait))
- [[#6618](https://github.com/sonata-project/SonataAdminBundle/pull/6618)] `AbstractAdmin::attachInlineValidator()` method ([@tambait](https://github.com/tambait))
- [[#6618](https://github.com/sonata-project/SonataAdminBundle/pull/6618)] `AdminExtensionInterface::validate()` method specification ([@tambait](https://github.com/tambait))
- [[#6618](https://github.com/sonata-project/SonataAdminBundle/pull/6618)] `AbstractAdminExtension::validate()` method implementation ([@tambait](https://github.com/tambait))
- [[#6622](https://github.com/sonata-project/SonataAdminBundle/pull/6622)] Referencing to DashboardAction and SearchAction by FQCN class instead of id. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6634](https://github.com/sonata-project/SonataAdminBundle/pull/6634)] Deprecated `Pool::getPropertyAccessor()` method. ([@franmomu](https://github.com/franmomu))
- [[#6634](https://github.com/sonata-project/SonataAdminBundle/pull/6634)] Deprecated not passing and instance of `PropertyAccessor` as argument 1 to `Sonata\AdminBundle\Admin\AdminHelper` constructor. ([@franmomu](https://github.com/franmomu))
- [[#6634](https://github.com/sonata-project/SonataAdminBundle/pull/6634)] Deprecated not passing and instance of `PropertyAccessor` as argument 5 to `Sonata\AdminBundle\Action\SetObjectFieldValueAction` constructor. ([@franmomu](https://github.com/franmomu))
- [[#6634](https://github.com/sonata-project/SonataAdminBundle/pull/6634)] Deprecated not passing and instance of `PropertyAccessor` as argument 5 to `Sonata\AdminBundle\Twig\Extension\SonataAdminExtension` constructor. ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#6529](https://github.com/sonata-project/SonataAdminBundle/pull/6529)] Explicitly cast types in `CRUDController::batchAction()` ([@peter-gribanov](https://github.com/peter-gribanov))

## [3.81.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.81.0...3.81.1) - 2020-11-21
### Fixed
- [[#6597](https://github.com/sonata-project/SonataAdminBundle/pull/6597)] `AdminInterface` extends `MutableTemplateRegistryAwareInterface` instead of `TemplateRegistryAwareInterface` ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.81.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.80.0...3.81.0) - 2020-11-15
### Deprecated
- [[#6593](https://github.com/sonata-project/SonataAdminBundle/pull/6593)] Not passing 'show', 'hide' or 'fade' as argument 4 to `Sonata\AdminBundle\Block\AdminSearchBlockService()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#6591](https://github.com/sonata-project/SonataAdminBundle/pull/6591)] Apply filter even if the operator is not provided ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.80.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.79.0...3.80.0) - 2020-11-13
### Added
- [[#6585](https://github.com/sonata-project/SonataAdminBundle/pull/6585)] `NOT_EQUAL` operator for `StringOperatorType` ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#6402](https://github.com/sonata-project/SonataAdminBundle/pull/6402)] Delegate filter query by empty value to filters ([@peter-gribanov](https://github.com/peter-gribanov))

### Deprecated
- [[#6568](https://github.com/sonata-project/SonataAdminBundle/pull/6568)] Deprecated `ModelManagerInterface::modelTransform()` method ([@franmomu](https://github.com/franmomu))

## [3.79.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.78.1...3.79.0) - 2020-11-09
### Added
- [[#6565](https://github.com/sonata-project/SonataAdminBundle/pull/6565)] Added `collection_by_reference` option for AdminType ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6556](https://github.com/sonata-project/SonataAdminBundle/pull/6556)] Added `Sonata\AdminBundle\Templating\AbstractTemplateRegistry` ([@wbloszyk](https://github.com/wbloszyk))
- [[#6556](https://github.com/sonata-project/SonataAdminBundle/pull/6556)] Added `Sonata\AdminBundle\Templating\MutableTemplateRegistry` ([@wbloszyk](https://github.com/wbloszyk))
- [[#6556](https://github.com/sonata-project/SonataAdminBundle/pull/6556)] Added `Sonata\AdminBundle\Templating\MutableTemplateRegistryAwareInterface` ([@wbloszyk](https://github.com/wbloszyk))
- [[#6463](https://github.com/sonata-project/SonataAdminBundle/pull/6463)] Added DataSourceInterface ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#6565](https://github.com/sonata-project/SonataAdminBundle/pull/6565)] `collection_by_reference` is used instead of `by_reference` in `AdminType in order to know which call should be used between `ObjectManipulator::addInstance()` and `ObjectManipulator::setObject()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6556](https://github.com/sonata-project/SonataAdminBundle/pull/6556)] Changed `Sonata\AdminBundle\Templating\TemplateRegistryAwareInterface` to handle instances of `TemplateRegistryInterface`. ([@wbloszyk](https://github.com/wbloszyk))
- [[#6548](https://github.com/sonata-project/SonataAdminBundle/pull/6548)] Prevent load entities when pass empty choices for `ModelType`, use null for native loading ([@kirya-dev](https://github.com/kirya-dev))

### Deprecated
- [[#6556](https://github.com/sonata-project/SonataAdminBundle/pull/6556)] Deprecated `Sonata\AdminBundle\Templating\TemplateRegistry:setTemplate()` ([@wbloszyk](https://github.com/wbloszyk))
- [[#6556](https://github.com/sonata-project/SonataAdminBundle/pull/6556)] Deprecated `Sonata\AdminBundle\Templating\TemplateRegistry:setTemplates()` ([@wbloszyk](https://github.com/wbloszyk))
- [[#6458](https://github.com/sonata-project/SonataAdminBundle/pull/6458)] `DatagridManagerInterface` with no replacement ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6463](https://github.com/sonata-project/SonataAdminBundle/pull/6463)] Deprecated not setting a DataSourceInterface to an Admin ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6463](https://github.com/sonata-project/SonataAdminBundle/pull/6463)] Deprecated `ModelManagerInterface::getDataSourceIterator()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#6571](https://github.com/sonata-project/SonataAdminBundle/pull/6571)] If no label is provided to the filter, the default label is used following the label translator strategy ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6574](https://github.com/sonata-project/SonataAdminBundle/pull/6574)] Showing toggle filter button when the filter is hidden in the admin list ([@phansys](https://github.com/phansys))
- [[#6534](https://github.com/sonata-project/SonataAdminBundle/pull/6534)] Allow usage object properties to get/set instance in `ObjectManipulator` ([@peter-gribanov](https://github.com/peter-gribanov))

## [3.78.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.78.0...3.78.1) - 2020-10-28
### Fixed
- [[#6530](https://github.com/sonata-project/SonataAdminBundle/pull/6530)] Added the ability to scroll the filter list dropdown ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.78.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.77.0...3.78.0) - 2020-10-23
### Added
- [[#6486](https://github.com/sonata-project/SonataAdminBundle/pull/6486)] Added `Pool::hasSingleAdminByClass()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6468](https://github.com/sonata-project/SonataAdminBundle/pull/6468)] Add phpdoc to array returns ([@core23](https://github.com/core23))

### Changed
- [[#6513](https://github.com/sonata-project/SonataAdminBundle/pull/6513)] Html with a links in no_result_content ([@axzx](https://github.com/axzx))
- [[#6500](https://github.com/sonata-project/SonataAdminBundle/pull/6500)] When accessing on a non-existent List page, it displays a link to go to page 1 instead of a button to create a new entity ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6497](https://github.com/sonata-project/SonataAdminBundle/pull/6497)] Setting default values ​​for filters and sorting even without a request ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#6503](https://github.com/sonata-project/SonataAdminBundle/pull/6503)] Instantiate a FieldDescription without passing the name as first argument ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6515](https://github.com/sonata-project/SonataAdminBundle/pull/6515)] Deprecate `FilterInterface::filter()` method ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6510](https://github.com/sonata-project/SonataAdminBundle/pull/6510)] Method `Validators::validateEntityName()` ([@phansys](https://github.com/phansys))
- [[#6320](https://github.com/sonata-project/SonataAdminBundle/pull/6320)] Deprecated `Sonata\AdminBundle\Controller\CRUDController::getRestMethod()` method in favor of `Symfony\Component\HttpFoundation\Request::getMethod()` ([@phansys](https://github.com/phansys))
- [[#6480](https://github.com/sonata-project/SonataAdminBundle/pull/6480)] Deprecated not configuring `acl_user_manager` service explicitly when using `friendsofsymfony/user-bundle` ([@franmomu](https://github.com/franmomu))
- [[#6480](https://github.com/sonata-project/SonataAdminBundle/pull/6480)] Deprecated configuring `acl_user_manager` service without implementing `AdminAclUserManagerInterface` ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#6509](https://github.com/sonata-project/SonataAdminBundle/pull/6509)] Passing an empty array as argument 3 for implementations of `ModelManagerInterface::addIdentifiersToQuery()` ([@phansys](https://github.com/phansys))
- [[#6507](https://github.com/sonata-project/SonataAdminBundle/pull/6507)] Pool support of group without admin ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6320](https://github.com/sonata-project/SonataAdminBundle/pull/6320)] Fixed return value from `CRUDController::getRestMethod()` respecting `Request::getHttpMethodParameterOverride()` ([@phansys](https://github.com/phansys))
- [[#6498](https://github.com/sonata-project/SonataAdminBundle/pull/6498)] Call \Knp\Menu\MenuItem::getLabel() method directly in twig template to avoid a possible side effect from \ArrayAccess ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6525](https://github.com/sonata-project/SonataAdminBundle/pull/6525)] Missing ellipsis in some truncated word ([@phansys](https://github.com/phansys))
- [[#6523](https://github.com/sonata-project/SonataAdminBundle/pull/6523)] fixed BC break with getting the translation domain for nested fields on a one-to-many inline edit table view ([@dmaicher](https://github.com/dmaicher))
- [[#6438](https://github.com/sonata-project/SonataAdminBundle/pull/6438)] AdminType with CollectionType passed by reference ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.77.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.76.0...3.77.0) - 2020-10-16
### Added
- [[#6419](https://github.com/sonata-project/SonataAdminBundle/pull/6419)] Add
  phpdoc for access mappings ([@core23](https://github.com/core23))

### Changed
- [[#6315](https://github.com/sonata-project/SonataAdminBundle/pull/6315)]
  Changed multiple calls to `ModelManagerInterface::find()` with
`ModelManagerInterface::findBy()` in order to avoid multiple transactions.
([@phansys](https://github.com/phansys))
- [[#6435](https://github.com/sonata-project/SonataAdminBundle/pull/6435)]
  Default mosaic background is now a file instead of a data URI.
([@jorrit](https://github.com/jorrit))
- [[#6451](https://github.com/sonata-project/SonataAdminBundle/pull/6451)]
  Moved confirmExit to window.load ([@fastnloud](https://github.com/fastnloud))

### Deprecated
- [[#6420](https://github.com/sonata-project/SonataAdminBundle/pull/6420)]
  Deprecate Passing a third argument to
`GenerateObjectAclCommand::__construct()`
([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6475](https://github.com/sonata-project/SonataAdminBundle/pull/6475)]
  Deprecated `Pool::getContainer()` method.
([@franmomu](https://github.com/franmomu))
- [[#6350](https://github.com/sonata-project/SonataAdminBundle/pull/6350)]
  Deprecate usage of a null groupItem label
([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6413](https://github.com/sonata-project/SonataAdminBundle/pull/6413)]
  Deprecate using shortcut notation for user_model in favor of FQCN in
`GenerateObjectAclCommand` ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#6466](https://github.com/sonata-project/SonataAdminBundle/pull/6466)]
  Solve problem with unset batch checkbox`s after back via browser history
([@kirya-dev](https://github.com/kirya-dev))
- [[#6401](https://github.com/sonata-project/SonataAdminBundle/pull/6401)]
  Fixed incorrect inline table / tabs collection translation domain
([@EmmanuelVella](https://github.com/EmmanuelVella))
- [[#6350](https://github.com/sonata-project/SonataAdminBundle/pull/6350)] Keep
  support for a null groupItem label
([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6429](https://github.com/sonata-project/SonataAdminBundle/pull/6429)]
  Superfluous deprecation notice in `getCurrentChild()` when calling
`getCurrentChildAdmin()`. ([@jorrit](https://github.com/jorrit))
- [[#6414](https://github.com/sonata-project/SonataAdminBundle/pull/6414)]
  Remove calls to deprecated method
`ModelInterface::getPaginationParameters()`.
([@franmomu](https://github.com/franmomu))
- [[#6414](https://github.com/sonata-project/SonataAdminBundle/pull/6414)]
  Remove calls to deprecated method `ModelInterface::getSortParameters()`.
([@franmomu](https://github.com/franmomu))
- [[#6421](https://github.com/sonata-project/SonataAdminBundle/pull/6421)] Call
  to deprecated `getTargetEntity`
([@jordisala1991](https://github.com/jordisala1991))
- [[#6412](https://github.com/sonata-project/SonataAdminBundle/pull/6412)]
  Deprecation notice in `MergeCollectionListener` when using the `ModelType`
form type with the `model_manager` option set.
([@jorrit](https://github.com/jorrit))

### Removed
- [[#6437](https://github.com/sonata-project/SonataAdminBundle/pull/6437)]
  Removed several large unnecessary resources from
public/bundles/sonatacore/vendor. ([@jorrit](https://github.com/jorrit))

## [3.76.0](sonata-project/SonataAdminBundle/compare/3.75.0...3.76.0) - 2020-09-20
### Added
- [[#6390](https://github.com/sonata-project/SonataAdminBundle/pull/6390)]  `Sonata\AdminBundle\Templating\TemplateRegistryAwareInterface` with `getTemplateRegistry()`, `hasTemplateRegistry()` and `setTemplateRegistry()` methods ([@wbloszyk](https://github.com/wbloszyk))
- [[#6378](https://github.com/sonata-project/SonataAdminBundle/pull/6378)] Added option to load different Admin LTE skins ([@fastnloud](https://github.com/fastnloud))
- [[#6381](https://github.com/sonata-project/SonataAdminBundle/pull/6381)] Added `ModelManagerInterface::supportsQuery()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6387](https://github.com/sonata-project/SonataAdminBundle/pull/6387)] `Sonata\AdminBundle\Admin\AbstractAdmin::hasTemplateRegistry(): bool` ([@wbloszyk](https://github.com/wbloszyk))
- [[#6377](https://github.com/sonata-project/SonataAdminBundle/pull/6377)] Add type hints to data transformers ([@core23](https://github.com/core23))
- [[#6377](https://github.com/sonata-project/SonataAdminBundle/pull/6377)] Add type hints to util classes ([@core23](https://github.com/core23))
- [[#6348](https://github.com/sonata-project/SonataAdminBundle/pull/6348)] Add `AbstractAdmin::configureExportFields` extension point ([@core23](https://github.com/core23))
- [[#6361](https://github.com/sonata-project/SonataAdminBundle/pull/6361)] Added generic type hints to (nearly) all admin interfaces ([@core23](https://github.com/core23))
- [[#6369](https://github.com/sonata-project/SonataAdminBundle/pull/6369)] Added support for `symfony/config` 5.1. ([@franmomu](https://github.com/franmomu))
- [[#6369](https://github.com/sonata-project/SonataAdminBundle/pull/6369)] Added support for `symfony/dependency-injection` 5.1. ([@franmomu](https://github.com/franmomu))
- [[#6369](https://github.com/sonata-project/SonataAdminBundle/pull/6369)] Added support for `symfony/routing` 5.1. ([@franmomu](https://github.com/franmomu))
- [[#6363](https://github.com/sonata-project/SonataAdminBundle/pull/6363)] Allow `symfony/security-csrf` and `symfony/asset` ^5.1 ([@jordisala1991](https://github.com/jordisala1991))
- [[#6344](https://github.com/sonata-project/SonataAdminBundle/pull/6344)] Support for symfony/http-foundation and symfony/validator 5.1 ([@dmaicher](https://github.com/dmaicher))
- [[#6349](https://github.com/sonata-project/SonataAdminBundle/pull/6349)] Add generics to `ModelManagerInterface` ([@core23](https://github.com/core23))

### Changed
- [[#6364](https://github.com/sonata-project/SonataAdminBundle/pull/6364)]  Prevent `addFormFieldDescription` if field role no granted ([@kirya-dev](https://github.com/kirya-dev))

### Deprecated
- [[#6381](https://github.com/sonata-project/SonataAdminBundle/pull/6381)] Create a new instance of ModelChoiceLoader with a query unsupported by the modelManager ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6387](https://github.com/sonata-project/SonataAdminBundle/pull/6387)] `Sonata\AdminBundle\Admin\AbstractAdmin::setTemplates()` ([@wbloszyk](https://github.com/wbloszyk))
- [[#6387](https://github.com/sonata-project/SonataAdminBundle/pull/6387)] `Sonata\AdminBundle\Admin\AbstractAdmin::setTemplate()` ([@wbloszyk](https://github.com/wbloszyk))
- [[#6386](https://github.com/sonata-project/SonataAdminBundle/pull/6386)] Deprecated `ProxyQueryInterface::getUniqueParameterId`. ([@franmomu](https://github.com/franmomu))
- [[#6386](https://github.com/sonata-project/SonataAdminBundle/pull/6386)] Deprecated `ProxyQueryInterface::entityJoin`. ([@franmomu](https://github.com/franmomu))
- [[#6311](https://github.com/sonata-project/SonataAdminBundle/pull/6311)] Passing another type than `object` to `AbstractAdmin::toString` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6311](https://github.com/sonata-project/SonataAdminBundle/pull/6311)] Passing `null` to `AbstractAdmin::getUrlSafeIdentifier`, `AbstractAdmin::getNormalizedIdentifier`, `AbstractAdmin::getId` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6311](https://github.com/sonata-project/SonataAdminBundle/pull/6311)] Using `GetShortObjectDescriptionAction` with an empty objectId. ([@VincentLanglet](https://github.com/VincentLanglet))

### Fix
- [[#6361](https://github.com/sonata-project/SonataAdminBundle/pull/6361)]  Fix calling undefined `AdminInterface::isCurrentChild` ([@core23](https://github.com/core23))

### Fixed
- [[#6394](https://github.com/sonata-project/SonataAdminBundle/pull/6394)] Fixed ProxyQueryInterface::getSortBy and ProxyQueryInterface::getSortOrder phpdoc ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6377](https://github.com/sonata-project/SonataAdminBundle/pull/6377)] Allow null transformation in ModelsToArrayTransformer ([@core23](https://github.com/core23))
- [[#5937](https://github.com/sonata-project/SonataAdminBundle/pull/5937)] Allow use Symfony Data Transformers in list fields editable ([@peter-gribanov](https://github.com/peter-gribanov))
- [[#6372](https://github.com/sonata-project/SonataAdminBundle/pull/6372)] The default `group` now correctly use the admin translation domain when using the admin label. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6335](https://github.com/sonata-project/SonataAdminBundle/pull/6335)] Remove unnecessary span wrapper for field help. Merge styles with paragraph. ([@kirya-dev](https://github.com/kirya-dev))
- [[#6335](https://github.com/sonata-project/SonataAdminBundle/pull/6335)] Prevent too many deprecation notices. ([@kirya-dev](https://github.com/kirya-dev))

### Removed
- [[#6334](https://github.com/sonata-project/SonataAdminBundle/pull/6334)] Long time deprecated and unused `setDefaultOptions` methods using `Symfony\Component\OptionsResolver\OptionsResolverInterface` ([@dmaicher](https://github.com/dmaicher))

## [3.75.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.74.0...3.75.0) - 2020-08-26
### Changed
- [[#6313](https://github.com/sonata-project/SonataAdminBundle/pull/6313)] Bump "knplabs/knp-menu-bundle" to ^3.0 ([@dmaicher](https://github.com/dmaicher))

### Deprecated
- [[#6312](https://github.com/sonata-project/SonataAdminBundle/pull/6312)] Deprecated `Sonata\AdminBundle\Model\ModelManagerInterface` collection-related methods ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6288](https://github.com/sonata-project/SonataAdminBundle/pull/6288)] Deprecated passing `callable` that does not return `Symfony\Component\Routing\Route` as `$element` (2nd argument) to `Sonata\AdminBundle\Route\RouteCollection::addElement($code, $element)` ([@vladyslavstartsev](https://github.com/vladyslavstartsev))

### Fixed
- [[#6325](https://github.com/sonata-project/SonataAdminBundle/pull/6325)] Fixed allowing valid action names in `CRUDController::batchAction()` ([@phansys](https://github.com/phansys))
- [[#6321](https://github.com/sonata-project/SonataAdminBundle/pull/6321)] Fixed mutating the original request at `CRUDController::batchAction()` ([@phansys](https://github.com/phansys))
- [[#6271](https://github.com/sonata-project/SonataAdminBundle/pull/6271)] "Notice: Undefined index: idx" if there are no list items are selected ([@nieuwenhuisen](https://github.com/nieuwenhuisen))

## [3.74.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.73.0...3.74.0) - 2020-08-22
### Added
- [[#6299](https://github.com/sonata-project/SonataAdminBundle/pull/6299)] Added support for symfony/expression-language:^5.1 ([@phansys](https://github.com/phansys))
- [[#6299](https://github.com/sonata-project/SonataAdminBundle/pull/6299)] Added support for symfony/options-resolver:^5.1 ([@phansys](https://github.com/phansys))
- [[#6299](https://github.com/sonata-project/SonataAdminBundle/pull/6299)] Added support for symfony/property-access:^5.1 ([@phansys](https://github.com/phansys))
- [[#6127](https://github.com/sonata-project/SonataAdminBundle/pull/6127)] Added support for `doctrine/common` 3 ([@jaikdean](https://github.com/jaikdean))
- [[#6256](https://github.com/sonata-project/SonataAdminBundle/pull/6256)] Added compatibility with Twig 3 ([@franmomu](https://github.com/franmomu))
- [[#6252](https://github.com/sonata-project/SonataAdminBundle/pull/6252)] Support for 'label_translation_parameters' in filter form types ([@tkuska](https://github.com/tkuska))
- [[#6212](https://github.com/sonata-project/SonataAdminBundle/pull/6212)] Add support for SonataBlockBundle 4.0 ([@jordisala1991](https://github.com/jordisala1991))

### Changed
- [[#6251](https://github.com/sonata-project/SonataAdminBundle/pull/6251)] Cast $allItems to an boolean to prevent typehint error ([@nieuwenhuisen](https://github.com/nieuwenhuisen))

### Deprecated
- [[#6308](https://github.com/sonata-project/SonataAdminBundle/pull/6308)] Passing a Pool to SearchHandler class ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6291](https://github.com/sonata-project/SonataAdminBundle/pull/6291)] Deprecated `Sonata\AdminBundle\Datagrid\ProxyQueryInterface::getSingleScalarResult` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6238](https://github.com/sonata-project/SonataAdminBundle/pull/6238)] Deprecated `base_filter_field.html.twig` template ([@franmomu](https://github.com/franmomu))
- [[#6238](https://github.com/sonata-project/SonataAdminBundle/pull/6238)] Deprecated `base_inline_edit_field.html.twig` template ([@franmomu](https://github.com/franmomu))
- [[#6238](https://github.com/sonata-project/SonataAdminBundle/pull/6238)] Deprecated `base_standard_edit_field.html.twig` template ([@franmomu](https://github.com/franmomu))
- [[#6238](https://github.com/sonata-project/SonataAdminBundle/pull/6238)] Deprecated `edit_array.html.twig ` template ([@franmomu](https://github.com/franmomu))
- [[#6238](https://github.com/sonata-project/SonataAdminBundle/pull/6238)] Deprecated `edit_boolean.html.twig` template ([@franmomu](https://github.com/franmomu))
- [[#6238](https://github.com/sonata-project/SonataAdminBundle/pull/6238)] Deprecated `edit_file.html.twig` template ([@franmomu](https://github.com/franmomu))
- [[#6238](https://github.com/sonata-project/SonataAdminBundle/pull/6238)] Deprecated `edit_integer.html.twig` template ([@franmomu](https://github.com/franmomu))
- [[#6238](https://github.com/sonata-project/SonataAdminBundle/pull/6238)] Deprecated `edit_sonata_type_immutable_array.html.twig` template ([@franmomu](https://github.com/franmomu))
- [[#6238](https://github.com/sonata-project/SonataAdminBundle/pull/6238)] Deprecated `edit_string.html.twig` template ([@franmomu](https://github.com/franmomu))
- [[#6238](https://github.com/sonata-project/SonataAdminBundle/pull/6238)] Deprecated `edit_text.html.twig` template ([@franmomu](https://github.com/franmomu))
- [[#6215](https://github.com/sonata-project/SonataAdminBundle/pull/6215)] Deprecated `BaseFieldDescription::setHelp()` and `BaseFieldDescription::getHelp()` ([@franmomu](https://github.com/franmomu))
- [[#6215](https://github.com/sonata-project/SonataAdminBundle/pull/6215)] Deprecated passing `help` option to `BaseFieldDescription::setOptions()` ([@franmomu](https://github.com/franmomu))
- [[#6215](https://github.com/sonata-project/SonataAdminBundle/pull/6215)] Deprecated `FormMapper::setHelps()` and `FormMapper:: addHelp()` ([@franmomu](https://github.com/franmomu))
- [[#6215](https://github.com/sonata-project/SonataAdminBundle/pull/6215)] Deprecated passing `help` option to `FormMapper::add()` third argument containing HTML code without also passing `help_html` with `true` value ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#6297](https://github.com/sonata-project/SonataAdminBundle/pull/6297)] ObjectManipulator::setObject does not throw an error anymore for DoctrineODM Embedded collections ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.73.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.72.0...3.73.0) - 2020-07-31
### Added
- [[#6200](https://github.com/sonata-project/SonataAdminBundle/pull/6200)] Added support for columns not belonging to the model to the list view. ([@jorrit](https://github.com/jorrit))
- [[#6206](https://github.com/sonata-project/SonataAdminBundle/pull/6206)] Support for 'help_translation_parameters' in form types ([@tkuska](https://github.com/tkuska))
- [[#6206](https://github.com/sonata-project/SonataAdminBundle/pull/6206)] Support for 'label_translation_parameters' in form types ([@tkuska](https://github.com/tkuska))

### Changed:
- [[#6225](https://github.com/sonata-project/SonataAdminBundle/pull/6225)] Stop to hide default filters in order to provide a more natural UX-behavior. ([@kirya-dev](https://github.com/kirya-dev))

### Deprecated
- [[#6220](https://github.com/sonata-project/SonataAdminBundle/pull/6220)] Passing `null` or an instance of `EngineInterface` as argument 2 when instantiating `Sonata\AdminBundle\Block\AdminListBlockService`, `Sonata\AdminBundle\Block\AdminSearchBlockService` or `Sonata\AdminBundle\Block\AbstractBlockService` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6225](https://github.com/sonata-project/SonataAdminBundle/pull/6225)] Abstract:: isDefaultFilter method. ([@kirya-dev](https://github.com/kirya-dev))
- [[#6234](https://github.com/sonata-project/SonataAdminBundle/pull/6234)] Deprecated `ModelManagerInterface::getParentFieldDescription`. ([@franmomu](https://github.com/franmomu))
- [[#6200](https://github.com/sonata-project/SonataAdminBundle/pull/6200)] Calling `SonataAdminExtension::getValueFromFieldDescription()` ([@jorrit](https://github.com/jorrit))

### Fixed
- [[#6219](https://github.com/sonata-project/SonataAdminBundle/pull/6219)] Allow to use AdminType with unidirectional field. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6222](https://github.com/sonata-project/SonataAdminBundle/pull/6222)] Set the Accept header for ajaxSubmit in `Resources/views/CRUD/Association/edit_many_script.html.twig` to  "application/json" to prevent 406 (Not acceptable)  error. ([@nieuwenhuisen](https://github.com/nieuwenhuisen))
- [[#6222](https://github.com/sonata-project/SonataAdminBundle/pull/6222)] CRUDController::handleXmlHttpRequestErrorResponse also accepts the wildcard Accept header  "*/*". ([@nieuwenhuisen](https://github.com/nieuwenhuisen))
- [[#6222](https://github.com/sonata-project/SonataAdminBundle/pull/6222)] CRUDController::handleXmlHttpRequestSuccessResponse also accepts the wildcard Accept header  "*/*". ([@nieuwenhuisen](https://github.com/nieuwenhuisen))
- [[#6224](https://github.com/sonata-project/SonataAdminBundle/pull/6224)] AdminExtractor sets subject created by `getNewInstance()` on Admin instance before extracting translatable strings. ([@jorrit](https://github.com/jorrit))
- [[#6144](https://github.com/sonata-project/SonataAdminBundle/pull/6144)] Can't use `datetime` field type as editable ([@peter-gribanov](https://github.com/peter-gribanov))
- [[#6167](https://github.com/sonata-project/SonataAdminBundle/pull/6167)] Show and edit `date` and `datetime` in the same timezone ([@peter-gribanov](https://github.com/peter-gribanov))
- [[#6223](https://github.com/sonata-project/SonataAdminBundle/pull/6223)] Admin has no active subclass exception in *edit_many_script.html.twig* after adding a ModelType field. ([@nieuwenhuisen](https://github.com/nieuwenhuisen))
- [[#6208](https://github.com/sonata-project/SonataAdminBundle/pull/6208)] Fixed checking if `FieldDescriptionInterface::getTargetModel()` exists before calling it. ([@franmomu](https://github.com/franmomu))
- [[#6112](https://github.com/sonata-project/SonataAdminBundle/pull/6112)] Allow to use parameters/placeholders in (sidebar) menu label translation. ([@pavol-tk](https://github.com/pavol-tk))

## [3.72.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.71.1...3.72.0) - 2020-07-14
### Added
- [[#6040](https://github.com/sonata-project/SonataAdminBundle/pull/6040)] Added new `AdminExtractor` to extract translations from the Admin classes ([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#6040](https://github.com/sonata-project/SonataAdminBundle/pull/6040)] `AdminExtractor` class for JMSTranslationBundle integration ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#6195](https://github.com/sonata-project/SonataAdminBundle/pull/6195)] Fixed design of validation message error when using a inline table collection. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6171](https://github.com/sonata-project/SonataAdminBundle/pull/6171)] EmbeddedAdmin now correctly set the parent object when creating a new instance. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6171](https://github.com/sonata-project/SonataAdminBundle/pull/6171)] Error message is correctly displayed for CollectionType ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6193](https://github.com/sonata-project/SonataAdminBundle/pull/6193)] Fixed default `[]` value for every non-nullable array class properties ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#5799](https://github.com/sonata-project/SonataAdminBundle/pull/5799)] Stop calling `mb_strlen()` on null in `RetrieveAutocompleteItemsAction` ([@mar20](https://github.com/mar20))
- [[#6183](https://github.com/sonata-project/SonataAdminBundle/pull/6183)] Fix form one-to-many tabs translations ([@EmmanuelVella](https://github.com/EmmanuelVella))

### Removed
- [[#6199](https://github.com/sonata-project/SonataAdminBundle/pull/6199)] Support for Symfony < 4.4 ([@wbloszyk](https://github.com/wbloszyk))

## [3.71.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.71.0...3.71.1) - 2020-06-30
### Changed
- [[#6170](https://github.com/sonata-project/SonataAdminBundle/pull/6170)] Move
  `twig/extra-bundle` from `require` to `suggest`
([@wbloszyk](https://github.com/wbloszyk))

### Fixed
- [[#6170](https://github.com/sonata-project/SonataAdminBundle/pull/6170)] Fix
  error with missing `u` filter when `twig/extra-bundle` is not registred
([@wbloszyk](https://github.com/wbloszyk))

## [3.71.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.70.1...3.71.0) - 2020-06-28
### Added
- [[#6134](https://github.com/sonata-project/SonataAdminBundle/pull/6134)]
  Added `StringOperatorType`
([@napestershine](https://github.com/napestershine))

### Fixed
- [[#6168](https://github.com/sonata-project/SonataAdminBundle/pull/6168)] Fix
  unit tests ([@peter-gribanov](https://github.com/peter-gribanov))

## [3.70.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.70.0...3.70.1) - 2020-06-21
### Fixed
- Allow `null` argument for `StringExtension` constructor

## [3.70.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.69.1...3.70.0) - 2020-06-19
### Added
- Added missing `ar` translations
- Added missing `bg` translations
- Added missing `ca` translations
- Added missing `cs` translations
- Added missing `de` translations
- Added missing `es` translations
- Added missing `eu` translations
- Added missing `fa` translations
- Added missing `hr` translations
- Added missing `hu` translations
- Added missing `it` translations
- Added missing `ja` translations
- Added missing `lb` translations
- Added missing `lt` translations
- Added missing `lv` translations
- Added missing `no` translations
- Added missing `pl` translations
- Added missing `pt` translations
- Added missing `pt_BR` translations
- Added missing `ro` translations
- Added missing `ru` translations
- Added missing `sk` translations
- Added missing `sl` translations
- Added missing `sv_SE` translations
- Added missing `tr` translations
- Added missing `uk` translations
- Added missing `zn_CH` translations

### Fixed
- `getFormFieldDescriptions`, `getFormFieldDescription` and
  `hasFormFieldDescription` doesn't build form anymore if the build already
started, avoiding an infinite loop.
- `getShowFieldDescriptions`, `getShowFieldDescription` and
  `hasShowFieldDescription` doesn't build show anymore if the build already
started, avoiding an infinite loop.
- `getListFieldDescriptions`, `getListFieldDescription` and
  `hasListFieldDescription` doesn't build list anymore if the build already
started, avoiding an infinite loop.
- `getFilterFieldDescriptions`, `getFilterFieldDescription` and
  `hasFilterFieldDescription` doesn't build datagrid anymore if the build
already started, avoiding an infinite loop.

### Deprecated
- Deprecated `sonata_admin.options.legacy_twig_text_extension` configuration

## [3.69.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.69.0...3.69.1) - 2020-06-16
### Fixed
- Infinite recursion error when mapping form fields with a help option set.

## [3.69.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.68.0...3.69.0) - 2020-06-14
### Added
- Added `FieldDescriptionInterface::getParent()`.
- Added `FieldDescriptionInterface::getAssociationAdmin()`.
- Added `FieldDescriptionInterface::getAdmin()`.
- Body classes `sonata-icheck` and `sonata-select2` when iCheck or Select2 are
  enabled.
- Added `Sonata\AdminBundle\EventListener\AssetsInstallCommandListener` to add
  `sonatacore` asssets when SonataCoreBundle is not installed

### Fixed
- Type guessing the `_action` list field.
- Styling of checkboxes and radio buttons when iCheck is disabled.

### Changed
- Changed use of `truncate` filter with `u` filter.
- ignore `sonata.admin.configuration.legacy_twig_text_extension` when
  SonataCoreBundle is not installed

### Deprecated
- Calling `AbstractAdmin::getChild()` when there is no child.
- Calling `BaseFieldDescription::getParent()` when there is no parent.
- Calling `BaseFieldDescription::getAssociationAdmin()` when there is no
  association admin.
- Calling `BaseFieldDescription::getAdmin()` when there is no admin.
- Calling `Pool::getAdminByClass()` when there is no admin for the class.
- Deprecated `Sonata\AdminBundle\Twig\Extension\UnicodeString` in favor of
  `Symfony\Component\String\UnicodeString`.
- Deprecated `sonata_truncate` in favor of `u.truncate`.
- Deprecated `FieldDescriptionInterface::getTargetEntity()` in favor of
  `FieldDescriptionInterface::getTargetModel()`;
- Deprecated `AdminHelper::getEntityClassName()` in favor of
  `AdminHelper::getModelClassName()`;
- Deprecated `GenerateObjectAclCommand::getUserEntityClass()` in favor of
  `GenerateObjectAclCommand::getUserModelClass()`.
- Deprecated `--user_entity` option in favor of `--user_model` at
  `sonata:admin:generate-object-acl` command.

### Removed
- remove all `SonataCoreBundle` dependencies

## [3.68.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.67.0...3.68.0) - 2020-05-31
### Added
- Added `TemplateRegistry::TYPE_*` constant to be used instead of string value.
- Added `format` option for `time` field type.

### Fixed
- Fixed exposing missing `object` variable in history compare view.
- Menu item `label_catalogue` correctly use the default value
  `default_label_catalogue`
- Menu item `icon` correctly use the default value `default_icon`
- Render of CKeditor field when embedded in a collection with the `inline =>
  natural` option.

### Deprecated
- Deprecated `smallint` type for template ; use `integer` instead.
- Deprecated `bigint` type for template ; use `integer` instead.
- Deprecated `decimal` type for template ; use `float` instead.
- Deprecated `text` type for template ; use `string` instead.

## [3.67.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.66.0...3.67.0) - 2020-05-28
### Added
- Manage class definition which is using "%parameter%" syntax.
- Added some `AbstractAdmin::configureDefaultSortValues` to override $datagridValues.
- Added some `AbstractAdminExtension::configureDefaultSortValues` to override $datagridValues.

### Fixed
- Reference to configuration option in `legacy_twig_text_extension` deprecation message.
- Call to deprecated `trans` method of `AbstractAdmin`
- Declare missing `one_to_many`, `many_to_many`, `many_to_one` and `one_to_one` type templates.

### Changed
- Update `doctrine/inflection` to ^1.4 || ^2.0
- update index of the first element in collection from 1 to 0

### Deprecated
- Accessing to a non existing value when adding field to `showMapper` and `listMapper`.
- Deprecate the `AbstractAdmin::maxPerPage` property
- Deprecate the `AbstractAdmin::setMaxPerPage` method
- Deprecate the `AbstractAdmin::perPageOption` property
- Deprecate the `AbstractAdmin::setPerPageOption` method
- Deprecate the `AbstractAdmin::predefinePerPageOptions` method
- Deprecate the `AbstractAdmin::datagridValues` property
- Deprecate implementing `ModelManagerInterface` without implementing `DatagridManagerInterface`

## [3.66.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.65.0...3.66.0) - 2020-05-03
### Added
- The possibility to edit/create an object without any field set in the configureFormField.
- Allow reuse render extra parameters

### Deprecated
- not passing a `Sonata\AdminBundle\Admin\AdminHelper` instance to
`Sonata\AdminBundle\Form\Type\AdminType::__construct()`
- Deprecate the call of `AbstractAdmin::getParentFieldDescription` if the value is `null`.
- Deprecate the call of `AbstractAdmin::getSubject` if the value is `null`.
- Deprecate the call of `AbstractAdmin::getListFieldDescription` if the value is `null`.
- Deprecate the call of `AbstractAdmin::getParent` if the value is `null`.

### Fixed
- Do not lose the `parentSubject` in case of multiple calls to the `AppendFormFieldElementAction`.
- Bug when trying to edit a datetime formtype in datagrid
- `label => false` doesn't lead to a missing `''` translation in the profiler.
- `label => '0'` and others non nullable falsy value are not overridden anymore.
- Fixed documented return type of `Sonata\AdminBundle\Filter\FilterInterface::getRenderSettings()`.

## [3.65.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.64.0...3.65.0) - 2020-04-21
### Added
- Added `AbstractAdmin::IsCurrentChild` method
- Added missing polish translation
- Added new options (`display`, `key_translation_domain` and
`value_translation_domain`) to the array field on Show and List pages.
- Automatically set `sortable`, `sort_parent_association_mappings`, and
`sort_field_mapping` when `associated_property` is set in `ListMapper::add`
to enable sorting on `associated_property` by default.

### Deprecated
- `AbstractAdmin::GetCurrentChild` method
- Deprecate `getModelIdentifier` in favor of  `getIdentifierFieldNames` in
`ModelManagerInterface`.
- Deprecated returning other types than `array` in
`AbstractAdmin::getFormGroups()`, `AbstractAdmin::getFormTabs()`,
`AbstractAdmin::getShowTabs()`, `AbstractAdmin::getShowGroups()`.
- `SimplePager::getResults` will not return ArrayCollection in next major
  version (4.0)

### Fixed
- Fixed nesting Twig block definitions under a non-capturing nodes.
- Fixed collisions with cache keys at `AbstractAdmin::isGranted()`.
- Fixed returning `void` in some methods which are intended to return a value
or `null`.
- Type of argument 3 passed to `UrlGeneratorInterface::generateMenuUrl()`.
- `AdminHelper::addNewInstance` to detect methods based on `method_exists`
instead of callable to maintain previous BC behavior.
- Admin Type is correctly using parentAssociationsMappings when using form with
  OneToOneToMany fields.

### Changed
- `$this->getSubject()` always returns a value in `configureFormField`

## [3.64.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.63.0...3.64.0) - 2020-03-31
### Added
- Added a new option `link_parameters` for list action button. This option is
  passed to `generateObjectUrl`.
- Added configuration parameter
`sonata_admin.options.legacy_twig_text_extension` in order to control which
text extension will be used;
- Added `truncate.cut` and `truncate.ellipsis` option in `list_html.html.twig`
and `show_html.html.twig` views.

### Deprecated
- Constructing `SonataAdminExtension` with an instance of TranslationInterface
  from components
- Deprecated "truncate.preserve" and "truncate.separator" options in
`list_html.html.twig` and `show_html.html.twig` views;
- Not setting `sonata_admin.options.legacy_twig_text_extension` as `false`.

## [3.63.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.62.1...3.63.0) - 2020-03-21
### Added
- Added SonataIntlBundle support back
- Added `configureQuery` method for an easier way to override Admin list queries.

### Fixed
- Class Helper addNewInstance take care about parentAssociation mapping.

### Deprecated
- Inheritance of the `createQuery` method.

## [3.62.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.62.0...3.62.1) - 2020-03-17
### Fixed
- Accessing to a non-existing parameter when extending `AbstractSonataAdminExtension`
- Check for "application/json" presence at `Accept` HTTP header, even if multiple types are provided.
- Deprecation about passing more than one attribute to `isGranted`

## [3.62.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.61.0...3.62.0) - 2020-03-16
### Added
- Allow `_sort_by` filter to not be initially defined.
- Import templates from SonataIntlBundle

### Removed
- deleteAction and batchAction does not use anymore the csrf_protection option from the editAction.
- Dropped SonataIntlBundle

### Fixed
- Stop calling the configureFormField in deleteAction and batchAction.
- Deprecation passing more than one attribute to `isGranted`
- `_sort_by` without `_sort_order` does not use invalid value anymore
- Deprecation passing more than one attribute to isGranted
- Only trigger deprecations of `sonata_help` when it is actually used

### Deprecated
- Deprecate id param of Sonata Action

## [3.61.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.60.0...3.61.0) - 2020-02-18
### Added
- `ifEnd` throws `LogicException` if there is nothing to end.

### Fixed
- Calling `ContainerBuilder::getDefinition()` with ids which have no associated
  definition.

## [3.60.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.59.0...3.60.0) - 2020-02-17
### Added
- `sonata.admin.manager` tag to services implementing `ModelManagerInterface`.

### Fixed
- Wording of `EqualOperatorType`

### Deprecated
- The use of `sonata_help` in form types

## [3.59.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.58.0...3.59.0) - 2020-02-10
### Added
- Add support for symfony form help

### Fixed
- Make filter alias optional
- Fix error of using list ValueObject as editable field in list fields.

## [3.58.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.57.0...3.58.0) - 2020-01-26
### Fixed
- Revert to trully unique uniqid for admins
- Deprecations for event dispatching

### Removed
- Support for Symfony < 4.3

## [3.57.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.56.1...3.57.0) - 2020-01-13
### Added
- Added OperatorType usable in CallbackFilter
- Multiple editable fields now is a real multiple.
- Toggle edit button `sonata_type_model_list_widget` after operations
  add/list/remove

## Fixed
- php 7.4 compatibility
- Fixed help messages not showing up in one to many inline tables.

### Changed
- Include the `edit_modal` and `edit_many_script` templates only if there is at
  least one displayed button that need them (instead of always include them).
- `IfTrue` and `IfFalse` apply correctly to tab and with functions. Nested
  `IfTrue` and `IfFalse` work as expected and do not throws exception anymore
- Mapper methods now throw `LogicException` instead of `RuntimeException`

### Deprecated
- The use of string names to reference filters

## [3.56.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.56.0...3.56.1) - 2019-12-07
### Fixed
- Ability of using string names and FQCNs to define filter types

## [3.56.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.55.0...3.56.0) - 2019-12-05
### Fixed
-  compatibility with `doctrine/doctrine-bundle` 2

### Removed
- Support for Symfony < 3.4
- Support for Symfony >= 4, < 4.2

## [3.55.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.54.1...3.55.0) - 2019-11-25
### Fixed
- crash when using the command that generates ACLs
- crash when using `bin/console list` or just `bin/console`
- Validation error on dashboard with `sonata.admin.block.stats`

### Changed
- deleteAction now respects csrf_protection

## [3.54.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.54.0...3.54.1) - 2019-10-14
### Fixed
- Using array accessor in traversable objects which aren't implementing
  `\ArrayAccess` at `AbstractAdmin::buildForm()`.
- Arguments passed to `transchoice()` Twig filter at `block_stats.html.twig`

## [3.54.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.53.0...3.54.0) - 2019-10-01
### Fixed
- incorrect sanity check ACL manipulators
- Broken layout on very large breadcrumb fragments.
- Using `{% trans %}` Twig tag for pluralized catalogs with
`symfony/translation` < 4.2.
- JavaScript exception and incorrect form input type for Autocomplete form type

### Changed
- `CRUDController::validateCsrfToken` to validate tokens not only from a POST
request, but GET as well
- `CRUDController::validateCsrfToken` to accept missing request token.

## [3.53.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.52.0...3.53.0) - 2019-09-03
### Fixed
- Fixed error when rendering revision list with Twig's `strict_variables` enabled

### Changed
- Usages of deprecated `transchoice` tags with `trans`
- Minimum version required for `sonata-project/block-bundle` (3.11 => 3.17).

## [3.52.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.51.0...3.52.0) - 2019-08-16
### Added
- Added `format` option to show type `trans`
- Method `TemplateRegistry::hasTemplate()` in order to know if a template with a given name is registered.

### Changed
- The visual aspect at column headers with sorting icons, in order to add some extra spacing between the column's title and its sorting icon.

### Deprecated
- The inheritance from these classes:
  * `Sonata\AdminBundle\Admin\AdminHelper`;
  * `Sonata\AdminBundle\Admin\Extension\LockExtension`;
  * `Sonata\AdminBundle\Admin\FieldDescriptionCollection`;
  * `Sonata\AdminBundle\Admin\Pool`;
  * `Sonata\AdminBundle\Annotation\Admin`;
  * `Sonata\AdminBundle\Block\AdminListBlockService`;
  * `Sonata\AdminBundle\Block\AdminSearchBlockService`;
  * `Sonata\AdminBundle\Block\AdminStatsBlockService`;
  * `Sonata\AdminBundle\Command\ExplainAdminCommand`;
  * `Sonata\AdminBundle\Command\GenerateAdminCommand`;
  * `Sonata\AdminBundle\Command\GenerateObjectAclCommand`;
  * `Sonata\AdminBundle\Command\ListAdminCommand`;
  * `Sonata\AdminBundle\Command\SetupAclCommand`;
  * `Sonata\AdminBundle\Command\Validators`;
  * `Sonata\AdminBundle\Datagrid\Datagrid`;
  * `Sonata\AdminBundle\Datagrid\DatagridMapper`;
  * `Sonata\AdminBundle\Datagrid\ListMapper`;
  * `Sonata\AdminBundle\Datagrid\SimplePager`;
  * `Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass`;
  * `Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass`;
  * `Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass`;
  * `Sonata\AdminBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass`;
  * `Sonata\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass`;
  * `Sonata\AdminBundle\DependencyInjection\Configuration`;
  * `Sonata\AdminBundle\DependencyInjection\SonataAdminExtension`;
  * `Sonata\AdminBundle\Event\AdminEventExtension`;
  * `Sonata\AdminBundle\Event\ConfigureEvent`;
  * `Sonata\AdminBundle\Event\ConfigureMenuEvent`;
  * `Sonata\AdminBundle\Event\ConfigureQueryEvent`;
  * `Sonata\AdminBundle\Event\PersistenceEvent`;
  * `Sonata\AdminBundle\Exception\LockException`;
  * `Sonata\AdminBundle\Exception\ModelManagerException`;
  * `Sonata\AdminBundle\Exception\NoValueException`;
  * `Sonata\AdminBundle\Export\Exporter`;
  * `Sonata\AdminBundle\Filter\FilterFactory`;
  * `Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader`;
  * `Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer`;
  * `Sonata\AdminBundle\Form\DataTransformer\LegacyModelsToArrayTransformer`;
  * `Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer`;
  * `Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer`;
  * `Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer`;
  * `Sonata\AdminBundle\Form\EventListener\MergeCollectionListener`;
  * `Sonata\AdminBundle\Form\Extension\ChoiceTypeExtension`;
  * `Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension`;
  * `Sonata\AdminBundle\Form\Extension\Field\Type\MopaCompatibilityTypeFieldExtension`;
  * `Sonata\AdminBundle\Form\FormMapper`;
  * `Sonata\AdminBundle\Form\Type\AclMatrixType`;
  * `Sonata\AdminBundle\Form\Type\AdminType`;
  * `Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType`;
  * `Sonata\AdminBundle\Form\Type\CollectionType`;
  * `Sonata\AdminBundle\Form\Type\Filter\ChoiceType`;
  * `Sonata\AdminBundle\Form\Type\Filter\DateRangeType`;
  * `Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType`;
  * `Sonata\AdminBundle\Form\Type\Filter\DateTimeType`;
  * `Sonata\AdminBundle\Form\Type\Filter\DateType`;
  * `Sonata\AdminBundle\Form\Type\Filter\DefaultType`;
  * `Sonata\AdminBundle\Form\Type\Filter\NumberType`;
  * `Sonata\AdminBundle\Form\Type\ModelAutocompleteType`;
  * `Sonata\AdminBundle\Form\Type\ModelHiddenType`;
  * `Sonata\AdminBundle\Form\Type\ModelListType`;
  * `Sonata\AdminBundle\Form\Type\ModelReferenceType`;
  * `Sonata\AdminBundle\Form\Type\ModelType`;
  * `Sonata\AdminBundle\Generator\AdminGenerator`;
  * `Sonata\AdminBundle\Generator\ControllerGenerator`;
  * `Sonata\AdminBundle\Guesser\TypeGuesserChain`;
  * `Sonata\AdminBundle\Manipulator\ServicesManipulator`;
  * `Sonata\AdminBundle\Menu\Matcher\Voter\ActiveVoter`;
  * `Sonata\AdminBundle\Menu\Matcher\Voter\AdminVoter`;
  * `Sonata\AdminBundle\Menu\MenuBuilder`;
  * `Sonata\AdminBundle\Menu\Provider\GroupMenuProvider`;
  * `Sonata\AdminBundle\Model\AuditManager`;
  * `Sonata\AdminBundle\Route\AdminPoolLoader`;
  * `Sonata\AdminBundle\Route\DefaultRouteGenerator`;
  * `Sonata\AdminBundle\Route\PathInfoBuilder`;
  * `Sonata\AdminBundle\Route\QueryStringBuilder`;
  * `Sonata\AdminBundle\Route\RouteCollection`;
  * `Sonata\AdminBundle\Route\RoutesCache`;
  * `Sonata\AdminBundle\Route\RoutesCacheWarmUp`;
  * `Sonata\AdminBundle\Search\SearchHandler`;
  * `Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap`;
  * `Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder`;
  * `Sonata\AdminBundle\Security\Handler\AclSecurityHandler`;
  * `Sonata\AdminBundle\Security\Handler\NoopSecurityHandler`;
  * `Sonata\AdminBundle\Security\Handler\RoleSecurityHandler`;
  * `Sonata\AdminBundle\Show\ShowMapper`;
  * `Sonata\AdminBundle\SonataAdminBundle`;
  * `Sonata\AdminBundle\Translator\BCLabelTranslatorStrategy`;
  * `Sonata\AdminBundle\Translator\Extractor\JMSTranslatorBundle\AdminExtractor`;
  * `Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy`;
  * `Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy`;
  * `Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy`;
  * `Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy`;
  * `Sonata\AdminBundle\Twig\Extension\SonataAdminExtension`;
  * `Sonata\AdminBundle\Twig\GlobalVariables`;
  * `Sonata\AdminBundle\Util\AdminAclManipulator`;
  * `Sonata\AdminBundle\Util\AdminObjectAclData`;
  * `Sonata\AdminBundle\Util\AdminObjectAclManipulator`;
  * `Sonata\AdminBundle\Util\FormBuilderIterator`;
  * `Sonata\AdminBundle\Util\FormViewIterator`.
- Passing invalid filter names to `Datagrid::getFilter()`;
- Passing invalid template names to `TemplateRegistry::getTemplate()`;
- Calling `AbstractAdmin::getActiveSubClass()` and `AbstractAdmin::getActiveSubclassCode()` when there is no active subclass.

### Fixed
- Returning `void` instead of `null` in functions which are capable to return values.
- Possibility to resolve Twig dependency to versions that don't support arrow functions on Twig filters.
- Call setName method in configure part of Command, for backward compatibility wiht sf 2.8.x
- Fixed `ModelManagerCompilerPass` & `ObjectAclManipulatorCompilerPass` to avoid crashing when there's services with numerical ids
- Error caused by passing a string instead object to `AbstractAdmin::toString()` from `base_list.htm.twig` when the admin's subject doesn't declare `__toString()` method.
- Fixed ChoiceFieldMaskType's twig template JavaScript using unescaped field value
- Fix typo in Russian translation

## [3.51.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.50.0...3.51.0) - 2019-06-27
### Changed
- The default value for "admin_code" setting  at `AdminSearchBlockService`.
- Values to passed with the "identifier" option for `ListMapper::add()` are
  cast to boolean before using them to infer if the field must be used as
  identifier or not.

### Deprecated
- Passing a non string value as argument 1 to `Pool::getAdminByAdminCode()`;
- Passing a non valid admin hierarchy as argument 1 to `Pool::getAdminByAdminCode()`.

### Fixed
- Edit form field group descriptions may again contain HTML.
- Crash when clicking "add" on a collection

## [3.50.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.49.1...3.50.0) - 2019-06-22
### Added
- Added "role" option for methods `ListMapper::add()`, `DatagridMapper::add()`,
  `ShowMapper::add()` and `FormMapper::add()`; in order to restrict the content
  rendering based on the provided role.

### Fixed
- Fixed deprecation notice when core services are injected in service through
  autowiring
- Deprecated controller syntax generated by "sonata:admin:generate" command.
- Bumped "twig/twig" dependency to "^2.9"
- Changed usages of `{% spaceless %}` tag, which is deprecated as of Twig 1.38
  with `{% apply spaceless %}` filter
- Changed usages of `{% for .. if .. %}`, which is deprecated as of Twig 2.10
  with `filter` filter'
- Edit form field group descriptions may again contain HTML.

### Changed
- Truncate long titles at 100 characters at breadcrumb and navbar.
- Subject is now fully displayed at navbar. Before, it was using the same title
  as the `<title>` tag, which is truncated to 15 characters.
- Values to passed with the "identifier" option for `ListMapper::add()` are
  cast to boolean before using them to infer if the field must be used as
  identifier or not.

### Deprecated
- Passing non boolean values to "identifier" option for `ListMapper::add()`.

## [3.49.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.49.0...3.49.1) - 2019-06-05

### Fixed
- Crash with Twig 2.11 with message `Template
  "@SonataAdmin/CRUD/base_edit_form.html.twig" cannot be used as a trait.`
(second attempt)

## [3.49.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.48.3...3.49.0) - 2019-06-02

### Fixed
- CreateClassCacheCommand deprecation message on container compiling
- Crash with Twig 2.11 with message `Template
  "@SonataAdmin/CRUD/base_edit_form.html.twig" cannot be used as a trait.`

### Changed
- Changed the rendering for the audit revision timestamp in order to use
  `<time>` tags, which print the dates in UTC using `datetime` and `title`
attributes, allowing to view the UTC date with the default browser tooltip.

## [3.48.3](https://github.com/sonata-project/SonataAdminBundle/compare/3.48.2...3.48.3) - 2019-05-21

### Fixed
- Fixed Tabs in Edit Form

## [3.48.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.48.1...3.48.2) - 2019-05-16

### Added
- Add canonicalization fallback for missing moment.js `de_DE` locale

### Fixed
- Use proper namespace for `Sonata\Exporter\Source\SourceIteratorInterface`
- Fix bootstrap tab toggle not working when clicking add button more than 2
  times.

### Changed
- Fix in edit page, the footer with actions buttons will be stuck for Windows
  users and the last field will no longer be hidden

## [3.48.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.48.0...3.48.1) - 2019-04-13
### Fixed
- Changed the way the search action generates links to the results. It used to
  consider every item editable, but would throw an error if it wasn't the case.
It now uses the `getSearchResultLink` that choses the best way to link to a
search result; eg. `edit` if available, or `show`.
- crash when submitting a form with only spaces in a required field
- redirecting to a blank tab after saving an object
- modifying form values changing tabs

## [3.48.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.47.1...3.48.0) - 2019-03-23
### Added
- Added ability to back to the tab which was edited on saving an object

## [3.47.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.47.0...3.47.1) - 2019-03-15
### Fixed
- Check if request exists before using it and getting an exception

## [3.47.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.46.0...3.47.0) - 2019-03-13
### Fixed
- `show_label` option not being honored when set to `false`
- type int for `_page` and `_per_page` from request
- display of batch checkbox when list page is loaded with Ajax
- wrong HTML generated (top nav menu), if the user does not have rights for
  first or last module

### Changed
- Changed the rendering for date, datetime and time properties in order to use
  `<time>` tags, which print the dates in UTC using `datetime` and `title`
attributes, allowing to view the UTC date with the default browser tooltip.
- Actions buttons were not displayed if the subject was cast to empty string
  through the `__toString` method.

## [3.46.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.45.2...3.46.0) - 2019-03-07

## Fixed
- the batch flash messages looked bad

### Deprecated
- Deprecated jms annotations

## [3.45.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.45.1...3.45.2) - 2019-02-14
### Fixed
- Regression bug which causes SonataMediaBundle's Media list to show fallback
  image in mosaic view instead of real image preview
- Crash when using `Metadata` class from block bundle
- `sonata_type_collection` fields no longer deletes row when adding a new row
- Admin maker no longer produces tabs

## [3.45.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.45.0...3.45.1) - 2019-01-14

### Fixed
- Crash about incompatible signatures involving `ErrorElement`
- Crash when using `null` as an admin class name

## [3.45.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.44.0...3.45.0) - 2019-01-14

### Added
- Added config for default mosaic background
- Added `Sonata\AdminBundle\Object\Metadata` class
- Compatibility with `sonata-project/exporter` 2
- php 7-specific type hints in generated code
- `strict_types` declaration in generated code
- generated classes are now final

### Changed
- Changed `Admin::getObjectMetadata` return type in PHPDoc

### Fixed
- Some deprecations about the new namespaces have been fixed
- 2 missing Dutch translations were added

## [3.44.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.43.0...3.44.0) - 2019-01-12

### Added
- Default admin service options (group, label catalogue and icon) are now configurable
- Added possibility to search globally case-sensitive/case-insensitive

### Removed
- support for php 5 and php 7.0

### Fixed
- Default values not getting overridden in `make:sonata:admin`

## [3.43.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.42.2...3.43.0) - 2018-12-15

### Added
- Added `template` option to admin `batchActions`

### Fixed
- Fix crash on form pages that use `ModelAutocompleteType` and does not have a
  create route

### Changed
- Better css layout for single column dropdowns

## [3.42.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.42.1...3.42.2) - 2018-12-07
### Fixed
- Remove "e" letter added after delete checkbox in inline table collection form

## [3.42.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.42.0...3.42.1) - 2018-12-06
### Fixed
- Fix crash on listing pages that have a datagrid filter

## [3.42.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.41.0...3.42.0) - 2018-12-03

### Fixed
- incorrect deprecation message about `AdminInterface::setSecurityHandler()`
- Error on some fields in collection table when `strict_variables` mode is enabled
- Newly created media were not autoselected

### Deprecated
- `header_style` option

## [3.41.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.40.3...3.41.0) - 2018-11-23
### Added
- Allow disabling front debug logs

### Fixed
- Fixed `AdminType` tabs ids when used in collections
- the list mode view is now preserved when a sorting is applied
## [3.40.3](https://github.com/sonata-project/SonataAdminBundle/compare/3.40.2...3.40.3) - 2018-11-10
### Added
- Added missing polish translation for `link_edit`

### Fixed
- Change icon on list column sorting
- invalid html in the export links list
- FrameworkBundle redirect action notation to the current syntax
- BC break when baseControllerName uses bundle notation with subfolder

## [3.40.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.40.1...3.40.2) - 2018-10-17
### Fixed
- Add display of missing `sonata_help` on many form type
- confirm_exit, use_select2, use_icheck and use_stickyforms settings.
- `make:sonata:admin` not working
- Moved the JS config in a meta tag in head section of the sonata_layout twig file

## [3.40.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.40.0...3.40.1) - 2018-10-08
### Fixed
- Minor bug in JavaScripts (Admin.js)

## [3.40.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.39.0...3.40.0) - 2018-10-06
### Added
- Added `Admin` object reference to javascript `window` object
- Added `RuntimeException` message for `Create` and `Show` actions from `CRUDController`
- Added a new maker to generate admin class, controller and service definition

### Fixed
- Added support for moment.js French language
- `Pool::getInstance` will always return a valid admin instance
- Fixed controller reference deprecations

### Removed
- Removed inline scripts for `SONATA_CONFIG` and `SONATA_TRANSLATIONS`

## [3.39.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.38.3...3.39.0) - 2018-09-09

### Added
- An affirmative grant checker for Twig
- Translation file for `zh_HK` (Traditional Chinese).

### Fixed
- Updated Luxembourgish translations
- Fixed negative admin unique id on 32 bits platforms
- Fixed iCheck inputs not triggering change event
- Fixed issue with `getNbResults` return negative result, if there where no results
- Dashboard block security was expected to be checked affirmatively rather than unanimously

### Changed
- moved `symfony/class-loader` into `require-dev` section of composer
- Use `Admin::getUniqid()` to generate HTML tab id

### Deprecated
- `CreateClassCacheCommand` is deprecated

## [3.38.3](https://github.com/sonata-project/SonataAdminBundle/compare/3.38.2...3.38.3) - 2018-08-21

### Fixed
- An error message about subclasses has been fixed
- issue Error 500 when requesting short object description as JSON

## [3.38.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.38.1...3.38.2) - 2018-08-17

### Fixed
- Checking the wrong method for form fields

## [3.38.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.38.0...3.38.1) - 2018-08-16
### Added
- Added exception message if no field is defined with the  `configureFormFields()` method

### Fixed
- Fixed tab id when the Admin Id contains backslashes

## [3.38.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.37.0...3.38.0) - 2018-08-14

### Added
- Added delete button in form action buttons when the request is ajax

### Fixed
- Added missing french translation
- Missing translation for `link_edit` in Dutch translation file.

### Changed
- Admin uniqids are now more consistent
- allow using overwritten constant for the mosaic html classes inside of admin class
- An exception message was improved to include hints about the correct configuration value

### Deprecated
- `SonataAdminBundle\Controller\HelperController` is now deprecated in favor of actions

## [3.37.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.36.0...3.37.0) - 2018-07-26
### Fixed
- fixed bug with complex properties in `ModelAutocompleteType`

### Changed
- Enable TimeZone for datetime and time templating
- Changing the way of checking the permissions when displaying links in templates

## [3.36.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.35.2...3.36.0) - 2018-07-17
### Added
- Added `title` to sidebar toggle
- Added missing polish translation for `Toggle Navigation`
- Added new JS function in Admin.js, which handle the control of a tabs and change address in URL query string when you change tab.
- Added a condition in the `getFieldGetterKey` in order remove the new warning produced by PHP7.2.

### Changed
- Escaped admin code in autocomplete
- `Controller\CoreController` is now deprecated in favor of `Action\{Search,Dasbhoard}Action`
- Added table-hover class to the table
- Changed ID's and added class name to tabs elements in edit view and show view, so we can control the address.
- Default load order of `assets.javascripts` at `Configuration::getConfigTreeBuilder()`

### Fixed
- Warning "Parameter must be an array or an object that implements Countable" for count(null) in php 7.2
- Fixed issue with `appendFormFieldElementAction` and `retrieveFormFieldElementAction` using ModelManager instead `getObject` admin class method
- Menu item security was expected to be checked affirmatively rather than unanimously
- Fixed choice field mask initial display when field value is empty
- Added missing russian translation for `Toggle Navigation`
- ECMAScript error `jquery.js:250 Uncaught Error: cannot call methods on button prior to initialization;` while trying to use `$.fn.button()` (ref: https://github.com/twbs/bootstrap/issues/6094)

## [3.35.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.35.1...3.35.2) - 2018-05-05
### Fixed
- Fixed route generation with parameter for on_top menu items
- Fixed custom group permission for menu items

## [3.35.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.35.0...3.35.1) - 2018-04-20
### Fixed
- Edit on list is fixed for choices not related to an entity

## [3.35.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.34.2...3.35.0) - 2018-04-16
### Added
- Admin can now have multiple parents

### Changed
- `AbstractAdmin::addChild` now accepts 2nd parameter with parent name

## [3.34.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.34.1...3.34.2) - 2018-04-11
### Deprecated
- Editing child admin that does not belong to a given parent

### Fixed
- Regression for child form type rendering
- Fixed a BC break where an overwritten `getTemplate()` method in an `Admin` was no longer called by Sonata.
- Not working persist_filter option for legacy admin property.

## [3.34.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.34.0...3.34.1) - 2018-04-09
### Fixed
- Fix regression on #5051: It is possible again to know which button triggered the submit of the form.

## [3.34.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.33.0...3.34.0) - 2018-04-09
### Added
- Added some `Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface` to externalise filter persistence to a dedicated class
- Added some `Sonata\AdminBundle\Filter\Persister\SessionFilterPersister` to store filters to session (as done now)
- Added `TemplateRegistry`, `TemplateRegistryInterface` and `MutableTemplateRegistryInterface` to handle all template registration related functionality from both `AbstractAdmin` and `Pool`.
- Added `required` option to editable list `choice`
- Added `search` option to enable/disable the search form in the sidebar

### Changed
- Changed `Sonata\AdminBundle\Admin\AbstractAdmin::getFilterParameters` to use the configured filter persister component
- Reordered bootstrap.js javascript dependency fixes problems with jQuery UI dialogs
- `side_bar_after_nav_content` block is now empty by default

### Deprecated
- Deprecated `AbstractAdmin` methods `getTemplate` and `getTemplates`.
- Deprecated `AbstractAdmin` attribute `$templates`.
- Deprecated `Pool` methods `getTemplate`, `setTemplates` and `getTemplates`.
- Deprecated `Pool` attribute `$templates`.
- Deprecated Twig function `get_pool_template`.

### Fixed
- if object is empty, don't try to create an edit route
- Fix edit choice with a relation field on admin list
- Added missing french translation for `Toggle Navigation`
- Explain command compatible with sf4
- Fixed deprecation notice when Pool is injected in service through autowiring
- `ChoiceFieldMaskType` now works on inline table collections
- Navbar positioning on mobile is no longer altered like in desktop
- Increase consistency on default page sizes (replaced 192 by 256)
- Disable form submit buttons when the form gets submitted
- form types FQCN are now used in filter. Improves compatibility with SF3/4
- Not working sidebar menu tree with AdminLTE v2.4

## [3.33.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.32.0...3.33.0) - 2018-03-12
### Changed
- Replaced calls to Twig internal `Environment::loadTemplate()` method with `Environment::load()` in `SonataAdminExtension`.

### Deprecated
- Deprecated `SonataAdminExtension::output()`. Now using the private `render()` method. Please use the `render*()` methods instead.

### Fixed
- `FieldDescription` null check missing in `ModelAutocompleteFilter`
- Fatal error in strict mode
- Avoid calling protected/private methods when retrieving values from entities
- `getFieldValue` from `BaseFieldDescription` can now handle getting a value from null objects.
- `base_list` template includes the configurable `button_create` template rather than the default `create_button.html.twig`.

## [3.32.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.31.1...3.32.0) - 2018-02-28
### Changed
- Allow capturing of any link click inside of modal
- batch checkbox is hidden when using the mosaic view in modal
- whole mosaic item is now wrapped in a tag instead of just title
- Make search result page less heavy

### Fixed
- `ModelAutocompleteType` can now be used without a linked admin class
- Compatibility with edit relation on list with PHPCR and MongoDB Sonata bundles
- fix behaviour of `AbstractAdmin::preValidate` by relying on form event listener
- Admins without global search filters will no longer be shown in the global search.
- including wrong javascript code for associations modals
- Fixed PHP warnings when `ChoiceFieldMaskType` option `map` is invalid or empty
- Fixed javascript handling of `ChoiceFieldMaskType` when option `expanded` is `true`
- Templates that rely on the `admin` variable in Twig can now use the `get_admin_template` function correctly.

## [3.31.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.31.0...3.31.1) - 2018-02-08
### Added
- Missing `getOwnerPermissions` to `AdminObjectAclData`

### Changed
- Switch all templates references to Twig namespaced syntax
- Switch from templating service to sonata.templating

### Fixed
- Not found issues for some locales which are not present in frontend dependencies like `moment` or `select2`
- Only do `$filter->apply` if the filter is "active" on the datagrid list
- Only do `$filter->apply` if the filter has a value that is not empty string
- Error if the field in filter list is named `children`
- Use FQCN form types for ACL form creation
- Fixed missing translation for `Toggle navigation`

## [3.31.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.30.1...3.31.0) - 2018-01-23
### Added
- Added new `safe_label` option to allow HTML pass-through on autocomplete form type
- Added filter counter to admin lists

### Changed
- template paths to adapt to the convention
- Replace twig paths with new naming conventions

### Fixed
- `request` attribute deprecation on `knp_menu.voter`
- Added path to cookies when hiding sidebar to avoid creating multiple cookies
- Nested arrays will show properly on show/list fields
- path to dashboard
- ModelHiddenType - default hidden attribute is now set to true
- Fix bug when we pass null as fieldName to BaseFieldDescriptor::getFieldValue

## [3.30.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.30.0...3.30.1) - 2018-01-02
### Added
- Make explicit dependency with `symfony/asset`

### Changed
- Admin and filter services are shared

## [3.30.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.29.0...3.30.0) - 2017-12-25
### Changed
- Menu on the left side stays hidden while changing pages.

### Deprecated
- `AbstractAdmin::addSubClass()`

### Fixed
- Checking for fixed class on body before adjusting the position of the content div
- Fixed container compile error, if JMSDiExtraBundle is enabled.
- Fixed twig dependency for sonata.admin.controller.admin service

## [3.29.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.28.0...3.29.0) - 2017-12-16
### Added
- Added edit button functionality
- Added possibility to add and remove javascripts/stylesheets without adding all defaults again

### Changed
- added missing Dutch translations
- Services tagged with `sonata.admin` and `sonata.admin.filter.type` are now public
- Improvements on `AbstractAdmin::getClass()` method

### Fixed
- Fixed calling route generator with boolean value
- Replace FQCN strings with `::class` constants
- Add trans filter to form_group and form_tab description
- added Turkish translations
- don't display fields that are missing in child classes
- Patched collection form handling script to maintain File input state when new items are added to collections
- exporter-related error during cache:clear command.
- added missing italian translations

## [3.28.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.27.0...3.28.0) - 2017-11-30
### Added
- Added `CRUDController::redirectToList` method for all list redirections
- added Russian translations

### Changed
- Handle empty id list in `CRUDController::batchAction`
- All services without a declared visibility are now public

### Fixed
- It is now allowed to install Symfony 4
- Updated `src/Resources/views/standard_layout.html.twig` template in order to remove whitespace rendering before HTML DOCTYPE declaration.
- interference with other bundles

### Deprecated
- using the `ChildrenVoter` class
- using the `sonata.admin.menu.matcher.voter.children` service

### Security
- Fixed XSS vulnerability in autocomplete form type

## [3.27.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.26.0...3.27.0) - 2017-11-26
### Added
- Added some Japanese messages
- Added `CRUDController::renderWithExtraParams` as a replacement for the `render` method

### Deprecated
- Deprecated `CRUDController::render`

### Fixed
- Problem with FormRenderer not having environment causing that inline collection was unusable on SF 3.4
- Deprecation warning for overriding `Controller::render` which is supposed to be final

## [3.26.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.25.1...3.26.0) - 2017-11-22
### Added
- Add html tag attributes support for sonata_type_model_autocomplete form type
- Added edit button that opens in dialog instead of add if there is object already in sonata type model list

### Fixed
- check if the field is used to sort the list
- Add a check for existing var associationadmin which is null for filter
- Fixed `AbstractAdmin::getSubject()` behavior when `id` parameter is not specified
- Add alias on `ChoiceType` uses to avoid collisions on Form filter classes
- BC-break in `CRUDController::render()`

### Removed
- Old usage of read_only var

## [3.25.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.25.0...3.25.1) - 2017-11-20
### Fixed
- Wrong configuration for `DateTimeType` and `DateType` filters

## [3.25.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.24.0...3.25.0) - 2017-11-19
### Added
- Ability to configure sonata main admin and super admin roles under the `security` configuration key
- Added `translation_domain` key to `AdminStatsBlockService` to change or disable translation
- Add support for `add` button in `sonata_type_model_autocomplete`

### Changed
- Changed internal folder structure to `src`, `tests` and `docs`
- a condition to check if the navbar is to be displayed

### Fixed
- subject assignment in embedded admins
- fixed choice_translation_domain for expanded choices in admin
- make false translation_domain working for the label if no translation is needed
- Removed overridden method `CRUDController::addFlash` which is final in SF 3.4.
- Don't translate empty placeholder on form render
- Register commands as services to prevent deprecation notices on Symfony 3.4
- `AbstractAdmin::hasSubject` is now populating `AbstractAdmin::$subject` property
- Deprecation about `Symfony\Component\DependencyInjection\DefinitionDecorator`
- getRuntime now receives a non deprecated runtime
- Fix for getRuntime on Symfony older than 3.4
- displaying a title when there are no specific actions

## [3.24.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.23.0...3.24.0) - 2017-10-23
### Added
- Add support for unlimited nested child admins.
- `Sonata\AdminBundle\Admin\AdminInterface` was split into smaller interfaces.

### Deprecated
- `AdminInterface::$baseCodeRoute` and `AdminInterface::setBaseCodeRoute(...)`.

### Fixed
- Fixed missing space when form class is defined in label_attr
- Fix duplicate DB queries on empty results.
- Fixed sticky navbar when top navbar height changes
- Fix missing flash message translations
- naming conflict with `UrlGeneratorInterface`
- Print of `sonata_help` for form subfields
- Error class for `sonata_type_immutable_array` form group
- Fixed the BaseFieldDescription class to also support 'has' prefixed getter methods for boolean properties on entities (besides the 'is' prefixed getters)
- Always apply "global_search.empty_boxes" setting to never searched admins
- Fixed a typo in CSS classes in `block_search_result.html.twig`
- Fixed autocomplete for cases when admin code uses service id and service id is equal to FQCN ('AppBundle\Admin\CompanyAdmin')
- Bad result when `Pool::getAdminByAdminCode()` was called with an invalid child path.

### Removed
- Support for old versions of PHP and Symfony.

## [3.23.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.22.0...3.23.0) - 2017-09-01
### Added
- Reference %sonata.admin.configuration.templates% when possible
- Added a `priority` option in `sonata_admin` extensions config

### Changed
- Passing object id in edit form from CRUD controller instead of getting it in twig

### Fixed
- Fixed the setting of the `translation_domain` twig variable. The value must change depending if the item has on_top set to true or false.
- Escaping of list headers.
- setting the column title
- Fixed deprecation when using checkbox in admin form type
- Fix knp menu extra configuration for domain translations in Group Menu
- Not declared variable trowing errors on some browsers

### Removed
- Useless IE8 compatibility code

## [3.22.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.21.0...3.22.0) - 2017-08-19
### Added
- Added option to inverse background for boolean fields in list and show actions

### Changed
- Run the Select2 code for autocomplete form type at onload event

### Fixed
- Fixed AbstractAdmin::getSubject on admins with parentFieldDescription
- Fixed deprecation when using hidden form type in model autocomplete
- Fixed the extra option being retrieved. The translation catalogue to be used is inside the label_catalogue option, not translation_domain.
- setting the column title
- Html tags do not appear in the meta title

## [3.21.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.20.1...3.21.0) - 2017-08-14
### Added
- Allow label icon on CRUD list table headers.
- Allow to disable label on CRUD list table heades.
- multidimensional arrays support in show array
- inline option in list array
- Added checkbox range selection with shift + click
- Added the persistence independent association templates
- Added Latvian translation of the bundle

### Changed
- Check for `ChildDefinition` instance when possible instead of `DefinitionDecorator` which got deprecated on Symfony 3.3

### Deprecated
- `ModelChoiceList` in favor of `ModelChoiceLoader`

### Fixed
- Fixes potentially wrong scheme in the sidebar urls by using relative urls
- Fixed choice field mask type javascript in the twig templates to works with immutable array form types
- `ServiceManipulator` now adds `public: true` to service declaration by default
- Fixed deprecation for Sf 3. support
- Sidebar menu elements are active when the current route is a child admin.
- Take admin annotation id into account

## [3.20.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.20.0...3.20.1) - 2017-06-27
### Fixed
- Fixed undefined `view_group` variable in show template
- Fixed compatibility with Symfony's IdentityTranslator when translator is disabled

## [3.20.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.19.0...3.20.0) - 2017-06-22
### Added
- Group and tabs translations

### Fixed
- Deprecation notices related to `addClassesToCompile`

## [3.19.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.18.2...3.19.0) - 2017-06-12
### Changed
- compatibility with immutable entities was improved

### Fixed
- Show icon for nav items when using `on_top` option
- use generated route instead of plain route in nav items
- it is no longer possible to get core bundle versions incompatible with sf3
- Missing admin-lte image file
- forms with a required autocomplete ajax field can be submitted again

## [3.18.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.18.1...3.18.2) - 2017-05-15
### Fixed
- Selected values issue with Select2 v4 on model autocomplete type

## [3.18.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.18.0...3.18.1) - 2017-05-12
### Fixed
- Fixed select2 width calculation when using select2 v4
- Compatibility with Select2 v4 on model autocomplete type

## [3.18.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.17.0...3.18.0) - 2017-05-09
### Added
- Added new configuration parameter named `empty_boxes` with 3 variable: show, hide, fade

### Fixed
- Undefined admin action error on `ModelAutocompleteFilter`
- added missing italian translations
- deprecations when using `sonata.admin.form.type.model_list`

## [3.17.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.16.0...3.17.0) - 2017-04-25
### Added
- Added editable support for association fields from type choice in `ListMapper`
- Added also new `class` option for field description
- Translation can now be disabled on specific form fields

### Changed
- Changed GroupMenuProvider::get to setDisplay(false) on menuItem if on_top used and no items could be displayed

### Fixed
- Fixed the bug that caused an error "The helper "dialog" is not defined." on Symfony3 with new `\Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper` when you run command "sonata:admin:generate-object-acl".
- Fixed issue on getExtendedType of MopaCompatibilityTypeFieldExtension and ChoiceTypeExtension because the method requires to return the fully-qualified class name (FQCN) since symfony version 2.8
- `ModelType` have choices as values by default now on SF 2.7+.
- Users without the `LIST` role can access the autocomplete items by configuring the `target_admin_access_action` option
- Non existent `isSuperior` key on `FormView` error

### Removed
- recently introduced checkbox-disabling feature, which was not stable enough

## [3.16.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.15.1...3.16.0) - 2017-03-31
### Added
- Added `onTop` parameter on `@Admin` annotation
- Added new `keep_open` option to keep menu group always open

### Fixed
- `field_description` comparison in `base_list_field.html.twig`

## [3.15.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.15.0...3.15.1) - 2017-03-28
### Added
- Added Brazilian Portuguese translation of `title_show`

### Changed
- change show picto on list view to use the same than in edit view

### Fixed
- do not double `FieldDescription::Name` and `property_path` in `AdminType`

## [3.15.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.14.0...3.15.0) - 2017-03-27
### Added
- Add polish translation of `title_show`
- Added the ability to leave the label of a show field empty by passing `label => false` to `ShowMapper::add()`

### Changed
- Make sure Moment.js translations work for every locale
- The `sonata/exporter` constraint has been bumped to `^1.7`

### Fixed
- Sanitize masked fields in `ChoiceFieldMaskType`
- Whitespaces are not taken into account when rendering blocks on `standard_layout`
- fixed boolean handling for `xEditableType`

## [3.14.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.13.0...3.14.0) - 2017-03-16
### Added
- Added `label` and `translation_domain` fallback for batch actions
- Config option to disable autoregistration of annotations with `JMSDiExtraBundle`
- Added missing titles to the CRUD show page.
- Added `attributes` parameter for `url` field type
- Added a missing variable placeholder to a translation unit.

### Fixed
- Missing title for nested admin
- Setting data form on update form field element by using `sonata_type_model`
- deprecation notices that could not be avoided in the `CoreController` class
- Fix #4292: don't overwrite `JMSDiExtraBundle` default configuration
- Fixed markup on list on Admin with subclasses
- x-editable choices are now correctly translated
- Default translation of Base Breadcrumb `Dashboard`
- Remove duplicated breadcrumb on admin list
- Breadcrumb without link are now displayed correctly
- Unified styles between admins with subclasses and admins without subclasses
- name of permission, use `VIEW` instead of `SHOW`
- Handling of boolean types in `HelperController`
- use `hasAccess` instead of `isGranted`
- better readability of exception message when too many admins are registered
- Improve Catalan and Spanish translations
- Fixed inconsistent translation placeholder quoting.
- Batch action breaks when coming from list view with filter using `doctrine_orm_model_autocomplete`
- Fixed non-existent variable `action` in `base_list_field.html.twig`

### Changed
- The export and list actions now integrate the sonata exporter bundle
- Changed `ActiveVoter` and `ChildrenVoter` to only work with menu items having the `SonataAdminBundle` extra set.
- Updated AdminLTE to 2.3.11
- Removed non FQCNs on form types on `AbstractAdmin`
- When checking the delete checkbox of an inline child form of `CollectionType` the related fields are now disabled to avoid preventing submission of the form when one of those inputs is required.
- Updated Luxembourgish translations
- Changed inconsistent translation unit name.
- Replaced `isGranted()` by `hasAccess()` or `checkAccess()`

### Deprecated
- Exporter class and service : use equivalents from `sonata-project/exporter` instead.
- auto registration of `JMSDiExtraBundle` annotations is now discouraged in favor of doing it manually

## [3.13.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.12.0...3.13.0) - 2017-02-03
### Added
- Added support for priority attribute in the Extension compiler pass

### Fixed
- Compatibility of ajax actions with Twig 2.0

## [3.12.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.11.0...3.12.0) - 2017-01-31
### Added
- Compatibility with Twig 2.0

### Changed
- `--services` default-value to null in `Sonata\AdminBundle\Command\GenerateAdminCommand`

### Deprecated
- `ModelToArrayTransformer::$choiceList` property
- `ModelToArrayTransformer::$choiceList::__construct()` three-argument-signature is deprecated

### Fixed
- "Silent display of undefined block" Twig deprecation
- Twig deprecation notice when using template inheritance to get a macro
- The `request` parameter is passed to custom batch actions.

## [3.11.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.10.3...3.11.0) - 2017-01-17
### Added
- Extract admin group and label translations
- JQuery event trigger to Admin.setup_list_modal()

### Changed
- Updated compiler pass to support parent definition when using abstract service for admin.

### Fixed
- Fixed behaviour of persistent parameters in list editables
- JMSDiExtraBundle is configured correctly to pick up Sonata annotations.

## [3.10.3](https://github.com/sonata-project/SonataAdminBundle/compare/3.10.2...3.10.3) - 2016-12-22
### Fixed
- A bug with the side menu arrow position
- Display correct name of group uses default translation domain

### Removed
- A Twig deprecation added in Twig 1.28.0
- A Sonata deprecation called by Sonata itself by adding a way to disable it when called internally

## [3.10.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.10.1...3.10.2) - 2016-12-15
### Fixed
- Filter form theme was used for create and edit forms too.

## [3.10.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.10.0...3.10.1) - 2016-12-13
### Fixed
- Fix compatibility with Symfony 3.2 form renderer.
- Fix permissions when setting role for the security handler
- Translation in twig templates uses the twig translation filter

## [3.10.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.9.0...3.10.0) - 2016-11-25
### Added
- Added new `roles` configuration field to configuration of menu item routes.
- Improved class support for filter factory
- Add a length option to truncate columns on list view

### Changed
- Update adminLTE dependency to 2.3.6
- Use block instead of macro to render show groups

### Fixed
- Fixed missing access check for menu route items.
- Fixed `trigger_error` calls - `E_USER_DEPRECATED` was concatenated to the sentence, not passed as argument
- Deep arrays can now be displayed without error, and recursively
- Fixed bug in revisions compare view

### Deprecated
- Deprecated `base_show_macro.html.twig`

## [3.9.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.8.0...3.9.0) - 2016-10-06
### Added
- Added `CRUDController::trans` method

### Changed
- Translation in twig templates uses the twig translation filter

### Deprecated
- Deprecated `AdminInterface::trans` method
- Deprecated `AbstractAdmin::$translator` property
- Deprecated `AbstractAdmin::trans` method
- Deprecated `AbstractAdmin::transChoice` method
- Deprecated `AbstractAdmin::getTranslator` method
- Deprecated `AbstractAdmin::setTranslator` method

### Fixed
- Fixed missing default `translationDomain`
- Fixed deprecated `BaseBlockService` usage

## [3.8.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.7.1...3.8.0) - 2016-09-20
### Added
- Added three new sub-blocks to standard_layouts javascript block

### Changed
- Moved the raw references of buttons templates from `Admin\AbstractAdmin` to configuration options

## [3.7.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.7.0...3.7.1) - 2016-09-13
### Fixed
- The `ALL` role needs to be checked separately, otherwise the `AuthorizationChecker` return `false` all the time.
- Added `var` keyword to explicitly define the "showMaskChoiceEl" variable

## [3.7.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.6.0...3.7.0) - 2016-09-07
### Added
- Added additional `_ALL` role check to `RolesecurityHandler`

### Changed
- Improve accessibility by adding `aria-hidden="true"`

## Fixed
- Set `choices_as_values` to `true` on choice type based to be compatible with Symfony 3
- Use class name when referencing `Form Type` to be compatible with Symfony 2.8+
- Remove `Sonata\CoreBundle\Exporter\Exporter` from classes to compile to cache to avoid deprecation warning

### Removed
- The admin no longer checks for the `translator` service before translating.

## [3.6.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.5.0...3.6.0) - 2016-09-01
### Added
- Added new methods to set default values for the list view

### Fixed
- Auto-detect type when adding `FormBuilderInterface` to `FormMapper`
- Type for `Filter` to be compatible with Symfony 2.8+
- Type for `Filter Operator` to be compatible with Symfony 2.8+

## [3.5.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.4.0...3.5.0) - 2016-08-29
### Added
- Export fields names are now translated
- Added PL translations
- Configuration to change the default route used to generate the link to the parent object inside a breadcrumb, when in a child admin
- Docs for this configuration
- Twig blocks to simplify the template override.
- Added test for `AdminBundle\Menu\Matcher\Voter\ChildrenVoter`
- Added class name support for `AbstractAdmin::configureDatagridFilters`
- Added `ShowMapper::removeGroup` method

### Changed
- Changed css `margin-left: -20px` of checkbox.
- Updated spanish batch confirmation message translation.
- Changed injection of `$container` to `$adminPool` in `Twig/GlobalVariables`
- use `RuntimeException` instead of non existing `RunTimeException`
- `AbstractAdmin::setSubject` in order to check that given `$subject` matches registered admin class entity.
- Added the action name to title_batch_confirmation translation.
- Added the object name to message_delete_confirmation translation.
- Added the action name to title_batch_confirmation translation.
- Move actions buttons display logic from templates to `AbstractAdmin::configureActionButtons`
- Moved translation of breadcrumbs to twig template
- Moved translation of batch action_label to twig template
- Move actions buttons display logic from templates to `AbstractAdmin::configureActionButtons`
- Widget tests should extend `AbstractWidgetTestCase`

### Deprecated
- The `$container` property in `Twig/GlobalVariables`

### Fixed
- The "batch" checkbox at the top of the list would not work when iCheck is disabled.
- Not working `read_only` option on Twig with Symfony 3
- Fixed PT-BR translations
- XSS Vulnerability in breadcrumbs
- Handle Symfony BC for Datagrid hidden types
- Fixed duplicate translation for list filters
- Fixed visibility of block `sonata_top_nav_menu` contents
- Fix how metadata information are retrieved when admin information are dumped
- Symfony 3 support in `AclMatrixType`
- Symfony 3 type support in `AclMatrixType`
- Fixed translation in browser titles breadcrumb
- Fixed translation of entities in breadcrumb
- Standardize the global form error

### Removed
- Internal test classes are now excluded from the autoloader
- Removed unnecessary security checks in `standard_layout.html.twig`

## [3.4.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.3.2...3.4.0) - 2016-07-05
### Added
- Support for select2 v4 (`select2.full.js` file is needed)

### Deprecated
- The `Sonata\AdminBundle\Form\Type\ModelTypeList` is deprecated for `ModelListType`

### Fixed
- Ignoring `translation_domain` in tab menu

### Removed
- Removed useless `ModelAutocompleteType::getParent` override
- Removed useless `read_only` option definition from `ModelAutocompleteType`

## [3.3.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.3.1...3.3.2) - 2016-06-23
### Fixed
 - Reverted [#3961](https://github.com/sonata-project/SonataAdminBundle/pull/3961) to fix a regression concerning child admins on edit route

## [3.3.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.3.0...3.3.1) - 2016-06-17
### Fixed
- Fixes broken extractor service
- Make CRUDController::editAction respect optional parameter
- Not aligned checkbox and radio on horizontal form

## [3.3.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.2.0...3.3.0) - 2016-06-13
### Changed
- The `Select` button is always visible and has a primary check style in `sonata_type_model_list` popups

### Deprecated
- The `$context` argument of `AdminInterface::createQuery` was deprecated

### Fixed
- Fix wrong view role check on `AbstractAdmin::getSearchResultLink`
- Eternal deprecation warning because of old class on compilation

## [3.2.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.1.0...3.2.0) - 2016-06-04
### Added
- Added new field type `email` on the list
- Added `AbstractAdmin::configureBatchActions` method
- Extract the breadcrumbs building part of the `AbstractAdmin` to a separate class
- Added `AbstractAdmin::getSearchResultLink` method
- Add the `AbstractAdmin::showMosaicButton` method
- Add the `show_mosaic_button` option to configuration

### Deprecated
- Overriding `AbstractAdmin::configureBatchActions` is now deprecated
- `AbstractAdmin::getBreadcrumbs` is deprecated in favor of `BreadcrumbsBuilder::getBreadcrumbs`
- `AbstractAdmin::buildBreadcrumbs` is deprecated
- `AbstractAdmin::$breadcrumbs` is deprecated

### Fixed
- Fix support for composite primary key in `AbstractAdmin::getSubject`
- Fixed wrong route in `list__select.html.twig`
- Fixed wrong method call in `list__select.html.twig`
- Fixed `Pool::getAdminsByGroup()` for the new admin groups values

## [3.1.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.0.0...3.1.0) - 2016-05-17
### Added
- Added `AbstractAdmin` class, replacing `Admin` one
- Added `BaseMapper::keys` method

### Changed
- Updated AdminLTE theme to version 2.3.3
- `RouteCollection::clearExcept` can now have a single string argument

### Deprecated
- Deprecated `BaseFieldDescription::camelize`
- Deprecated `AdminHelper::camelize`
- Deprecated `Admin` class
- Deprecated `AdminExtension` class
- Deprecated default template loading on exception mechanism

### Fixed
- Fix detection of path when using nested properties with underscores in `AdminHelper:getElementAccessPath` method
- Fixed bad rendering on datetime field with `single_text` widget for date and time
- Fixed rendering of empty form groups

## [3.0.0](https://github.com/sonata-project/SonataAdminBundle/compare/2.3.10...3.0.0) - 2016-05-08
### Added
- Add missing Route constructor parameters to `RouteCollection:add` method
- Add the `hasRoute` method to the AdminInterface
- Integration of KNPMenu for the admin menu. This integration is reset when the standard layout
`standard_layout.html.twig` is overriden. The KNPMenu is available in `sonata_menu.html.twig` template.
- Add `getFieldOption`, `setFieldOption` methods to the FilterInterface
- Add the `getFilterFieldDescription` method to the AdminInterface
- Add the `getMaxPageLinks`, `setMaxPageLinks` methods to the PagerInterface

### Changed
- Admin LTE 2.0 used. Assets files changed.
- Move `sonata_wrapper` block on `standard_layout.html.twig`
- CSS class `sonata-autocomplete-dropdown-item` is not automatically added to dropdown
autocomplete item in `sonata_type_model_autocomplete`, use option `dropdown_item_css_class`
to set the CSS class of dropdown item.
- Text from `Admin::toString` method is escaped for html output before adding in flash message to prevent possible XSS vulnerability.

### Removed
- Remove `btn-outline` from doctrine-orm-admin form actions buttons
