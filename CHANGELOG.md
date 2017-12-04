# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

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
