# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [4.33.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.32.0...4.33.0) - 2024-12-04
### Added
- [[#8219](https://github.com/sonata-project/SonataAdminBundle/pull/8219)] Support editable mode on `FieldDescriptionInterface::TYPE_ENUM` ([@onEXHovia](https://github.com/onEXHovia))

### Fixed
- [[#8218](https://github.com/sonata-project/SonataAdminBundle/pull/8218)] Style fix for the version comparison page ([@vityachis](https://github.com/vityachis))
- [[#8200](https://github.com/sonata-project/SonataAdminBundle/pull/8200)] Allow delete permission to be voted on a specific subject. ([@DenuxPlays](https://github.com/DenuxPlays))

## [4.32.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.31.0...4.32.0) - 2024-11-14
### Added
- [[#8213](https://github.com/sonata-project/SonataAdminBundle/pull/8213)] Support for select2 tags option ([@micbis](https://github.com/micbis))

### Changed
- [[#8212](https://github.com/sonata-project/SonataAdminBundle/pull/8212)] Enums implementing Symfony's `TranslatableInterface` are now correctly translated by the `FieldDescriptionInterface::TYPE_ENUM` field type ([@zyberspace](https://github.com/zyberspace))

## [4.31.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.30.2...4.31.0) - 2024-07-15
### Added
- [[#8192](https://github.com/sonata-project/SonataAdminBundle/pull/8192)]  Enable security information mapping for `RoleSecurityHandler` ([@core23](https://github.com/core23))

### Fixed
- [[#8187](https://github.com/sonata-project/SonataAdminBundle/pull/8187)] Symfony 7.1 deprecation about `Symfony\Component\HttpKernel\DependencyInjection\Extension` usage ([@AirBair](https://github.com/AirBair))

## [4.30.2](https://github.com/sonata-project/SonataAdminBundle/compare/4.30.1...4.30.2) - 2024-05-07
### Fixed
- [[#8177](https://github.com/sonata-project/SonataAdminBundle/pull/8177)] Batch consistency ([@pietaj](https://github.com/pietaj))

## [4.30.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.30.0...4.30.1) - 2024-04-10
### Fixed
- [[#8175](https://github.com/sonata-project/SonataAdminBundle/pull/8175)] `Filter::getLabelTranslationParameters` implementation ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.30.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.29.3...4.30.0) - 2024-04-10
### Added
- [[#8170](https://github.com/sonata-project/SonataAdminBundle/pull/8170)] SearchHandlerInterface ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#8172](https://github.com/sonata-project/SonataAdminBundle/pull/8172)] Not implementing `FilterInterface::showFilter` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#8172](https://github.com/sonata-project/SonataAdminBundle/pull/8172)] Not implementing `FilterInterface::getLabelTranslationParameters` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#8172](https://github.com/sonata-project/SonataAdminBundle/pull/8172)] Not implementing `FilterInterface::withAdvancedFilter` ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#8173](https://github.com/sonata-project/SonataAdminBundle/pull/8173)] Active state in side menu for nested child admins ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#8172](https://github.com/sonata-project/SonataAdminBundle/pull/8172)] `label_translation_parameters` option usage ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.29.3](https://github.com/sonata-project/SonataAdminBundle/compare/4.29.2...4.29.3) - 2024-01-22
### Fixed
- [[#8149](https://github.com/sonata-project/SonataAdminBundle/pull/8149)] Fix possible null error in search ([@core23](https://github.com/core23))

## [4.29.2](https://github.com/sonata-project/SonataAdminBundle/compare/4.29.1...4.29.2) - 2023-12-24
### Changed
- [[#8141](https://github.com/sonata-project/SonataAdminBundle/pull/8141)] CRUDController::handleModelManagerException can now return a custom error message string for display in the flashbag instead of Sonata ones. Return type is removed from the signature ([@wadjei](https://github.com/wadjei))

### Fixed
- [[#8139](https://github.com/sonata-project/SonataAdminBundle/pull/8139)] Deprecation about `ArgumentValueResolverInterface` usage ([@Hanmac](https://github.com/Hanmac))

## [4.29.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.29.0...4.29.1) - 2023-12-12
### Fixed
- [[#8136](https://github.com/sonata-project/SonataAdminBundle/pull/8136)] Fix clashing global twig variables (admin, base_template) if already set ([@core23](https://github.com/core23))

## [4.29.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.28.1...4.29.0) - 2023-11-25
### Added
- [[#8127](https://github.com/sonata-project/SonataAdminBundle/pull/8127)] Symfony 7 support ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.28.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.28.0...4.28.1) - 2023-11-20
### Fixed
- [[#8125](https://github.com/sonata-project/SonataAdminBundle/pull/8125)] Needing to submit form twice when using CRUDController::render() ([@AntoineRoue](https://github.com/AntoineRoue))

## [4.28.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.27.1...4.28.0) - 2023-10-19
### Changed
- [[#8116](https://github.com/sonata-project/SonataAdminBundle/pull/8116)] Automatically detect `base_template` and `admin` twig variable ([@core23](https://github.com/core23))

### Deprecated
- [[#8116](https://github.com/sonata-project/SonataAdminBundle/pull/8116)] Deprecate `CRUDController::renderWithExtraParams` method ([@core23](https://github.com/core23))
- [[#8116](https://github.com/sonata-project/SonataAdminBundle/pull/8116)] Deprecate `CRUDController::addRenderExtraParams` method ([@core23](https://github.com/core23))
- [[#8116](https://github.com/sonata-project/SonataAdminBundle/pull/8116)] Deprecate `CRUDController::getBaseTemplate` method ([@core23](https://github.com/core23))

### Fixed
- [[#8118](https://github.com/sonata-project/SonataAdminBundle/pull/8118)] Allow AdminExtractor to be used for abstract admins ([@core23](https://github.com/core23))
- [[#8115](https://github.com/sonata-project/SonataAdminBundle/pull/8115)] `GroupMapper::removeTab` and `GroupMapper::removeGroup` ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.27.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.27.0...4.27.1) - 2023-09-21
### Added
- [[#8105](https://github.com/sonata-project/SonataAdminBundle/pull/8105)] Allow to disable `icheck` and to be able to use still choice field mask with the expanded option ([@goetas](https://github.com/goetas))

### Fixed
- [[#8100](https://github.com/sonata-project/SonataAdminBundle/pull/8100)] Logic to handle show / hide column of table html element ([@nhung-le](https://github.com/nhung-le))
- [[#8108](https://github.com/sonata-project/SonataAdminBundle/pull/8108)] Add missing type button on ModelList form widget ([@tdumalin](https://github.com/tdumalin))
- [[#8099](https://github.com/sonata-project/SonataAdminBundle/pull/8099)] `label_catalogue` configuration node deprecation message ([@7ochem](https://github.com/7ochem))

## [4.27.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.26.1...4.27.0) - 2023-09-07
### Added
- [[#8083](https://github.com/sonata-project/SonataAdminBundle/pull/8083)] Autocomplete attr support for select2 ([@pietaj](https://github.com/pietaj))

## [4.26.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.26.0...4.26.1) - 2023-09-02
### Fixed
- [[#8091](https://github.com/sonata-project/SonataAdminBundle/pull/8091)] Fix display of enums for nullable property ([@bedfir](https://github.com/bedfir))

## [4.26.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.25.0...4.26.0) - 2023-07-29
### Changed
- [[#8079](https://github.com/sonata-project/SonataAdminBundle/pull/8079)] Update jQuery to `^3.7.0` ([@tonyaxo](https://github.com/tonyaxo))

### Fixed
- [[#8082](https://github.com/sonata-project/SonataAdminBundle/pull/8082)] Installing SonataAdminBundle without any persistence does not throw missing abstract argument definition ([@jordisala1991](https://github.com/jordisala1991))

## [4.25.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.24.0...4.25.0) - 2023-06-03
### Added
- [[#8072](https://github.com/sonata-project/SonataAdminBundle/pull/8072)] Support for SonataBlockBundle 5.0 ([@jordisala1991](https://github.com/jordisala1991))
- [[#8070](https://github.com/sonata-project/SonataAdminBundle/pull/8070)] "enum_translation_domain" and "use_value" options for fields of type `FieldDescriptionInterface::TYPE_ENUM`. ([@phansys](https://github.com/phansys))

## [4.24.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.23.0...4.24.0) - 2023-05-13
### Added
- [[#8064](https://github.com/sonata-project/SonataAdminBundle/pull/8064)] Support for `sonata-project/form-extensions` 2.0 ([@jordisala1991](https://github.com/jordisala1991))
- [[#7838](https://github.com/sonata-project/SonataAdminBundle/pull/7838)] Stimulus dependency and initialization inside main `app.js` ([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#8061](https://github.com/sonata-project/SonataAdminBundle/pull/8061)] ExplainAdminCommand no longer complains about missing subject on admin ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#8055](https://github.com/sonata-project/SonataAdminBundle/pull/8055)] Child admins not related directly to a parent now handle show, edit and delete actions correctly. ([@maMykola](https://github.com/maMykola))

## [4.23.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.22.6...4.23.0) - 2023-04-24
### Fixed
- [[#8041](https://github.com/sonata-project/SonataAdminBundle/pull/8041)] Top nav bar menu for user is shown if there is a user logged in ([@jordisala1991](https://github.com/jordisala1991))
- [[#8041](https://github.com/sonata-project/SonataAdminBundle/pull/8041)] Top nav bar menu for creating admin entities is shown even is there is no user logged in ([@jordisala1991](https://github.com/jordisala1991))

### Removed
- [[#8044](https://github.com/sonata-project/SonataAdminBundle/pull/8044)] Support for Symfony 4 ([@jordisala1991](https://github.com/jordisala1991))
- [[#8044](https://github.com/sonata-project/SonataAdminBundle/pull/8044)] Support for Twig 2 ([@jordisala1991](https://github.com/jordisala1991))

## [4.22.6](https://github.com/sonata-project/SonataAdminBundle/compare/4.22.5...4.22.6) - 2023-04-12
### Fixed
- [[#8039](https://github.com/sonata-project/SonataAdminBundle/pull/8039)] Fix display of enums for nullable property ([@AirBair](https://github.com/AirBair))

## [4.22.5](https://github.com/sonata-project/SonataAdminBundle/compare/4.22.4...4.22.5) - 2023-03-30
### Changed
- [[#8029](https://github.com/sonata-project/SonataAdminBundle/pull/8029)] Use `symfony/string` instead of `doctrine/inflector` to convert words from "snake_case" to "camelCase" ([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#8026](https://github.com/sonata-project/SonataAdminBundle/pull/8026)] Fix errors not being displayed in modal ([@antoinemineau](https://github.com/antoinemineau))
- [[#8025](https://github.com/sonata-project/SonataAdminBundle/pull/8025)] Message when the admin class declared on the container does not exist. ([@jordisala1991](https://github.com/jordisala1991))

### Removed
- [[#8016](https://github.com/sonata-project/SonataAdminBundle/pull/8016)] Support for PHP 7.4 ([@SonataCI](https://github.com/SonataCI))
- [[#8016](https://github.com/sonata-project/SonataAdminBundle/pull/8016)] Support for Symfony 6.0 and 6.1 ([@SonataCI](https://github.com/SonataCI))

## [4.22.4](https://github.com/sonata-project/SonataAdminBundle/compare/4.22.3...4.22.4) - 2023-01-26
### Fixed
- [[#8009](https://github.com/sonata-project/SonataAdminBundle/pull/8009)] Fix response_item_callback option for `ModelAutocompleteType` form field. ([@aleksvaganoff](https://github.com/aleksvaganoff))
- [[#7891](https://github.com/sonata-project/SonataAdminBundle/pull/7891)] Fix Sortable drag and drop for `ModelType` form field. ([@jorrit](https://github.com/jorrit))

## [4.22.3](https://github.com/sonata-project/SonataAdminBundle/compare/4.22.2...4.22.3) - 2023-01-11
### Fixed
- [[#8003](https://github.com/sonata-project/SonataAdminBundle/pull/8003)] Value resolver deprecations using Symfony 6.2 ([@franmomu](https://github.com/franmomu))

## [4.22.2](https://github.com/sonata-project/SonataAdminBundle/compare/4.22.1...4.22.2) - 2023-01-05
### Fixed
- [[#8001](https://github.com/sonata-project/SonataAdminBundle/pull/8001)] Admin extension declaration with priority ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.22.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.22.0...4.22.1) - 2023-01-05
### Fixed
- [[#7998](https://github.com/sonata-project/SonataAdminBundle/pull/7998)] ExtensionCompilerPass usage with parameters class string. ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.22.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.21.1...4.22.0) - 2023-01-03
### Added
- [[#7994](https://github.com/sonata-project/SonataAdminBundle/pull/7994)] Added usage of ExporterInterface instead of Exporter class. ([@pkameisha](https://github.com/pkameisha))
- [[#7964](https://github.com/sonata-project/SonataAdminBundle/pull/7964)] Ability to configure admin extension in the `sonata_admin.extensions` config with the admin class rather than the model class thanks to `admin_implements`, `admin_instanceof`, `admin_extends` and `admin_use` options. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7964](https://github.com/sonata-project/SonataAdminBundle/pull/7964)] `excludes`, `extends`, `implements`, `instanceof`, `uses`, `admin_implements`, `admin_instanceof`, `admin_extends` and `admin_use` options are now available directly when tagging an admin extension. ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.21.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.21.0...4.21.1) - 2022-12-08
### Fixed
- [[#7985](https://github.com/sonata-project/SonataAdminBundle/pull/7985)] Fixed an issue with integer indexed form children within `FormBuilderIterator` ([@dmaicher](https://github.com/dmaicher))
- [[#7981](https://github.com/sonata-project/SonataAdminBundle/pull/7981)] `data-sonata-select2-maximumSelectionLength` attribute ([@sterrien](https://github.com/sterrien))

## [4.21.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.20.0...4.21.0) - 2022-11-09
### Added
- [[#7960](https://github.com/sonata-project/SonataAdminBundle/pull/7960)] Added support for `doctrine/collections` 2 ([@franmomu](https://github.com/franmomu))

### Changed
- [[#7962](https://github.com/sonata-project/SonataAdminBundle/pull/7962)] Made `AbstractAdmin::setSubject()` use `getModelClass()` instead of `getClass()` to check is the subject is allowed ([@7ochem](https://github.com/7ochem))

### Fixed
- [[#7963](https://github.com/sonata-project/SonataAdminBundle/pull/7963)] Fix `appendFormFieldElement` on nested collection named the same as one of the parent fields ([@fgueguen](https://github.com/fgueguen))
- [[#7961](https://github.com/sonata-project/SonataAdminBundle/pull/7961)] Fixes ChoiceFieldMaskType behavior inside CollectionType ([@Darkabso](https://github.com/Darkabso))
- [[#7952](https://github.com/sonata-project/SonataAdminBundle/pull/7952)] Fix an issue that an exception will be thrown by `Pool::getAdminByClass()` when the admin code is different from its service id. ([@tamcy](https://github.com/tamcy))
- [[#7952](https://github.com/sonata-project/SonataAdminBundle/pull/7952)] Fix a typo in the deprecation message which is triggered when multiple `sonata.admin` tags are found in the same service definition. ([@tamcy](https://github.com/tamcy))

## [4.20.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.19.0...4.20.0) - 2022-10-20
### Added
- [[#7937](https://github.com/sonata-project/SonataAdminBundle/pull/7937)] Allow manually reorder tagged admin services using the `priority` attribute. ([@tamcy](https://github.com/tamcy))

### Deprecated
- [[#7941](https://github.com/sonata-project/SonataAdminBundle/pull/7941)] Applying the service tag `sonata.admin` to a service more than once is deprecated. This is not meant to be supported and will not work as intended, as only one of the tag attributes will be applied to the service. ([@tamcy](https://github.com/tamcy))

### Fixed
- [[#7949](https://github.com/sonata-project/SonataAdminBundle/pull/7949)] Fix validation errors while editting many to one|many relations in a modal. ([@jordisala1991](https://github.com/jordisala1991))
- [[#7948](https://github.com/sonata-project/SonataAdminBundle/pull/7948)] Violations error handling ([@ggabrovski](https://github.com/ggabrovski))
- [[#7940](https://github.com/sonata-project/SonataAdminBundle/pull/7940)] Fix an issue that exception is thrown when the admin code is different from its service id. ([@tamcy](https://github.com/tamcy))

## [4.19.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.18.0...4.19.0) - 2022-09-27
### Changed
- [[#7922](https://github.com/sonata-project/SonataAdminBundle/pull/7922)] [BC break] Change json error output for ajax calls to create or edit admin endpoints. ([@jordisala1991](https://github.com/jordisala1991))

### Deprecated
- [[#7927](https://github.com/sonata-project/SonataAdminBundle/pull/7927)] BCLabelTranslatorStrategy ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7930](https://github.com/sonata-project/SonataAdminBundle/pull/7930)] `ModelManager::getIdentifierValues` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7930](https://github.com/sonata-project/SonataAdminBundle/pull/7930)] `ModelManager::getIdentifierFieldNames` ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7930](https://github.com/sonata-project/SonataAdminBundle/pull/7930)] ModelToIdPropertyTransformer now supports composite identifiers ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7926](https://github.com/sonata-project/SonataAdminBundle/pull/7926)] Fix missing default translation for actions label on list view. ([@jordisala1991](https://github.com/jordisala1991))

## [4.18.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.17.0...4.18.0) - 2022-08-21
### Added
- [[#7913](https://github.com/sonata-project/SonataAdminBundle/pull/7913)] Security attributes can be string or symfony expression language expressions ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7909](https://github.com/sonata-project/SonataAdminBundle/pull/7909)] Added generics to the ProxyQueryInterface ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.17.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.16.0...4.17.0) - 2022-08-15
### Added
- [[#7906](https://github.com/sonata-project/SonataAdminBundle/pull/7906)] Added ProxyManagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#7906](https://github.com/sonata-project/SonataAdminBundle/pull/7906)] Relying on doctrine `ClassUtils::getClass` to resolve proxy classes. The ModelManager have to implements the ProxyManagerInterface instead. ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7899](https://github.com/sonata-project/SonataAdminBundle/pull/7899)] AdminMaker service option ([@joerndyherrn](https://github.com/joerndyherrn))

## [4.16.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.15.0...4.16.0) - 2022-08-02
### Added
- [[#7894](https://github.com/sonata-project/SonataAdminBundle/pull/7894)] Support for twig-extensions 2 ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7887](https://github.com/sonata-project/SonataAdminBundle/pull/7887)] Support for `sonata-project/doctrine-extensions` ^2 ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7886](https://github.com/sonata-project/SonataAdminBundle/pull/7886)] Support for `sonata-project/exporter ^3` ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#7885](https://github.com/sonata-project/SonataAdminBundle/pull/7885)] Sidebar menu is now non-shared. (new instance is created everytime its requested) ([@Warxcell](https://github.com/Warxcell))

### Fixed
- [[#7893](https://github.com/sonata-project/SonataAdminBundle/pull/7893)] Allow ChildAdmin for Unidirectional References ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7890](https://github.com/sonata-project/SonataAdminBundle/pull/7890)] Do not try to check access for created object ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.15.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.14.0...4.15.0) - 2022-07-23
### Added
- [[#7867](https://github.com/sonata-project/SonataAdminBundle/pull/7867)] Added `AbstractAdmin::generateBaseRoutePattern()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7867](https://github.com/sonata-project/SonataAdminBundle/pull/7867)] Added `AbstractAdmin::generateBaseRouteName()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#7878](https://github.com/sonata-project/SonataAdminBundle/pull/7878)] Bump moment to 2.29.4 ([@dependabot[bot]](https://github.com/apps/dependabot))

### Deprecated
- [[#7867](https://github.com/sonata-project/SonataAdminBundle/pull/7867)] Added `AbstractAdmin::$baseRoutePattern` property ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7867](https://github.com/sonata-project/SonataAdminBundle/pull/7867)] Added `AbstractAdmin::$baseRouteName` property ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.14.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.13.1...4.14.0) - 2022-07-12
### Added
- [[#7841](https://github.com/sonata-project/SonataAdminBundle/pull/7841)] Support for a FilterInterface::getFormOptions method. ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#7841](https://github.com/sonata-project/SonataAdminBundle/pull/7841)] FilterInterface::getRenderSettigns ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7841](https://github.com/sonata-project/SonataAdminBundle/pull/7841)] All the `Sonata\AdminBundle\Form\Type\Filter\*Type` except the FilterDataType. ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7868](https://github.com/sonata-project/SonataAdminBundle/pull/7868)] Batch action ProxyQueryInterface parameter don't have to be named $query anymore. ([@mpoiriert](https://github.com/mpoiriert))

## [4.13.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.13.0...4.13.1) - 2022-07-05
### Fixed
- [[#7861](https://github.com/sonata-project/SonataAdminBundle/pull/7861)] AdminInterface phpdoc ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.13.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.12.0...4.13.0) - 2022-06-25
### Added
- [[#7819](https://github.com/sonata-project/SonataAdminBundle/pull/7819)] Added support to batch action via another controller with 'controller' configuration ([@mpoiriert](https://github.com/mpoiriert))

### Deprecated
- [[#7819](https://github.com/sonata-project/SonataAdminBundle/pull/7819)] BatchAction{actionName}IsRelevant will be remove in version 5.0. Move logic to your action. ([@mpoiriert](https://github.com/mpoiriert))
- [[#7835](https://github.com/sonata-project/SonataAdminBundle/pull/7835)] FormMapper::create() ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7851](https://github.com/sonata-project/SonataAdminBundle/pull/7851)] Model Auto Complete failing in create flow of admin with subclasses ([@mpoiriert](https://github.com/mpoiriert))
- [[#7847](https://github.com/sonata-project/SonataAdminBundle/pull/7847)] AdminValueResolver does support generic AdminInterface type. ([@mpoiriert](https://github.com/mpoiriert))
- [[#7845](https://github.com/sonata-project/SonataAdminBundle/pull/7845)] Fix the subclass query parameter conflict in the sonata.admin.action.get_short_object_description controller. ([@mpoiriert](https://github.com/mpoiriert))
- [[#7836](https://github.com/sonata-project/SonataAdminBundle/pull/7836)] Command deprecations using Symfony 6.1 ([@franmomu](https://github.com/franmomu))

### Removed
- [[#7837](https://github.com/sonata-project/SonataAdminBundle/pull/7837)] Support of Symfony 5.3 ([@franmomu](https://github.com/franmomu))

## [4.12.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.11.1...4.12.0) - 2022-05-21
### Added
- [[#7823](https://github.com/sonata-project/SonataAdminBundle/pull/7823)] Support for abstract class subject in `RetrieveAutocompleteItemsAction`. ([@Geekimo](https://github.com/Geekimo))

### Changed
- [[#7824](https://github.com/sonata-project/SonataAdminBundle/pull/7824)] Update jQuery to `^3.6` ([@jordisala1991](https://github.com/jordisala1991))
- [[#7809](https://github.com/sonata-project/SonataAdminBundle/pull/7809)] Use datetime picker library from `sonata-project/form-extensions`. ([@jordisala1991](https://github.com/jordisala1991))

### Removed
- [[#7814](https://github.com/sonata-project/SonataAdminBundle/pull/7814)] Removed `doctrine/persistence` dependency ([@franmomu](https://github.com/franmomu))

## [4.11.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.11.0...4.11.1) - 2022-04-29
### Fixed
- [[#7803](https://github.com/sonata-project/SonataAdminBundle/pull/7803)] Fix vulnerabilities on several frontend dependencies by upgrading them: `async`, `minimist`, `ansi-regex` and `moment`, ([@jordisala1991](https://github.com/jordisala1991))

## [4.11.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.10.1...4.11.0) - 2022-04-27
### Changed
- [[#7792](https://github.com/sonata-project/SonataAdminBundle/pull/7792)] In preparation for SonataBlockBundle 5.0 we are not rendering block responses as private anymore ([@dmaicher](https://github.com/dmaicher))

### Fixed
- [[#7785](https://github.com/sonata-project/SonataAdminBundle/pull/7785)] Correctly pass the class to the ModelHiddentType when using child admin without parentAssociationMapping. ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.10.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.10.0...4.10.1) - 2022-03-29
### Fixed
- [[#7781](https://github.com/sonata-project/SonataAdminBundle/pull/7781)] Correctly set the parent object in AdminType with CollectionType passed by reference ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7779](https://github.com/sonata-project/SonataAdminBundle/pull/7779)] ProxyQueryInterface::setSortBy phpdoc ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7776](https://github.com/sonata-project/SonataAdminBundle/pull/7776)] Fixed display of list items when there are no results and using an entity with inheritance ([@jordisala1991](https://github.com/jordisala1991))

## [4.10.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.9.0...4.10.0) - 2022-03-21
### Changed
- [[#7761](https://github.com/sonata-project/SonataAdminBundle/pull/7761)] CrudController::handleModelManagerThrowable can now return a custom error message to display in the flashbag instead of the generic one from Sonata. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7766](https://github.com/sonata-project/SonataAdminBundle/pull/7766)] Reduced number of ModelManager::find calls ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7769](https://github.com/sonata-project/SonataAdminBundle/pull/7769)] Improve detection of Symfony ACL bundle installation ([@Buratinas](https://github.com/Buratinas))

## [4.9.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.8.1...4.9.0) - 2022-02-27
### Added
- [[#7730](https://github.com/sonata-project/SonataAdminBundle/pull/7730)] `TemplateType` to display custom data in the edit/create view. ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#7749](https://github.com/sonata-project/SonataAdminBundle/pull/7749)] Improve `make:sonata:admin` command, now it's passing modelClass and Controller in services tag. ([@eerison](https://github.com/eerison))
- [[#7725](https://github.com/sonata-project/SonataAdminBundle/pull/7725)] The default list mode is now the first one of the `getListModes` method. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7744](https://github.com/sonata-project/SonataAdminBundle/pull/7744)] Some typehint from SourceIteratorInterface to \Iterator to allow using this library without deprecation ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7729](https://github.com/sonata-project/SonataAdminBundle/pull/7729)] Use dynamic action variable to template ([@core23](https://github.com/core23))
- [[#7714](https://github.com/sonata-project/SonataAdminBundle/pull/7714)] Change if targetClass is instance of other className ([@willemverspyck](https://github.com/willemverspyck))

### Deprecated
- [[#7748](https://github.com/sonata-project/SonataAdminBundle/pull/7748)] `sonata_admin.options.default_label_catalogue` in favor of `sonata_admin.options.default_translation_domain` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7748](https://github.com/sonata-project/SonataAdminBundle/pull/7748)] `label_catalogue` attribute on `sonata_admin` tag in favor of `translation_domain` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7748](https://github.com/sonata-project/SonataAdminBundle/pull/7748)] `catalogue` option on fieldDescription in favor of `choice_translation_domain` for TYPE_CHOICE or `value_translation_domain` for TYPE_TRANS. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7748](https://github.com/sonata-project/SonataAdminBundle/pull/7748)] `label_catalogue` on group configuration in favor of `translation_domain` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7748](https://github.com/sonata-project/SonataAdminBundle/pull/7748)] `btn_catalogue` option on form type in favor of `btn_translation_domain` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7725](https://github.com/sonata-project/SonataAdminBundle/pull/7725)] Defining a list mode with a "class" setting, use the "icon" setting instead. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7743](https://github.com/sonata-project/SonataAdminBundle/pull/7743)] Passing '' for the `admin` key in an item of the `sonata_admin.dashboard.groups` config ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7743](https://github.com/sonata-project/SonataAdminBundle/pull/7743)] Passing a `route` or a `label` when an `admin` is passed in an item of the `sonata_admin.dashboard.groups` config ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7743](https://github.com/sonata-project/SonataAdminBundle/pull/7743)] The `item_adds` key when configuring the `sonata_admin.dashboard.groups` config ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7729](https://github.com/sonata-project/SonataAdminBundle/pull/7729)] `tree.html.twig` template ([@core23](https://github.com/core23))

### Fixed
- [[#7748](https://github.com/sonata-project/SonataAdminBundle/pull/7748)] `translation_domain` attribute on `sonata_admin` is used to set the admin translation domain. (Unlike the `label_catalogue` attribute) ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7757](https://github.com/sonata-project/SonataAdminBundle/pull/7757)] Duplicate display of the help option for ModelListType ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.8.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.8.0...4.8.1) - 2022-01-31
### Fixed
- [[#7716](https://github.com/sonata-project/SonataAdminBundle/pull/7716)] Configure the admin with the `sonata_admin` tag before any custom `call` provided in the service configuration. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7681](https://github.com/sonata-project/SonataAdminBundle/pull/7681)] Disabling `global_search` for admin with non-default code. ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.8.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.7.0...4.8.0) - 2022-01-25
### Added
- [[#7684](https://github.com/sonata-project/SonataAdminBundle/pull/7684)] `code`, `model_class` and `controller` attribute to the `sonata.admin` service tag ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7708](https://github.com/sonata-project/SonataAdminBundle/pull/7708)] Added support to display backed enums in lists and show templates ([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#7684](https://github.com/sonata-project/SonataAdminBundle/pull/7684)] Passing the code, the model class and the controller name as first, second and third argument of the Admin constructor. Use the `sonata.admin` attributes instead. ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.7.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.6.1...4.7.0) - 2022-01-05
### Added
- [[#7685](https://github.com/sonata-project/SonataAdminBundle/pull/7685)] Support for `symfony/translation-contracts` 3.x ([@jordisala1991](https://github.com/jordisala1991))
- [[#7653](https://github.com/sonata-project/SonataAdminBundle/pull/7653)] "export_format_xlsx" translations ([@willemverspyck](https://github.com/willemverspyck))
- [[#7658](https://github.com/sonata-project/SonataAdminBundle/pull/7658)] `AdminInterface::showInDashboard()` method ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#7662](https://github.com/sonata-project/SonataAdminBundle/pull/7662)] Twig extensions are now lazy ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#7658](https://github.com/sonata-project/SonataAdminBundle/pull/7658)] `AdminInterface::showIn()` method ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7674](https://github.com/sonata-project/SonataAdminBundle/pull/7674)] Multiple choice field with predefined default values ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7673](https://github.com/sonata-project/SonataAdminBundle/pull/7673)] Order of provided IDs at `ModelsToArrayTransformer::reverseTransform()` ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.6.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.6.0...4.6.1) - 2021-12-23
### Fixed
- [[#7669](https://github.com/sonata-project/SonataAdminBundle/pull/7669)] Model_autocomplete_type template ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.6.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.5.1...4.6.0) - 2021-12-23
### Added
- [[#7661](https://github.com/sonata-project/SonataAdminBundle/pull/7661)] ModelAutocompleteType delay option ([@Gasjki](https://github.com/Gasjki))

### Deprecated
- [[#7661](https://github.com/sonata-project/SonataAdminBundle/pull/7661)] ModelAutocompleteType quietMillis option ([@Gasjki](https://github.com/Gasjki))
- [[#7659](https://github.com/sonata-project/SonataAdminBundle/pull/7659)] Passing an array of role to `AdminInterface::isGranted()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7659](https://github.com/sonata-project/SonataAdminBundle/pull/7659)] Passing an array of role to `AbstractAdmin::isGranted()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7659](https://github.com/sonata-project/SonataAdminBundle/pull/7659)] Passing an array of role to `SecurityHandlerInterface::isGranted()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7659](https://github.com/sonata-project/SonataAdminBundle/pull/7659)] Passing an array of role to `RoleSecurityHandler::isGranted()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7659](https://github.com/sonata-project/SonataAdminBundle/pull/7659)] Passing an array of role to `AclSecurityHandler::isGranted()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7666](https://github.com/sonata-project/SonataAdminBundle/pull/7666)] DefaultRouterGenerator route name generation when full name is given ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7661](https://github.com/sonata-project/SonataAdminBundle/pull/7661)] ModelAutocompleteType quietMillis option ([@Gasjki](https://github.com/Gasjki))

## [4.5.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.5.0...4.5.1) - 2021-12-16
### Fixed
- [[#7647](https://github.com/sonata-project/SonataAdminBundle/pull/7647)] DefaultRouteGenerator Routes generation for nested admins ([@Devristo](https://github.com/Devristo))
- [[#7636](https://github.com/sonata-project/SonataAdminBundle/pull/7636)] Remove support for translation-contracts until it can be installed without relying on dev dependencies ([@jordisala1991](https://github.com/jordisala1991))

## [4.5.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.4.0...4.5.0) - 2021-12-03
### Added
- [[#7626](https://github.com/sonata-project/SonataAdminBundle/pull/7626)] Support for a nullable username in a Revision. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7624](https://github.com/sonata-project/SonataAdminBundle/pull/7624)] Support for ArrayAccess when accessing value in the appendFormFieldElement method. ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7622](https://github.com/sonata-project/SonataAdminBundle/pull/7622)] Fixed PHP 8.1 deprecation notice for nl2br usage in base_show_field.html.twig. ([@javer](https://github.com/javer))

## [4.4.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.3.2...4.4.0) - 2021-11-25
### Added
- [[#7617](https://github.com/sonata-project/SonataAdminBundle/pull/7617)] The ability to not add a `select` column when accessing to the List with AJAX. ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#7617](https://github.com/sonata-project/SonataAdminBundle/pull/7617)] Template `src/Resources/views/CRUD/base_list_flat_field.html.twig` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7617](https://github.com/sonata-project/SonataAdminBundle/pull/7617)] Template `src/Resources/views/CRUD/base_list_flat_inner_row.html.twig` ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.3.2](https://github.com/sonata-project/SonataAdminBundle/compare/4.3.1...4.3.2) - 2021-11-22
### Fixed
- [[#7608](https://github.com/sonata-project/SonataAdminBundle/pull/7608)] Accessing a non existing template variable in list_footer block when the list_table block is overriden ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.3.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.3.0...4.3.1) - 2021-11-22
### Fixed
- [[#7609](https://github.com/sonata-project/SonataAdminBundle/pull/7609)] Model Autocomplete template ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.3.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.2.2...4.3.0) - 2021-11-19
### Added
- [[#7579](https://github.com/sonata-project/SonataAdminBundle/pull/7579)] Added `AbstractAdminExtension::preBatchAction()` and `AdminExtensionInterface::preBatchAction()` (as annotation for BC) to have an extension point on batch actions. ([@7ochem](https://github.com/7ochem))
- [[#7579](https://github.com/sonata-project/SonataAdminBundle/pull/7579)] Added `AdminEventExtension::preBatchAction()` that dispatches a `sonata.admin.event.batch_action.pre_batch_action` event with a BatchActionEvent object containing the data ([@7ochem](https://github.com/7ochem))
- [[#7579](https://github.com/sonata-project/SonataAdminBundle/pull/7579)] Added BatchActionEvent class as a transport for (newly introduced) batch action events ([@7ochem](https://github.com/7ochem))
- [[#7604](https://github.com/sonata-project/SonataAdminBundle/pull/7604)] `ModelManagerThrowable` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7604](https://github.com/sonata-project/SonataAdminBundle/pull/7604)] `CRUDController::handleModelManagerThrowable()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#7580](https://github.com/sonata-project/SonataAdminBundle/pull/7580)] `FormTypeFieldExtension::getValueFromFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7604](https://github.com/sonata-project/SonataAdminBundle/pull/7604)] `CRUDController::handleModelManagerException()` ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.2.2](https://github.com/sonata-project/SonataAdminBundle/compare/4.2.1...4.2.2) - 2021-11-08
### Fixed
- [[#7599](https://github.com/sonata-project/SonataAdminBundle/pull/7599)] French translations ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7598](https://github.com/sonata-project/SonataAdminBundle/pull/7598)] Filter interface phpdoc ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.2.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.2.0...4.2.1) - 2021-10-31
### Fixed
- [[#7576](https://github.com/sonata-project/SonataAdminBundle/pull/7576)] Missing `merge` call in twig ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.2.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.1.0...4.2.0) - 2021-10-30
### Added
- [[#7561](https://github.com/sonata-project/SonataAdminBundle/pull/7561)] Make `AuditReaderInterface` generic ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7563](https://github.com/sonata-project/SonataAdminBundle/pull/7563)] `AbstractAdmin::removeExtension()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7564](https://github.com/sonata-project/SonataAdminBundle/pull/7564)] The ability to not add a `select` column when accessing to the list with AJAX ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7568](https://github.com/sonata-project/SonataAdminBundle/pull/7568)] Added support for `psr/log` 3.0. ([@jordisala1991](https://github.com/jordisala1991))
- [[#7568](https://github.com/sonata-project/SonataAdminBundle/pull/7568)] Added support for `psr/container` 2.0. ([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#7529](https://github.com/sonata-project/SonataAdminBundle/pull/7529)] Setting a default order if it is not set in the field description ([@franmomu](https://github.com/franmomu))
- [[#7530](https://github.com/sonata-project/SonataAdminBundle/pull/7530)] Using multiple properties with `ModelAutocompleteType` with different order ([@franmomu](https://github.com/franmomu))

## [4.1.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.0.1...4.1.0) - 2021-10-27
### Added
- [[#7558](https://github.com/sonata-project/SonataAdminBundle/pull/7558)] Added more generic information ([@core23](https://github.com/core23))
- [[#7555](https://github.com/sonata-project/SonataAdminBundle/pull/7555)] Added support for Symfony 6 ([@jordisala1991](https://github.com/jordisala1991))

### Changed
- [[#7549](https://github.com/sonata-project/SonataAdminBundle/pull/7549)] Move appendParentObject call into createNewInstance ([@goetas](https://github.com/goetas))

### Deprecated
- [[#7519](https://github.com/sonata-project/SonataAdminBundle/pull/7519)] `Sonata\AdminBundle\Builder\BuilderInterface` ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7556](https://github.com/sonata-project/SonataAdminBundle/pull/7556)] Set subject of $associationAdmin when collection is append to childs collections. ([@Pasttaga](https://github.com/Pasttaga))
- [[#7537](https://github.com/sonata-project/SonataAdminBundle/pull/7537)] Made `getBatchActions()` return an empty array when the admin does not have a batch route ([@7ochem](https://github.com/7ochem))

## [4.0.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.0.0...4.0.1) - 2021-10-01
### Changed
- [[#7506](https://github.com/sonata-project/SonataAdminBundle/pull/7506)] Improved error message when a required option is not passed to the FormType. ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7514](https://github.com/sonata-project/SonataAdminBundle/pull/7514)] `empty_boxes` option ([@krubenrc](https://github.com/krubenrc))
- [[#7509](https://github.com/sonata-project/SonataAdminBundle/pull/7509)] Do not display empty user-block on the navbar ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7483](https://github.com/sonata-project/SonataAdminBundle/pull/7483)] Issue where `configureActionButtons()` is not being called in custom actions. ([@toooni](https://github.com/toooni))
- [[#7487](https://github.com/sonata-project/SonataAdminBundle/pull/7487)] Deprecations triggered with Symfony 5.4 ([@franmomu](https://github.com/franmomu))

## [4.0.0](https://github.com/sonata-project/SonataAdminBundle/compare/4.0.0-rc.2...4.0.0) - 2021-09-05
### Added
- [[#7464](https://github.com/sonata-project/SonataAdminBundle/pull/7464)] New role `ROLE_MYADMIN_HISTORY` to access to the history actions ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7452](https://github.com/sonata-project/SonataAdminBundle/pull/7452)] `AdminInterface::removeFieldFromShowGroup` method ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7452](https://github.com/sonata-project/SonataAdminBundle/pull/7452)] `AbstractAdmin::removeFieldFromShowGroup` method ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#7458](https://github.com/sonata-project/SonataAdminBundle/pull/7458)] Fail fast when using ACL as security handler without security.acl.provider service ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7454](https://github.com/sonata-project/SonataAdminBundle/pull/7454)] The route used by search result is now `show`. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7454](https://github.com/sonata-project/SonataAdminBundle/pull/7454)] The route used by `ListMapper::addIdentifier()` is now `show` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7464](https://github.com/sonata-project/SonataAdminBundle/pull/7464)] `AbstractAdmin::getAccessMapping` visibility ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7450](https://github.com/sonata-project/SonataAdminBundle/pull/7450)] `AclSecurityHandlerInterface::addObjectClassAces()` signature ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7450](https://github.com/sonata-project/SonataAdminBundle/pull/7450)] `AclSecurityHandler::addObjectClassAces()` signature ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7450](https://github.com/sonata-project/SonataAdminBundle/pull/7450)] `AdminAclManipulator::addAdminClassAces()` signature ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7450](https://github.com/sonata-project/SonataAdminBundle/pull/7450)] `AdminAclManipulatorInterface::addAdminClassAces()` signature ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7455](https://github.com/sonata-project/SonataAdminBundle/pull/7455)] Missing translation on batch confirmation page. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7452](https://github.com/sonata-project/SonataAdminBundle/pull/7452)] Fix `CollectionType` for nested fields. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7452](https://github.com/sonata-project/SonataAdminBundle/pull/7452)] `ShowMapper::remove` method now correctly remove the field from the groups. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7453](https://github.com/sonata-project/SonataAdminBundle/pull/7453)] Add Font Awesome icon instead of the jQuery UI icon ([@willemverspyck](https://github.com/willemverspyck))

### Removed
- [[#7454](https://github.com/sonata-project/SonataAdminBundle/pull/7454)] `AbstractAdmin::searchResultAction()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7454](https://github.com/sonata-project/SonataAdminBundle/pull/7454)] `AbstractAdmin::getSearchResultLink()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7454](https://github.com/sonata-project/SonataAdminBundle/pull/7454)] `AdminInterface::getSearchResultLink()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7464](https://github.com/sonata-project/SonataAdminBundle/pull/7464)] Access to the history actions with the role `ROLE_MYADMIN_EDIT` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7464](https://github.com/sonata-project/SonataAdminBundle/pull/7464)] `AdminInterface::getAccessMapping` method ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.0.0-rc.2](https://github.com/sonata-project/SonataAdminBundle/compare/4.0.0-rc.1...4.0.0-rc.2) - 2021-08-24
### Added
- [[#7449](https://github.com/sonata-project/SonataAdminBundle/pull/7449)] `Revision` class ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#7449](https://github.com/sonata-project/SonataAdminBundle/pull/7449)] `AuditReaderInterface::findRevision()` return type ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7448](https://github.com/sonata-project/SonataAdminBundle/pull/7448)] Display of list when a field is called `elements`. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7433](https://github.com/sonata-project/SonataAdminBundle/pull/7433)] Make Select2 use the locale. ([@vindert](https://github.com/vindert))
- [[#7445](https://github.com/sonata-project/SonataAdminBundle/pull/7445)] Display of the `base_show_compare` template ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7386](https://github.com/sonata-project/SonataAdminBundle/pull/7386)] Display of nested form fields by the  FormMapper ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7386](https://github.com/sonata-project/SonataAdminBundle/pull/7386)] `AbstractAdmin::configureActionButtons()` phpdoc for phpstan ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7388](https://github.com/sonata-project/SonataAdminBundle/pull/7388)] Do not throw error when trying to inject an admin as a service in an action. ([@VincentLanglet](https://github.com/VincentLanglet))

### Security
- [[#7393](https://github.com/sonata-project/SonataAdminBundle/pull/7393)] Bump yarn dependencies ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.0.0-rc.1](https://github.com/sonata-project/SonataAdminBundle/compare/4.0.0-alpha-2...4.0.0.rc.1) - 2021-08-08
### Added
- [[#7364](https://github.com/sonata-project/SonataAdminBundle/pull/7364)]  `FormMapper` now implements the `BlockBundle/FormMapper` interface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7348](https://github.com/sonata-project/SonataAdminBundle/pull/7348)] Support for POST request to deleteAction. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7351](https://github.com/sonata-project/SonataAdminBundle/pull/7351)] Added second parameter to `AdminInterface::getPersistentParameter()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7205](https://github.com/sonata-project/SonataAdminBundle/pull/7205)] Added missing generic phpdoc for `AdminInterface::getSubject()` method ([@franmomu](https://github.com/franmomu))

### Changed
- [[#7358](https://github.com/sonata-project/SonataAdminBundle/pull/7358)] Icon for filters ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7310](https://github.com/sonata-project/SonataAdminBundle/pull/7310)] Remove unused code and add some `final` keyword ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7252](https://github.com/sonata-project/SonataAdminBundle/pull/7252)] Add final to multiple method of CRUDController ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7345](https://github.com/sonata-project/SonataAdminBundle/pull/7345)] Make `getPreviousFilter()` non nullable ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7347](https://github.com/sonata-project/SonataAdminBundle/pull/7347)] Block_search_result.html.twig now display all the possible filter and not only the matching ones. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7238](https://github.com/sonata-project/SonataAdminBundle/pull/7238)] `isXmlHttpRequest()`, `redirectTo()`, `isPreviewApproved()`, `isInPreviewMode()`, `isPreviewDeclined()`, `validateCsrfToken()` signatures. They now require to pass the request as first argument. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7241](https://github.com/sonata-project/SonataAdminBundle/pull/7241)] Changed argument 1 of `SessionFilterPersister::__construct()` from `SessionInterface` to `RequestStack` ([@franmomu](https://github.com/franmomu))
- [[#7220](https://github.com/sonata-project/SonataAdminBundle/pull/7220)] All the protected property of the AsbtractAdmin with a specific setter are now private. ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7350](https://github.com/sonata-project/SonataAdminBundle/pull/7350)] Fixed behavior of placeholder (empty value) for select2 choice fields ([@dmaicher](https://github.com/dmaicher))
- [[#7330](https://github.com/sonata-project/SonataAdminBundle/pull/7330)] Fix for not showing placeholder when using Select2 with ChoiceType ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7342](https://github.com/sonata-project/SonataAdminBundle/pull/7342)] Fixed broken javascript using `select2` ([@franmomu](https://github.com/franmomu))
- [[#7193](https://github.com/sonata-project/SonataAdminBundle/pull/7193)] Scroll lag on Safari due to refreshNavbarStuckClass call. ([@moostrich](https://github.com/moostrich))
- [[#7253](https://github.com/sonata-project/SonataAdminBundle/pull/7253)] Allow relative path to resources ([@jderusse](https://github.com/jderusse))
- [[#7249](https://github.com/sonata-project/SonataAdminBundle/pull/7249)] "multiple" option of AutocompleteType ([@willemverspyck](https://github.com/willemverspyck))
- [[#7219](https://github.com/sonata-project/SonataAdminBundle/pull/7219)] Fix the css of sort arrows in lists. ([@Laffesz](https://github.com/Laffesz))
- [[#7224](https://github.com/sonata-project/SonataAdminBundle/pull/7224)] Focus on Select2 or CKEditor in modal dialog ([@willemverspyck](https://github.com/willemverspyck))
- [[#7208](https://github.com/sonata-project/SonataAdminBundle/pull/7208)] Fixed `keep_open` option to always keep open a group menu ([@franmomu](https://github.com/franmomu))
- [[#7202](https://github.com/sonata-project/SonataAdminBundle/pull/7202)] Missing comma & bracket ([@cecicifu](https://github.com/cecicifu))
- [[#7198](https://github.com/sonata-project/SonataAdminBundle/pull/7198)] Pagination results per page selector ([@1ed](https://github.com/1ed))

### Removed
- [[#7354](https://github.com/sonata-project/SonataAdminBundle/pull/7354)] Removed support for Symfony 5.2 ([@jordisala1991](https://github.com/jordisala1991))
- [[#7354](https://github.com/sonata-project/SonataAdminBundle/pull/7354)] Removed support for SensioFrameworkExtraBundle < 6.1 ([@jordisala1991](https://github.com/jordisala1991))
- [[#7319](https://github.com/sonata-project/SonataAdminBundle/pull/7319)] Properties and getters validation from ExplainAdminCommand. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7238](https://github.com/sonata-project/SonataAdminBundle/pull/7238)] `CRUDController::getRequest()` method ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.0.0-alpha-2](https://github.com/sonata-project/SonataAdminBundle/compare/4.0.0-alpha-1...4.0.0-alpha-2) - 2021-05-14
### Added
- [[#7131](https://github.com/sonata-project/SonataAdminBundle/pull/7131)] Add Source Sans Pro font source files ([@jordisala1991](https://github.com/jordisala1991))
- [[#7022](https://github.com/sonata-project/SonataAdminBundle/pull/7022)] Added `FilterData` to model the data from a filter ([@franmomu](https://github.com/franmomu))
- [[#7034](https://github.com/sonata-project/SonataAdminBundle/pull/7034)] Added integration with webpack encore ([@jordisala1991](https://github.com/jordisala1991))
- [[#7034](https://github.com/sonata-project/SonataAdminBundle/pull/7034)] Npm as a package manager for internal assets ([@jordisala1991](https://github.com/jordisala1991))
### Changed
- [[#7133](https://github.com/sonata-project/SonataAdminBundle/pull/7133)] Upgrade to Font Awesome 5 maintaining compatibility with version 4 icons ([@jordisala1991](https://github.com/jordisala1991))
- [[#7109](https://github.com/sonata-project/SonataAdminBundle/pull/7109)] Move public images to `src/Resources/public/images` directory ([@jordisala1991](https://github.com/jordisala1991))
- [[#7106](https://github.com/sonata-project/SonataAdminBundle/pull/7106)] Unify js files into a single `app.js` file ([@jordisala1991](https://github.com/jordisala1991))
- [[#7022](https://github.com/sonata-project/SonataAdminBundle/pull/7022)] Changed `FilterInterface::apply()` to accept a `FilterData` instance instead of `array` in argument 4 ([@franmomu](https://github.com/franmomu))
- [[#7098](https://github.com/sonata-project/SonataAdminBundle/pull/7098)] Unify vendor js files into a single `app.js` file ([@jordisala1991](https://github.com/jordisala1991))
- [[#7084](https://github.com/sonata-project/SonataAdminBundle/pull/7084)] Unify css files into a single `app.css` file ([@jordisala1991](https://github.com/jordisala1991))
- [[#7084](https://github.com/sonata-project/SonataAdminBundle/pull/7084)] Move remaining third party assets from `src/Resources/public` to `assets` directory. Only necessary assets are exposed to the `public` dir ([@jordisala1991](https://github.com/jordisala1991))
- [[#7073](https://github.com/sonata-project/SonataAdminBundle/pull/7073)] Move css files to root assets directory and only expose the minified `app.css` ([@jordisala1991](https://github.com/jordisala1991))
- [[#7067](https://github.com/sonata-project/SonataAdminBundle/pull/7067)] Upgrade all npm packages to their latest patch release ([@jordisala1991](https://github.com/jordisala1991))
- [[#7067](https://github.com/sonata-project/SonataAdminBundle/pull/7067)] Upgrade `jquery-form` to v4 ([@jordisala1991](https://github.com/jordisala1991))
- [[#7067](https://github.com/sonata-project/SonataAdminBundle/pull/7067)] Upgrade `jquery.scrollto` to v2 ([@jordisala1991](https://github.com/jordisala1991))
- [[#7048](https://github.com/sonata-project/SonataAdminBundle/pull/7048)] Upgrade to Select2 ^4.0. ([@jordisala1991](https://github.com/jordisala1991))
- [[#7034](https://github.com/sonata-project/SonataAdminBundle/pull/7034)] Changed paths of javascript and css files ([@jordisala1991](https://github.com/jordisala1991))
### Removed
- [[#7147](https://github.com/sonata-project/SonataAdminBundle/pull/7147)] Support for phpcr ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7067](https://github.com/sonata-project/SonataAdminBundle/pull/7067)] Removed Ionicons dependency ([@jordisala1991](https://github.com/jordisala1991))
- [[#7034](https://github.com/sonata-project/SonataAdminBundle/pull/7034)] Bower as a package manager ([@jordisala1991](https://github.com/jordisala1991))

## [4.0.0-alpha-1](https://github.com/sonata-project/SonataAdminBundle/compare/3.105.0...4.0.0-alpha-1) - 2021-04-11
See UPGRADE-4.0.md

## [3.107.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.107.1...3.107.2) - 2022-01-19
### Fixed
- [[#7705](https://github.com/sonata-project/SonataAdminBundle/pull/7705)] Catch AccessException in AbstractAdmin::appendParentObject() to prevent an error when the property for the parent object is uninitialised. ([@7ochem](https://github.com/7ochem))

## [3.107.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.107.0...3.107.1) - 2022-01-01
### Fixed
- [[#7641](https://github.com/sonata-project/SonataAdminBundle/pull/7641)] Fixed batch confirmation translations to include mentioning the selected action or object ([@7ochem](https://github.com/7ochem))
- [[#7641](https://github.com/sonata-project/SonataAdminBundle/pull/7641)] Fixed (other) Dutch translations that were missing a placeholder compared to the English file ([@7ochem](https://github.com/7ochem))

## [3.107.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.106.1...3.107.0) - 2021-11-15
### Added
- [[#7602](https://github.com/sonata-project/SonataAdminBundle/pull/7602)] `ModelManagerThrowable` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7602](https://github.com/sonata-project/SonataAdminBundle/pull/7602)] `CRUDController::handleModelManagerThrowable()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#7602](https://github.com/sonata-project/SonataAdminBundle/pull/7602)] `CRUDController::handleModelManagerException()` ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.106.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.106.0...3.106.1) - 2021-10-31
### Fixed
- [[#7574](https://github.com/sonata-project/SonataAdminBundle/pull/7574)] Missing `merge` call in twig ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.106.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.105.3...3.106.0) - 2021-10-30
### Added
- [[#7563](https://github.com/sonata-project/SonataAdminBundle/pull/7563)] `AbstractAdmin::removeExtension()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7564](https://github.com/sonata-project/SonataAdminBundle/pull/7564)] The ability to not add a `select` column when accessing to the list with AJAX ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7567](https://github.com/sonata-project/SonataAdminBundle/pull/7567)] Final information to `AbstractAdmin::getAccess()` ([@core23](https://github.com/core23))

### Fixed
- [[#7529](https://github.com/sonata-project/SonataAdminBundle/pull/7529)] Setting a default order if it is not set in the field description ([@franmomu](https://github.com/franmomu))
- [[#7530](https://github.com/sonata-project/SonataAdminBundle/pull/7530)] Using multiple properties with `ModelAutocompleteType` with different order ([@franmomu](https://github.com/franmomu))

### Removed
- [[#7567](https://github.com/sonata-project/SonataAdminBundle/pull/7567)] Final information from `AbstractAdmin::getAccessMapping()` ([@core23](https://github.com/core23))

## [3.105.3](https://github.com/sonata-project/SonataAdminBundle/compare/3.105.2...3.105.3) - 2021-10-07
### Fixed
- [[#7524](https://github.com/sonata-project/SonataAdminBundle/pull/7524)] HTML icon rendering in the menu ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.105.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.105.1...3.105.2) - 2021-10-05
### Fixed
- [[#7521](https://github.com/sonata-project/SonataAdminBundle/pull/7521)] Correctly display the default icon in the admin menu if none are provided ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.105.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.105.0...3.105.1) - 2021-09-08
### Fixed
- [[#7387](https://github.com/sonata-project/SonataAdminBundle/pull/7387)] Do not throw error when trying to inject an admin as a service in an action. ([@VincentLanglet](https://github.com/VincentLanglet))

### Removed
- [[#7376](https://github.com/sonata-project/SonataAdminBundle/pull/7376)] Information about the matching filters in the search results, because it does not work for cascaded oneToMany filters. ([@core23](https://github.com/core23))

## [3.105.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.104.0...3.105.0) - 2021-08-07
### Added
- [[#7356](https://github.com/sonata-project/SonataAdminBundle/pull/7356)] Added support for `psr/log` ^2.0. ([@jordisala1991](https://github.com/jordisala1991))

### Deprecated
- [[#7362](https://github.com/sonata-project/SonataAdminBundle/pull/7362)] Deprecated using `@ParamConverter` annotation for injecting admin services ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#7363](https://github.com/sonata-project/SonataAdminBundle/pull/7363)] Requesting a non existent service "sonata.admin.exporter.do-not-use". ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7360](https://github.com/sonata-project/SonataAdminBundle/pull/7360)] `RetrieveAutocompleItemsAction` when used with an array of property ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.104.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.103.0...3.104.0) - 2021-07-20
### Added
- [[#7325](https://github.com/sonata-project/SonataAdminBundle/pull/7325)] Option `list_action_button_content` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7299](https://github.com/sonata-project/SonataAdminBundle/pull/7299)] Added support for non admin prefixed `ROLE_*` roles by the RoleSecurityHandler ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7306](https://github.com/sonata-project/SonataAdminBundle/pull/7306)] New `block` twig in order to override the button display by the admin in the base_edit_form ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#7320](https://github.com/sonata-project/SonataAdminBundle/pull/7320)] `response_item_callback` callback signature ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#7325](https://github.com/sonata-project/SonataAdminBundle/pull/7325)] Option `title_mode`. Use `logo_content` instead ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7290](https://github.com/sonata-project/SonataAdminBundle/pull/7290)] Passing `admin` in the request of search action ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7335](https://github.com/sonata-project/SonataAdminBundle/pull/7335)] Crash on choice field inline edition with a 0 value ([@soullivaneuh](https://github.com/soullivaneuh))
- [[#7332](https://github.com/sonata-project/SonataAdminBundle/pull/7332)] Accessing on non existent array keys `translation_domain` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7320](https://github.com/sonata-project/SonataAdminBundle/pull/7320)] `response_item_callback` option of ModelAutocompleteFilter ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.103.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.102.0...3.103.0) - 2021-07-11
### Added
- [[#7308](https://github.com/sonata-project/SonataAdminBundle/pull/7308)] `ChainableFilterInterface` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7285](https://github.com/sonata-project/SonataAdminBundle/pull/7285)] Added deprecated annotation to AbstractAdmin validate() method. ([@7ochem](https://github.com/7ochem))
- [[#7275](https://github.com/sonata-project/SonataAdminBundle/pull/7275)] SearchableFilterInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7283](https://github.com/sonata-project/SonataAdminBundle/pull/7283)] Added `IconExtension` twig extension. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7283](https://github.com/sonata-project/SonataAdminBundle/pull/7283)] Added `parse_icon` filter. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7279](https://github.com/sonata-project/SonataAdminBundle/pull/7279)] Add generic to MapperInterface and all extending classes. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7279](https://github.com/sonata-project/SonataAdminBundle/pull/7279)] Added generic to ConfigureEvent and PersistenceEvent. ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#7301](https://github.com/sonata-project/SonataAdminBundle/pull/7301)] Not passing argument 2 of `Symfony\Component\Form\FormRegistryInterface` to `AbstractFormContractor::__construct` ([@dmaicher](https://github.com/dmaicher))
- [[#7275](https://github.com/sonata-project/SonataAdminBundle/pull/7275)] Passing `global_search` option to a filter which does not implements `SearchableFilterInterface`. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7275](https://github.com/sonata-project/SonataAdminBundle/pull/7275)] `sonata_admin.global_search.case_sensitive` configuration. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7282](https://github.com/sonata-project/SonataAdminBundle/pull/7282)] `AbstractAdmin::addParentAssociationMapping()` method. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7282](https://github.com/sonata-project/SonataAdminBundle/pull/7282)] Calling `AdminInterface::setParent()` without second argument. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7282](https://github.com/sonata-project/SonataAdminBundle/pull/7282)] Calling `AdminInterface::getParentAssociationMapping()` for a non parent admin. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7265](https://github.com/sonata-project/SonataAdminBundle/pull/7265)] Passing a FieldDescriptionInterface as first argument of method `ListMapper::add`. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7265](https://github.com/sonata-project/SonataAdminBundle/pull/7265)] Passing a FieldDescriptionInterface as first argument of method `ShowMapper::add`. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7265](https://github.com/sonata-project/SonataAdminBundle/pull/7265)] Passing a FieldDescriptionInterface as first argument of method `DatagridMapper::add`. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7265](https://github.com/sonata-project/SonataAdminBundle/pull/7265)] Passing a FormBuilderInterface as first argument of method `FormMapper::add`. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7265](https://github.com/sonata-project/SonataAdminBundle/pull/7265)] `FieldDescriptionInterface::mergeOptions()`. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7265](https://github.com/sonata-project/SonataAdminBundle/pull/7265)] `BaseFieldDescription::mergeOptions()`. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7283](https://github.com/sonata-project/SonataAdminBundle/pull/7283)] Passing an `icon` option with the format `fa-plus` or `plus`, use the format `fa fa-plus` or pass directly the `<i>` html instead. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7267](https://github.com/sonata-project/SonataAdminBundle/pull/7267)] Deprecated passing `collection` as argument 2 of `FormMapper::add()` method ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#7301](https://github.com/sonata-project/SonataAdminBundle/pull/7301)] Default options are added correctly to form types that use the Symfony way of extending Sonata's default types ([@dmaicher](https://github.com/dmaicher))
- [[#7287](https://github.com/sonata-project/SonataAdminBundle/pull/7287)] `FieldCollection::reorder()` and `Datagrid::reorderFilter()` methods. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7254](https://github.com/sonata-project/SonataAdminBundle/pull/7254)] Filters are correctly updated if only default values are submitted. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7277](https://github.com/sonata-project/SonataAdminBundle/pull/7277)] Respect `false` as translation_domain for tab and action buttons. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7264](https://github.com/sonata-project/SonataAdminBundle/pull/7264)] `Max_per_page` drowpdown propagate correctly the `sort_by` filter value ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7271](https://github.com/sonata-project/SonataAdminBundle/pull/7271)] Do not try to translate fieldDescription label when false is provided as translation domain. ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.102.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.101.0...3.102.0) - 2021-06-21
### Deprecated
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getDataSourceIterator()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::create()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::delete()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getFilterParameters()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getParentAssociationMapping()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getBaseRoutePattern()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getBaseRouteName()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getClass()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getSubClasses()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setSubClasses()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasSubClass()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasActiveSubClass()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getActiveSubClass()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getActiveSubclassCode()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getBatchActions()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getRoutes()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasRoute()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::isCurrentRoute()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::generateObjectUrl()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::generateUrl()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::generateMenuUrl()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getFormBuilder()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::defineFormBuilder()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::attachAdminClass()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getForm()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getList()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getDatagrid()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getSideMenu()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getRootCode()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getRoot()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setBaseControllerName()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getBaseControllerName()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getMaxPerPage()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setMaxPageLinks()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getMaxPageLinks()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getFormGroups()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setFormGroups()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::removeFieldFromFormGroup()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::reorderFormGroup()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getFormTabs()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setFormTabs()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getShowTabs()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setShowTabs()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getShowGroups()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setShowGroups()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::reorderShowGroup()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setParentFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getParentFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasParentFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setSubject()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getSubject()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasSubject()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getFormFieldDescriptions()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getFormFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasFormFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::addFormFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getShowFieldDescriptions()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getShowFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasShowFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::addShowFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::removeShowFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getListFieldDescriptions()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasListFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::addListFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::removeListFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getFilterFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasFilterFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::addFilterFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::removeFilterFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getFilterFieldDescriptions()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::addChild()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasChild()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getChildren()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getChild()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::isChild()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasChildren()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setUniqid()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getUniqid()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getClassnameLabel()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getPersistentParameter()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setCurrentChild()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::isCurrentChild()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getCurrentChildAdmin()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setTranslationDomain()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getTranslationDomain()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getTranslationLabel()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setRequest()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getRequest()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasRequest()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getCode()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getBaseCodeRoute()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::showIn()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::createObjectSecurity()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::isGranted()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getUrlSafeIdentifier()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getNormalizedIdentifier()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getShow()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setFormTheme()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getFormTheme()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setFilterTheme()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getFilterTheme()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::addExtension()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getExtensions()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::supportsPreviewMode()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::determinedPerPageValue()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::isAclEnabled()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::setListMode()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getListMode()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getAccessMapping()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::checkAccess()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::hasAccess()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7266](https://github.com/sonata-project/SonataAdminBundle/pull/7266)] Overriding `AbstractAdmin::getActionButtons()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7263](https://github.com/sonata-project/SonataAdminBundle/pull/7263)] Overriding `renderWithExtraParams()`, `renderJson()`, `isXmlHttpRequest()`, `isPreviewRequested()`, `isPreviewApproved()`, `isInPreviewMode()`, `isPreviewDeclined()`, `validateCsrfToken()`, `escapeHtml()`, `getCsrfToken()` methods in `CRUDController` ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7247](https://github.com/sonata-project/SonataAdminBundle/pull/7247)] Correctly propagate the group translation domain to field description even if it's `false` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7247](https://github.com/sonata-project/SonataAdminBundle/pull/7247)] Field description stop to use the admin translation domain if the value was set to `false` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7262](https://github.com/sonata-project/SonataAdminBundle/pull/7262)] Conflicts with other libraries using the `$` variable ([@phansys](https://github.com/phansys))

## [3.101.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.100.2...3.101.0) - 2021-06-13
### Added
- [[#7240](https://github.com/sonata-project/SonataAdminBundle/pull/7240)] `strict` param to `CRUDController::assertObjectExists()` in order to check for the existence of the id in the request ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7239](https://github.com/sonata-project/SonataAdminBundle/pull/7239)] Display of `history_revision_timestamp.html.twig` ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.100.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.100.1...3.100.2) - 2021-05-27
### Changed
- [[#7223](https://github.com/sonata-project/SonataAdminBundle/pull/7223)] Reverted the changes of #7196, because they only work for Select2 version 4, that is used on the Master branche. Branche 3.x uses Select2 version 3. ([@willemverspyck](https://github.com/willemverspyck))

### Fixed
- [[#7211](https://github.com/sonata-project/SonataAdminBundle/pull/7211)] ChildId are not lost anymore when using multiple times the appendFormFieldAction and similar actions. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7214](https://github.com/sonata-project/SonataAdminBundle/pull/7214)] `FieldDescriptionInterface::getMappingType()` return type ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7206](https://github.com/sonata-project/SonataAdminBundle/pull/7206)] Fixed type of `sortable` option in `FieldDescriptionOptions` ([@franmomu](https://github.com/franmomu))

## [3.100.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.100.0...3.100.1) - 2021-05-18
### Fixed
- [[#7203](https://github.com/sonata-project/SonataAdminBundle/pull/7203)] `edit_many_script.html.twig` rendering ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.100.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.99.1...3.100.0) - 2021-05-18
### Deprecated
- [[#7191](https://github.com/sonata-project/SonataAdminBundle/pull/7191)] Passing a `code` value in the request of AppendFormFieldElementAction, GetShortObjectDescriptionAction, RetrieveAutocompleteItemsAction, RetrieveFormFieldElementAction and SetObjectFieldValueAction. Pass `_sonata_admin` instead. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7191](https://github.com/sonata-project/SonataAdminBundle/pull/7191)] Passing a `Pool` argument to the constructor of AppendFormFieldElementAction, GetShortObjectDescriptionAction, RetrieveAutocompleteItemsAction, RetrieveFormFieldElementAction and SetObjectFieldValueAction. Pass an AdminFetcher instead. ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7191](https://github.com/sonata-project/SonataAdminBundle/pull/7191)] AppendFormFieldElementAction, GetShortObjectDescriptionAction, RetrieveAutocompleteItemsAction, RetrieveFormFieldElementAction and SetObjectFieldValueAction now set the childAdmin subjects to the childAdmin. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7196](https://github.com/sonata-project/SonataAdminBundle/pull/7196)] Changed .select2() to .attr() ([@cecicifu](https://github.com/cecicifu))
- [[#7196](https://github.com/sonata-project/SonataAdminBundle/pull/7196)] Set id in the value attribute of hidden input after save ([@cecicifu](https://github.com/cecicifu))

## [3.99.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.99.0...3.99.1) - 2021-05-11
### Fixed
- [[#7181](https://github.com/sonata-project/SonataAdminBundle/pull/7181)] Fix SimplePager pagination ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.99.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.98.2...3.99.0) - 2021-05-09
### Added
- [[#7077](https://github.com/sonata-project/SonataAdminBundle/pull/7077)] Added `default_admin_services` config key to overwrite the default services provided to all the admin ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7144](https://github.com/sonata-project/SonataAdminBundle/pull/7144)] Added `AdminExtensionInterface::configure()` method ([@yann-eugone](https://github.com/yann-eugone))
- [[#7144](https://github.com/sonata-project/SonataAdminBundle/pull/7144)] Added `AbstractAdminExtension::configure()` method with no content ([@yann-eugone](https://github.com/yann-eugone))
- [[#6950](https://github.com/sonata-project/SonataAdminBundle/pull/6950)] Added `AdminFetcher` to fetch the `Admin` service associated to a request ([@franmomu](https://github.com/franmomu))
- [[#6950](https://github.com/sonata-project/SonataAdminBundle/pull/6950)] Added `AdminParamConverter` to be able to inject an Admin service in a controller ([@franmomu](https://github.com/franmomu))
- [[#7108](https://github.com/sonata-project/SonataAdminBundle/pull/7108)] `final` for method of every abstract classes ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#7144](https://github.com/sonata-project/SonataAdminBundle/pull/7144)] Changed `AbstractAdmin::initialize()` and call `AdminExtensionInterface::configure()` on each extension ([@yann-eugone](https://github.com/yann-eugone))

### Deprecated
- [[#7077](https://github.com/sonata-project/SonataAdminBundle/pull/7077)] Deprecated `admin_services` config key ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#7162](https://github.com/sonata-project/SonataAdminBundle/pull/7162)] Fix font route to be relative and not absolute ([@jordisala1991](https://github.com/jordisala1991))
- [[#7149](https://github.com/sonata-project/SonataAdminBundle/pull/7149)] `ProxyQuery::execute()` return type; it doesn't require to be an instance of `Countable` ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.98.2](https://github.com/sonata-project/SonataAdminBundle/compare/3.98.1...3.98.2) - 2021-05-01
### Fixed
- [[#7132](https://github.com/sonata-project/SonataAdminBundle/pull/7132)] Fixed inclusion of Source Sans Pro as main font ([@jordisala1991](https://github.com/jordisala1991))
- [[#7127](https://github.com/sonata-project/SonataAdminBundle/pull/7127)] `AdminInterface::initialize()` is the last method called ([@dmaicher](https://github.com/dmaicher))
- [[#7127](https://github.com/sonata-project/SonataAdminBundle/pull/7127)] `admin_services.templates.form` is correctly set ([@dmaicher](https://github.com/dmaicher))
- [[#7127](https://github.com/sonata-project/SonataAdminBundle/pull/7127)] `admin_services.templates.filter` is correctly set ([@dmaicher](https://github.com/dmaicher))
- [[#7127](https://github.com/sonata-project/SonataAdminBundle/pull/7127)] Default service setter calls on `AdminInterface` are only added if there are no calls defined yet ([@dmaicher](https://github.com/dmaicher))
- [[#7135](https://github.com/sonata-project/SonataAdminBundle/pull/7135)] Remove `do-not-use` alias for exporter service. ([@eerison](https://github.com/eerison))
- [[#7130](https://github.com/sonata-project/SonataAdminBundle/pull/7130)] Fix `display_url.html.twig` template ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.98.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.98.0...3.98.1) - 2021-04-27
### Fixed
- [[#7125](https://github.com/sonata-project/SonataAdminBundle/pull/7125)] Unclosed "{" on src/Resources/views/CRUD/Association/edit_one_to_one.html.twig ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7128](https://github.com/sonata-project/SonataAdminBundle/pull/7128)] Fix displays of the link to go to page 1 when accessing on a non-existent List page ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.98.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.97.0...3.98.0) - 2021-04-27
### Added
- [[#7118](https://github.com/sonata-project/SonataAdminBundle/pull/7118)] Added `FilterData` to use it for forward compatibility with `4.0` ([@franmomu](https://github.com/franmomu))
- [[#7115](https://github.com/sonata-project/SonataAdminBundle/pull/7115)] `TaggedAdmin::setListModes()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7092](https://github.com/sonata-project/SonataAdminBundle/pull/7092)] Generic template for DatagridBuilderInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7092](https://github.com/sonata-project/SonataAdminBundle/pull/7092)] Generic template for LockInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7088](https://github.com/sonata-project/SonataAdminBundle/pull/7088)] Added `render_breadcrumbs` and `render_breadcrumbs_for_title` Twig functions to render breadcrumbs ([@franmomu](https://github.com/franmomu))

### Changed
- [[#7112](https://github.com/sonata-project/SonataAdminBundle/pull/7112)] Restrict return type of `ProxyQuery::execute()` method to `array<object>|(\Traversable<object>&\Countable)` ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#7115](https://github.com/sonata-project/SonataAdminBundle/pull/7115)] `TaggedAdmin::showMosaicButton()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7110](https://github.com/sonata-project/SonataAdminBundle/pull/7110)] `_email_link.html.twig` template, use `display_email.html.twig` instead ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7110](https://github.com/sonata-project/SonataAdminBundle/pull/7110)] Using `display_*` templates passing a field_description, you SHOULD pass the option directly instead ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7088](https://github.com/sonata-project/SonataAdminBundle/pull/7088)] Deprecated overriding the `breadcrumbs_builder` variable passed to Twig templates ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#7103](https://github.com/sonata-project/SonataAdminBundle/pull/7103)] FieldDescriptions options phpdoc to keep only common options ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7121](https://github.com/sonata-project/SonataAdminBundle/pull/7121)] Calling foreach on `ArrayAccess` in ModelToIdPropertyTransformer ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7065](https://github.com/sonata-project/SonataAdminBundle/pull/7065)] Fixed "Field "FIELD" has already been rendered" error message when using ModelType ([@epci](https://github.com/epci))
- [[#7092](https://github.com/sonata-project/SonataAdminBundle/pull/7092)] `DatagridBuilderInterface::addFilter` phpdoc: The `$type` MUST be a class-string ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7092](https://github.com/sonata-project/SonataAdminBundle/pull/7092)] Missing generic template for ModelManagerInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7110](https://github.com/sonata-project/SonataAdminBundle/pull/7110)] Stop throwing an error when using `display_*` templates without passing a field_description ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7093](https://github.com/sonata-project/SonataAdminBundle/pull/7093)] URLs generated at global search results for the list filtering, which were missing the `value` dimension ([@phansys](https://github.com/phansys))
- [[#7093](https://github.com/sonata-project/SonataAdminBundle/pull/7093)] Layout for the filter links generated at global search results ([@phansys](https://github.com/phansys))

### Removed
- [[#7100](https://github.com/sonata-project/SonataAdminBundle/pull/7100)] Remove deprecation for `AbstractAdmin::preValidate()` method ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.97.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.96.0...3.97.0) - 2021-04-19
### Added
- [[#7033](https://github.com/sonata-project/SonataAdminBundle/pull/7033)] Added "block.admin_preview" block in order to show a preview for admin lists in dashboard ([@phansys](https://github.com/phansys))
- [[#7078](https://github.com/sonata-project/SonataAdminBundle/pull/7078)] Added `DatagridInterface::SORT_ORDER` constant to use it instead of `_sort_order` string ([@franmomu](https://github.com/franmomu))
- [[#7078](https://github.com/sonata-project/SonataAdminBundle/pull/7078)] Added `DatagridInterface::SORT_BY` constant to use it instead of `_sort_by` string ([@franmomu](https://github.com/franmomu))
- [[#7078](https://github.com/sonata-project/SonataAdminBundle/pull/7078)] Added `DatagridInterface::PAGE` constant to use it instead of `_page` string.([@franmomu](https://github.com/franmomu))
- [[#7078](https://github.com/sonata-project/SonataAdminBundle/pull/7078)] Added `DatagridInterface::PER_PAGE` constant to use it instead of `_per_page` string ([@franmomu](https://github.com/franmomu))
- [[#7066](https://github.com/sonata-project/SonataAdminBundle/pull/7066)] Added generic for ModelManager class ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7063](https://github.com/sonata-project/SonataAdminBundle/pull/7063)] Added `XEditableExtension::FIELD_DESCRIPTION_MAPPING` to expose mapping between FieldDescription types and xEditable types ([@franmomu](https://github.com/franmomu))
- [[#7039](https://github.com/sonata-project/SonataAdminBundle/pull/7039)] Added `MapperInterface` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7037](https://github.com/sonata-project/SonataAdminBundle/pull/7037)] Generics for Datagrid and DatagridInterface ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7005](https://github.com/sonata-project/SonataAdminBundle/pull/7005)] Information about the matching filters in the search results ([@phansys](https://github.com/phansys))

### Changed
- [[#7074](https://github.com/sonata-project/SonataAdminBundle/pull/7074)] Changed `TemplateRegistryExtension` constructor to `@internal` ([@franmomu](https://github.com/franmomu))
- [[#7038](https://github.com/sonata-project/SonataAdminBundle/pull/7038)] Calls to `FieldDescriptionInterface::getOptions()` by calls to `FieldDescriptionInterface::getOption()` in views ([@phansys](https://github.com/phansys))
- [[#7044](https://github.com/sonata-project/SonataAdminBundle/pull/7044)] Calls to `Filter::getOptions()` by `FilterInterface::getOption()` in views ([@phansys](https://github.com/phansys))
- [[#7032](https://github.com/sonata-project/SonataAdminBundle/pull/7032)] Update to AdminLTE 2.4.15 ([@core23](https://github.com/core23))

### Fixed
- [[#7085](https://github.com/sonata-project/SonataAdminBundle/pull/7085)] Fixed main sidebar toggling ([@jordisala1991](https://github.com/jordisala1991))
- [[#7079](https://github.com/sonata-project/SonataAdminBundle/pull/7079)] AddIdentifierToQuery phpdoc to reflect the fact that it shouldn't be called with an empty array of identifier ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#7080](https://github.com/sonata-project/SonataAdminBundle/pull/7080)] Using a non existing `Session` instance from the `Request` object at `AbstractAdmin::getListMode()` ([@phansys](https://github.com/phansys))
- [[#7021](https://github.com/sonata-project/SonataAdminBundle/pull/7021)] Fixed using the admin translation domain as default for `form_help` twig block ([@franmomu](https://github.com/franmomu))
- [[#7055](https://github.com/sonata-project/SonataAdminBundle/pull/7055)] Fixed using the hardcode `id` parameter instead of calling `AdminInterface::getIdParameter()` method ([@franmomu](https://github.com/franmomu))
- [[#7007](https://github.com/sonata-project/SonataAdminBundle/pull/7007)] Fixed using invalid values in `field_options` of filters ([@franmomu](https://github.com/franmomu))

## [3.96.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.95.0...3.96.0) - 2021-04-06
### Added
- [[#6992](https://github.com/sonata-project/SonataAdminBundle/pull/6992)] "or_group" filter option at `SearchHandler::search()`. ([@phansys](https://github.com/phansys))

### Changed
- [[#7008](https://github.com/sonata-project/SonataAdminBundle/pull/7008)] Names of variables in the skeleton ([@axzx](https://github.com/axzx))
- [[#7003](https://github.com/sonata-project/SonataAdminBundle/pull/7003)] Compiler Pass classes have been marked as `@internal`. ([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#7010](https://github.com/sonata-project/SonataAdminBundle/pull/7010)] Deprecated `Sonata\AdminBundle\Admin\AdminHelper` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7010](https://github.com/sonata-project/SonataAdminBundle/pull/7010)] Deprecated `Sonata\AdminBundle\SonataConfiguration` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Command\ExplainAdminCommand` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Command\GenerateObjectAclCommand` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Command\ListAdminCommand` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Command\SetupAclCommand` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Admin\BreadcrumbsBuilder` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Event\AdminEventExtension` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Filter\FilterFactory` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Filter\Persister\SessionFilterPersister` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Model\AuditManager` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Search\SearchHandler` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Templating\TemplateRegistry` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Translator\BCLabelTranslatorStrategy` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Translator\Extractor\AdminExtractor` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy` service alias. ([@franmomu](https://github.com/franmomu))
- [[#7001](https://github.com/sonata-project/SonataAdminBundle/pull/7001)] Deprecated `Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy` service alias. ([@franmomu](https://github.com/franmomu))
- [[#6998](https://github.com/sonata-project/SonataAdminBundle/pull/6998)] Deprecated `sonata.admin.validator.inline` service. ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#7002](https://github.com/sonata-project/SonataAdminBundle/pull/7002)] Fixed allowing avoid deprecation using `DatagridManagerInterface::getDefaultSortValues()`. ([@franmomu](https://github.com/franmomu))
- [[#6987](https://github.com/sonata-project/SonataAdminBundle/pull/6987)] Allow `TypeGuesserInterface::guess()` to return `null` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6988](https://github.com/sonata-project/SonataAdminBundle/pull/6988)] Fixed removing deprecations having audit readers tagged. ([@franmomu](https://github.com/franmomu))

### Removed
- [[#6997](https://github.com/sonata-project/SonataAdminBundle/pull/6997)] Extra calls to `Pager::getCurrentPageResults()`, `Pager::countResults()`, `Pager::getNbResults()` and `Pager::getResults()` made at `block_search_result.html.twig`. ([@phansys](https://github.com/phansys))

## [3.95.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.94.0...3.95.0) - 2021-03-29
### Added
- [[#6983](https://github.com/sonata-project/SonataAdminBundle/pull/6983)] Added `sonata.admin.audit_reader` tag for Audit readers. ([@franmomu](https://github.com/franmomu))
- [[#6965](https://github.com/sonata-project/SonataAdminBundle/pull/6965)] Added `FieldDescriptionInterface::describesAssociation`, `FieldDescriptionInterface::describesSingleValuedAssociation`, `FieldDescriptionInterface::describesCollectionValuedAssociation` to abstract the association mapping types. ([@franmomu](https://github.com/franmomu))
- [[#6965](https://github.com/sonata-project/SonataAdminBundle/pull/6965)] Added `AbstractFormContractor` base class to centralize association form option validation & defaults. ([@franmomu](https://github.com/franmomu))
- [[#6946](https://github.com/sonata-project/SonataAdminBundle/pull/6946)] Added `Sonata\AdminBundle\FieldDescription\BaseFieldDescription` class. ([@franmomu](https://github.com/franmomu))
- [[#6946](https://github.com/sonata-project/SonataAdminBundle/pull/6946)] Added `Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection` class. ([@franmomu](https://github.com/franmomu))
- [[#6946](https://github.com/sonata-project/SonataAdminBundle/pull/6946)] Added `Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface` interface. ([@franmomu](https://github.com/franmomu))
- [[#6946](https://github.com/sonata-project/SonataAdminBundle/pull/6946)] Added `Sonata\AdminBundle\FieldDescription\FieldDescriptionRegistryInterface` interface. ([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#6978](https://github.com/sonata-project/SonataAdminBundle/pull/6978)] Deprecated constructing `AdminPoolLoader` passing more than one argument. ([@franmomu](https://github.com/franmomu))
- [[#6978](https://github.com/sonata-project/SonataAdminBundle/pull/6978)] Deprecated `AdminPoolLoader` service alias. ([@franmomu](https://github.com/franmomu))
- [[#6983](https://github.com/sonata-project/SonataAdminBundle/pull/6983)] Deprecated not tagging an audit reader with the tag `sonata.admin.audit_reader`. ([@franmomu](https://github.com/franmomu))
- [[#6976](https://github.com/sonata-project/SonataAdminBundle/pull/6976)] Deprecated not registering filters as a service. ([@franmomu](https://github.com/franmomu))
- [[#6979](https://github.com/sonata-project/SonataAdminBundle/pull/6979)] `MenuBuilderInterface` interface. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6979](https://github.com/sonata-project/SonataAdminBundle/pull/6979)] `AbstractAdmin::buildSideMenu()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6979](https://github.com/sonata-project/SonataAdminBundle/pull/6979)] `AbstractAdmin::buildTabMenu()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6946](https://github.com/sonata-project/SonataAdminBundle/pull/6946)] Deprecated `Sonata\AdminBundle\Admin\BaseFieldDescription` class. ([@franmomu](https://github.com/franmomu))
- [[#6946](https://github.com/sonata-project/SonataAdminBundle/pull/6946)] Deprecated `Sonata\AdminBundle\Admin\FieldDescriptionCollection` class. ([@franmomu](https://github.com/franmomu))
- [[#6946](https://github.com/sonata-project/SonataAdminBundle/pull/6946)] Deprecated `Sonata\AdminBundle\Admin\FieldDescriptionInterface` interface. ([@franmomu](https://github.com/franmomu))
- [[#6946](https://github.com/sonata-project/SonataAdminBundle/pull/6946)] Deprecated `Sonata\AdminBundle\Admin\FieldDescriptionRegistryInterface` interface. ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#6944](https://github.com/sonata-project/SonataAdminBundle/pull/6944)] Optimize performance of checking is can access object in `AbstractAdmin::configureActionButtons()` ([@peter-gribanov](https://github.com/peter-gribanov))

## [3.94.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.93.0...3.94.0) - 2021-03-27
### Added
- [[#6960](https://github.com/sonata-project/SonataAdminBundle/pull/6960)] `AbstractAdmin::configureDashboardActions()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6960](https://github.com/sonata-project/SonataAdminBundle/pull/6960)] `AdminExtension::configureDashboardActions()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6960](https://github.com/sonata-project/SonataAdminBundle/pull/6960)] `AdminExtensionInterface::configureDashboardActions()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6957](https://github.com/sonata-project/SonataAdminBundle/pull/6957)] Added constant `ListMapper::NAME_ACTIONS` to use instead of `_action` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6957](https://github.com/sonata-project/SonataAdminBundle/pull/6957)] Added constant `ListMapper::NAME_SELECT` to use instead of `select` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6957](https://github.com/sonata-project/SonataAdminBundle/pull/6957)] Added constant `ListMapper::NAME_BATCH` to use instead of `batch` ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#6960](https://github.com/sonata-project/SonataAdminBundle/pull/6960)] `AbstractAdmin::configureDashboardButtons()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6960](https://github.com/sonata-project/SonataAdminBundle/pull/6960)] `AdminExtension::configureDashboardButtons()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6960](https://github.com/sonata-project/SonataAdminBundle/pull/6960)] `AdminExtensionInterface::configureDashboardButtons()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6957](https://github.com/sonata-project/SonataAdminBundle/pull/6957)] Relying on the code `_select` or `_batch` to determine if a field description is a `select` or a `batch` one. You should rely on the name `ListMapper::NAME_SELECT` or `ListMapper::NAME_BATCH` instead. ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#6967](https://github.com/sonata-project/SonataAdminBundle/pull/6967)] Return typehint of DataSourceInterface::createIterator ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6901](https://github.com/sonata-project/SonataAdminBundle/pull/6901)] Global search on admins with default filter parameters. ([@jorrit](https://github.com/jorrit))

### Removed
- [[#6968](https://github.com/sonata-project/SonataAdminBundle/pull/6968)] Remove the navigation role from nav. ([@axzx](https://github.com/axzx))

## [3.93.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.92.0...3.93.0) - 2021-03-15
### Added
- [[#6937](https://github.com/sonata-project/SonataAdminBundle/pull/6937)] `ModelManager::reverseTransform()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6939](https://github.com/sonata-project/SonataAdminBundle/pull/6939)] `AbstractAdmin::configureActionButtons()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6939](https://github.com/sonata-project/SonataAdminBundle/pull/6939)] `AdminExtensionInterface::configureActionButtons()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6939](https://github.com/sonata-project/SonataAdminBundle/pull/6939)] `AbstractAdminExtension::configureActionButtons()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#6937](https://github.com/sonata-project/SonataAdminBundle/pull/6937)] `ModelManager::modelReverseTransform()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6939](https://github.com/sonata-project/SonataAdminBundle/pull/6939)] Overriding `AbstractAdmin::getActionButtons()` ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.92.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.91.1...3.92.0) - 2021-03-13
### Added
- [[#6922](https://github.com/sonata-project/SonataAdminBundle/pull/6922)] Added extension point `AbstractAdmin::configureFilterParameters()` to configure/modify filter parameters ([@dmaicher](https://github.com/dmaicher))
- [[#6854](https://github.com/sonata-project/SonataAdminBundle/pull/6854)] Added `FieldDescriptionFactoryInterface` to create FieldDescription instances ([@franmomu](https://github.com/franmomu))
- [[#6854](https://github.com/sonata-project/SonataAdminBundle/pull/6854)] Added `TypeGuesserInterface` to guess the proper FieldDescription type based on its properties ([@franmomu](https://github.com/franmomu))
- [[#6913](https://github.com/sonata-project/SonataAdminBundle/pull/6913)] Route parameters "baseRevision" and "compareRevision" for `CRUDController::historyCompareRevisionsAction()` ([@phansys](https://github.com/phansys))

### Deprecated
- [[#6885](https://github.com/sonata-project/SonataAdminBundle/pull/6885)] `AbstractAdmin::buildForm()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6885](https://github.com/sonata-project/SonataAdminBundle/pull/6885)] `AbstractAdmin::buildDatagrid()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6885](https://github.com/sonata-project/SonataAdminBundle/pull/6885)] `AbstractAdmin::buildShow()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6885](https://github.com/sonata-project/SonataAdminBundle/pull/6885)] `AbstractAdmin::buildList()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6885](https://github.com/sonata-project/SonataAdminBundle/pull/6885)] `AbstractAdmin::canAccessObject()` in favor of `AbstractAdmin::hasAccess()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6885](https://github.com/sonata-project/SonataAdminBundle/pull/6885)] `AdminInterface::canAccessObject()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6885](https://github.com/sonata-project/SonataAdminBundle/pull/6885)] `AdminInterface::configureActionButtons()` and `AbstractAdmin::configureActionButtons()` will become protected ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6854](https://github.com/sonata-project/SonataAdminBundle/pull/6854)] Deprecated `ModelManagerInterface::getNewFieldDescriptionInstance()` ([@franmomu](https://github.com/franmomu))
- [[#6913](https://github.com/sonata-project/SonataAdminBundle/pull/6913)] Route parameters "base_revision" and "compare_revision" for `CRUDController::historyCompareRevisionsAction()` ([@phansys](https://github.com/phansys))

### Fixed
- [[#6928](https://github.com/sonata-project/SonataAdminBundle/pull/6928)] Fix wrong phpstan param on `FilterInterface::filter() method` ([@core23](https://github.com/core23))
- [[#6920](https://github.com/sonata-project/SonataAdminBundle/pull/6920)] Issue "Right side of && is always true" detected by PHPStan ([@phansys](https://github.com/phansys))

## [3.91.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.91.0...3.91.1) - 2021-03-06
### Added
- [[#6911](https://github.com/sonata-project/SonataAdminBundle/pull/6911)] Added `AbstractAdminExtension::configurePersistentParameters()` method stub ([@core23](https://github.com/core23))

### Fixed
- [[#6902](https://github.com/sonata-project/SonataAdminBundle/pull/6902)] Possibility to access nonexistent parent objects from child admins ([@phansys](https://github.com/phansys))

## [3.91.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.90.0...3.91.0) - 2021-03-02
### Deprecated
- [[#6878](https://github.com/sonata-project/SonataAdminBundle/pull/6878)] Passing a fieldDescription option `name` ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#6895](https://github.com/sonata-project/SonataAdminBundle/pull/6895)] Fix computing changed filter values ([@kirya-dev](https://github.com/kirya-dev))

## [3.90.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.89.1...3.90.0) - 2021-02-25
### Added
- [[#6877](https://github.com/sonata-project/SonataAdminBundle/pull/6877)] Added `AbstractAdmin::mergeParameters()` to merge parameters but replace them when it's a subarray. ([@willemverspyck](https://github.com/willemverspyck))
- [[#6864](https://github.com/sonata-project/SonataAdminBundle/pull/6864)] Added `AbstractAdmin::alterObject()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6882](https://github.com/sonata-project/SonataAdminBundle/pull/6882)] Add generic information for child admins ([@core23](https://github.com/core23))
- [[#6883](https://github.com/sonata-project/SonataAdminBundle/pull/6883)] Added `AbstractAdmin::createNewInstance()` to allow the user to customize how to create their objects. ([@franmomu](https://github.com/franmomu))
- [[#6873](https://github.com/sonata-project/SonataAdminBundle/pull/6873)] `AbstractAdmin::configurePersistentParameters()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6873](https://github.com/sonata-project/SonataAdminBundle/pull/6873)] `AdminExtensionInterface::configurePersistentParameters()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#6864](https://github.com/sonata-project/SonataAdminBundle/pull/6864)] Overriding `AbstractAdmin::getObject()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6869](https://github.com/sonata-project/SonataAdminBundle/pull/6869)] `LifecycleHookProviderInterface::preUpdate()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6869](https://github.com/sonata-project/SonataAdminBundle/pull/6869)] `LifecycleHookProviderInterface::preCreate()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6869](https://github.com/sonata-project/SonataAdminBundle/pull/6869)] `LifecycleHookProviderInterface::preDelete()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6869](https://github.com/sonata-project/SonataAdminBundle/pull/6869)] `LifecycleHookProviderInterface::postUpdate()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6869](https://github.com/sonata-project/SonataAdminBundle/pull/6869)] `LifecycleHookProviderInterface::postCreate()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6869](https://github.com/sonata-project/SonataAdminBundle/pull/6869)] `LifecycleHookProviderInterface::postDelete()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6883](https://github.com/sonata-project/SonataAdminBundle/pull/6883)] Deprecated `ModelManagerInterface::getModelInstance()` method in favor of `AbstractAdmin::createNewInstance()`. ([@franmomu](https://github.com/franmomu))
- [[#6873](https://github.com/sonata-project/SonataAdminBundle/pull/6873)] Overriding `AbstractAdmin::getPersistentParameters()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6873](https://github.com/sonata-project/SonataAdminBundle/pull/6873)] `AdminExtensionInterface::getPersistentParameters()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#6877](https://github.com/sonata-project/SonataAdminBundle/pull/6877)] Fix merge problem for filters with array value ([@willemverspyck](https://github.com/willemverspyck))
- [[#6889](https://github.com/sonata-project/SonataAdminBundle/pull/6889)] Show XHR errors on "one to many" and "many to many" forms ([@phansys](https://github.com/phansys))
- [[#6411](https://github.com/sonata-project/SonataAdminBundle/pull/6411)] Prevent false deprecation notice when accessing an item in a child admin that has a Many to Many relationship with the parent item. ([@jorrit](https://github.com/jorrit))
- [[#6871](https://github.com/sonata-project/SonataAdminBundle/pull/6871)] Fix lost filter value when only type submitted ([@kirya-dev](https://github.com/kirya-dev))
- [[#6871](https://github.com/sonata-project/SonataAdminBundle/pull/6871)] Allow submit empty value when default filter non empty ([@kirya-dev](https://github.com/kirya-dev))
- [[#6871](https://github.com/sonata-project/SonataAdminBundle/pull/6871)] Invisible disabling filters (using remove name) ([@kirya-dev](https://github.com/kirya-dev))

## [3.89.1](https://github.com/sonata-project/SonataAdminBundle/compare/3.89.0...3.89.1) - 2021-02-16
### Fixed
- [[#6867](https://github.com/sonata-project/SonataAdminBundle/pull/6867)] Handle case when `attachAdminClass()` does not find an admin to attach ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.89.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.88.0...3.89.0) - 2021-02-16
### Added
- [[#6821](https://github.com/sonata-project/SonataAdminBundle/pull/6821)] `Pool::getAdminByFieldDescription()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6821](https://github.com/sonata-project/SonataAdminBundle/pull/6821)] `AdminClassNotFoundException` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6821](https://github.com/sonata-project/SonataAdminBundle/pull/6821)] `AdminCodeNotFoundException` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6821](https://github.com/sonata-project/SonataAdminBundle/pull/6821)] `TooManyAdminClassException` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6821](https://github.com/sonata-project/SonataAdminBundle/pull/6821)] A tag attribute `default: true` for the tag `sonata.admin` to define a default admin for a specific class ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6776](https://github.com/sonata-project/SonataAdminBundle/pull/6776)] Added `RouteCollection::getRouteName` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6776](https://github.com/sonata-project/SonataAdminBundle/pull/6776)] Added `RouteCollectionInterface::getRouteName` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6814](https://github.com/sonata-project/SonataAdminBundle/pull/6814)] Added some `Sonata\AdminBundle\Admin\AbstractAdmin::configureFormOptions()` to do great stuff. ([@rgrassian](https://github.com/rgrassian))
- [[#6817](https://github.com/sonata-project/SonataAdminBundle/pull/6817)] A block `table` to override the whole datagrid table in the `base_list.html.twig` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6789](https://github.com/sonata-project/SonataAdminBundle/pull/6789)] A callback with a possibility to modify each individual item in the response. ([@keshancs](https://github.com/keshancs))
- [[#6789](https://github.com/sonata-project/SonataAdminBundle/pull/6789)] A way to customize any autocomplete select2 properties in js via adding sonata_type_model_autocomplete_select2_options_js block before initializing select2 element because select2 doesn't allow modification of several options after it has been initialized, you can only destroy it and initialize again from scratch. ([@keshancs](https://github.com/keshancs))
- [[#6789](https://github.com/sonata-project/SonataAdminBundle/pull/6789)] Set error bubbling to false - prevent passing error to parent form elements, which occurs by default for compound form types. ([@keshancs](https://github.com/keshancs))
- [[#6837](https://github.com/sonata-project/SonataAdminBundle/pull/6837)] `FieldDescription` `accessor` option ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6850](https://github.com/sonata-project/SonataAdminBundle/pull/6850)] Added `AbstractAdmin::alterNewInstance()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#6789](https://github.com/sonata-project/SonataAdminBundle/pull/6789)] The autocomplete item list is now independent of filters set in the corresponding list via removing its filters. Before these changes, it returned only items that matched the selected filter. ([@keshancs](https://github.com/keshancs))
- [[#6844](https://github.com/sonata-project/SonataAdminBundle/pull/6844)] From `DatagridMapper::add($name, $type, $filterOptions = [], $fieldType = null, $fieldOptions = null, $fieldDescriptionOptions = [])` to `DatagridMapper::add($name, $type, $filterOptions = [], $fieldDescriptionOptions = [])` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6827](https://github.com/sonata-project/SonataAdminBundle/pull/6827)] `ProxyQueryInterface::execute(array $params = [], ?int $hydrationMode = null)` signature to `ProxyQueryInterface::execute()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#6821](https://github.com/sonata-project/SonataAdminBundle/pull/6821)] `Pool::hasSingleAdminByClass()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6798](https://github.com/sonata-project/SonataAdminBundle/pull/6798)] FieldDescription `parameters` option. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6814](https://github.com/sonata-project/SonataAdminBundle/pull/6814)] Deprecated `Sonata\AdminBundle\Admin\AbstractAdmin::formOptions` property. ([@rgrassian](https://github.com/rgrassian))
- [[#6837](https://github.com/sonata-project/SonataAdminBundle/pull/6837)] `FieldDescription` `code` option ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6850](https://github.com/sonata-project/SonataAdminBundle/pull/6850)] Overriding `AbstractAdmin::getNewInstance()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6794](https://github.com/sonata-project/SonataAdminBundle/pull/6794)] Deprecated `Pool::setTemplateRegistry()` method. ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#6856](https://github.com/sonata-project/SonataAdminBundle/pull/6856)] Handling of JSON responses at `Admin.setup_xeditable()`. ([@phansys](https://github.com/phansys))
- [[#6783](https://github.com/sonata-project/SonataAdminBundle/pull/6783)] Fixed phpdoc for `Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer::$property` which can be `string[]` as well ([@dmaicher](https://github.com/dmaicher))
- [[#6818](https://github.com/sonata-project/SonataAdminBundle/pull/6818)] Stop displaying the filter dropdown with no usable filters ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6824](https://github.com/sonata-project/SonataAdminBundle/pull/6824)] Do not add a '-' between the export button and the pager results if there is no pager results. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6778](https://github.com/sonata-project/SonataAdminBundle/pull/6778)] How `AdminHelper::appendFormFieldElement` renders nested forms. ([@vladyslavstartsev](https://github.com/vladyslavstartsev))
- [[#6784](https://github.com/sonata-project/SonataAdminBundle/pull/6784)] Default "show" template for type "float" from SonataIntlBundle. ([@phansys](https://github.com/phansys))

## [3.88.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.87.0...3.88.0) - 2021-01-18
### Added
- [[#6767](https://github.com/sonata-project/SonataAdminBundle/pull/6767)] Allow PHP 8 ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6766](https://github.com/sonata-project/SonataAdminBundle/pull/6766)] Added `Sonata\AdminBundle\Templating\MutableTemplateRegistryAwareInterface::setTemplate()` ([@wbloszyk](https://github.com/wbloszyk))
- [[#6766](https://github.com/sonata-project/SonataAdminBundle/pull/6766)] Added `Sonata\AdminBundle\Templating\MutableTemplateRegistryAwareInterface::setTemplates()` ([@wbloszyk](https://github.com/wbloszyk))
- [[#6769](https://github.com/sonata-project/SonataAdminBundle/pull/6769)] Translation file for `bs` (Bosnian) ([@tambait](https://github.com/tambait))
- [[#6769](https://github.com/sonata-project/SonataAdminBundle/pull/6769)] Translation files for `sr_Latn` & `sr_Cyrl` (Serbian Latin & Cyrillic script) ([@tambait](https://github.com/tambait))

### Fixed
- [[#6772](https://github.com/sonata-project/SonataAdminBundle/pull/6772)] Fixed using admin maker command having a default controller service ([@franmomu](https://github.com/franmomu))

### Removed
- [[#6766](https://github.com/sonata-project/SonataAdminBundle/pull/6766)] Removed deprecations for `Sonata\AdminBundle\Admin\AbstractAdmin::setTemplate()` ([@wbloszyk](https://github.com/wbloszyk))
- [[#6766](https://github.com/sonata-project/SonataAdminBundle/pull/6766)] Removed deprecations for `Sonata\AdminBundle\Admin\AbstractAdmin::setTemplates()` ([@wbloszyk](https://github.com/wbloszyk))

## [3.87.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.86.0...3.87.0) - 2021-01-12
### Added
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Added `Sonata\AdminBundle\Twig\Extension\SecurityExtension` ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Added `Sonata\AdminBundle\Twig\Extension\CanonicalizeExtension` ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Added `Sonata\AdminBundle\Twig\Extension\XEditableExtension` ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Added `Sonata\AdminBundle\Twig\Extension\RenderElementExtension` ([@tambait](https://github.com/tambait))
- [[#6760](https://github.com/sonata-project/SonataAdminBundle/pull/6760)] Added `PagerInterface::getCurrentPageResults()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6759](https://github.com/sonata-project/SonataAdminBundle/pull/6759)] Add generics to `PagerInterface`. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6729](https://github.com/sonata-project/SonataAdminBundle/pull/6729)] Added `default_controller` to configure the default controlled used by the admins. ([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::MOMENT_UNSUPPORTED_LOCALES` constant ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::setXEditableTypeMapping()` method ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::getXEditableType()` method ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::getXEditableChoices()` method ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::getCanonicalizedLocaleForMoment()` method ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::getCanonicalizedLocaleForSelect2()` method ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::isGrantedAffirmative()` method ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::renderListElement()` method ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::renderViewElement()` method ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::renderViewElementCompare()` method ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::renderRelationElement()` method ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::getTemplate()` method ([@tambait](https://github.com/tambait))
- [[#6676](https://github.com/sonata-project/SonataAdminBundle/pull/6676)] Deprecated `SonataAdminExtension::getTemplateRegistry()` method ([@tambait](https://github.com/tambait))
- [[#6734](https://github.com/sonata-project/SonataAdminBundle/pull/6734)] Deprecate `DateOperatorType::TYPE_NULL` and `DateOperatorType::TYPE_NOT_NULL` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6760](https://github.com/sonata-project/SonataAdminBundle/pull/6760)] Added `PagerInterface::getResults()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#6764](https://github.com/sonata-project/SonataAdminBundle/pull/6764)] Added missing phpdoc type declarations. ([@franmomu](https://github.com/franmomu))
- [[#6758](https://github.com/sonata-project/SonataAdminBundle/pull/6758)] Fixed `Pool` definition arguments. ([@franmomu](https://github.com/franmomu))

## [3.86.0](https://github.com/sonata-project/SonataAdminBundle/compare/3.85.0...3.86.0) - 2021-01-09
### Added
- [[#6732](https://github.com/sonata-project/SonataAdminBundle/pull/6732)] Added `PagerInterface::countResults` ([@dmaicher](https://github.com/dmaicher))
- [[#6700](https://github.com/sonata-project/SonataAdminBundle/pull/6700)] Added `AbstractAdmin::getDefaultFilterParameters()` to get default filter parameters ([@kirya-dev](https://github.com/kirya-dev))
- [[#6746](https://github.com/sonata-project/SonataAdminBundle/pull/6746)] `Added BaseGroupMapper::removeGroup()` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#6746](https://github.com/sonata-project/SonataAdminBundle/pull/6746)] `Added BaseGroupMapper::removeTab()` ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#6700](https://github.com/sonata-project/SonataAdminBundle/pull/6700)] Only changed (non default, non empty) filters will be submitted ([@kirya-dev](https://github.com/kirya-dev))

### Deprecated
- [[#6732](https://github.com/sonata-project/SonataAdminBundle/pull/6732)] `PagerInterface::getNbResults()` in favour of `countResults()` ([@dmaicher](https://github.com/dmaicher))
- [[#6732](https://github.com/sonata-project/SonataAdminBundle/pull/6732)] `PagerInterface::setNbResults(...)` ([@dmaicher](https://github.com/dmaicher))
- [[#6732](https://github.com/sonata-project/SonataAdminBundle/pull/6732)] `PagerInterface::$nbResults` ([@dmaicher](https://github.com/dmaicher))
- [[#6735](https://github.com/sonata-project/SonataAdminBundle/pull/6735)] Deprecated `Pool::setAdminServiceIds` in favor of passing service ids as argument 2 to the constructor ([@franmomu](https://github.com/franmomu))
- [[#6735](https://github.com/sonata-project/SonataAdminBundle/pull/6735)] Deprecated `Pool::setAdminGroups` in favor of passing service ids as argument 3 to the constructor ([@franmomu](https://github.com/franmomu))
- [[#6735](https://github.com/sonata-project/SonataAdminBundle/pull/6735)] Deprecated `Pool::setAdminClasses` in favor of passing service ids as argument 4 to the constructor ([@franmomu](https://github.com/franmomu))
- [[#6751](https://github.com/sonata-project/SonataAdminBundle/pull/6751)] Deprecated `CRUDController::configure()` method, to configure the associated admin, you should call `CRUDController::configureAdmin()` instead ([@franmomu](https://github.com/franmomu))
- [[#6752](https://github.com/sonata-project/SonataAdminBundle/pull/6752)] Deprecated `TemplateRegistryInterface::TYPE_*` constants, they have been moved to `FieldDescriptionInterface` ([@franmomu](https://github.com/franmomu))

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
