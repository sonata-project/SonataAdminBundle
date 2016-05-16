# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [4.x]
### Added
- Add the ``configureActionButtons`` method to the AdminInterface
- Add the ``configureActionButtons`` method to the AdminExtensionInterface
- Add the ``configureBatchActions`` method to the AdminExtensionInterface
- Added the `getAccessMapping` method to the AdminExtensionInterface

### Removed
- Removed BC handler for deprecated `view` `_action`

## [3.x]
### Added
- Added AbstractAdmin, replacing Admin

### Fixed
- Fix detection of path when using nested properties with underscores in `AdminHelper:getElementAccessPath` method

### Changed
- Updated AdminLTE theme to version 2.3.3

### Deprecated
- Deprecated `BaseFieldDescription::camelize()`
- Deprecated `AdminHelper::camelize()`
- Deprecated `Admin` class

### Fixed
- Fixed bad rendering on datetime field with `single_text` widget for date and time

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
- Text from Admin's toString method is escaped for html output before adding in flash message to prevent possible XSS vulnerability.

### Removed
- Remove `btn-outline` from doctrine-orm-admin form actions buttons
