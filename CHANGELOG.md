# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

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
