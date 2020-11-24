# Change Log

This file will get merged with CHANGELOG.md when releasing 4.0.0

## [4.0.0]

### Added
- Add the `hasAccess` method to the AdminInterface
- Add the `getExportFields` method to the AdminInterface
- Add the `setTemplates` method to the AdminInterface
- Add the `setTemplate` method to the AdminInterface
- Add the `getTemplates` method to the AdminInterface
- Add the `getClassnameLabel` method to the AdminInterface
- Add the `getPersistentParameter` method to the AdminInterface
- Add the `preValidate` method to the AdminInterface
- Add the `getSubClasses` method to the AdminInterface
- Add the `addSubClass` method to the AdminInterface
- Add the `getDashboardActions` method to the AdminInterface
- Add the `getActionButtons` method to the AdminInterface
- Add the ``configureActionButtons`` method to the AdminExtensionInterface
- Add the ``configureBatchActions`` method to the AdminExtensionInterface
- Added the `getAccessMapping` method to the AdminExtensionInterface
- Add the `isCurrentRoute` method to the AdminInterface

### Changed
- `AbstractAdmin::configureActionButtons` method is now protected
- `AbstractAdmin::getActionButtons` is now final
- `AbstractAdmin::configure` method is now protected
- `AbstractAdmin::buildDatagrid` method is now private
- `AbstractAdmin::urlize` method is now protected and final
- `AbstractAdmin::defineFormBuilder` method is now private
- `AbstractAdmin::configureActionButtons` method signature has changed
- Moved default buttons from `AbstractAdmin::configureActionButtons` to `AbstractAdmin::getActionButtons`
- `AbstractAdmin::getBatchActions` is now final

### Removed
- Removed BC handler for deprecated `view` `_action`
- The fallback mechanism that loads a default template when the template
specified in a field description cannot be found was removed.
- The Sonata Twig extension has been made final, you may no longer extend it to
  build your own extension (which is deprecated by Twig anyway)
- Public access to the translator strategy services has been removed, namely:
  - `sonata.admin.label.strategy.bc`
  - `sonata.admin.label.strategy.form_component`
  - `sonata.admin.label.strategy.native`
  - `sonata.admin.label.strategy.noop`
  - `sonata.admin.label.strategy.underscore`
- Removed deprecated `AbstractAdmin::buildSideMenu` method
- `AdminInterface::configure` was removed
