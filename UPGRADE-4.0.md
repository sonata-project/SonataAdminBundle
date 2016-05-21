UPGRADE FROM 3.x to 4.0
=======================

## Deprecations

All the deprecated code introduced on 3.x is removed on 4.0.

Please read [3.x](https://github.com/sonata-project/SonataAdminBundle/tree/3.x) upgrade guides for more information.

See also the [diff code](https://github.com/sonata-project/SonataAdminBundle/compare/3.x...4.0.0).

## Admin
If you have implemented a custom admin, you must adapt the signature of the following new methods to match the one in `AdminInterface` again:
 * `hasAccess`
 * `configureActionButtons`
 * `getExportFields`
 * `setTemplates`
 * `setTemplate`
 * `getTemplates`
 * `getClassnameLabel`
 * `getPersistentParameter`
 * `preValidate`
 * `getSubClasses`
 * `addSubClass`
 * `getDashboardActions`
 * `getActionButtons`
 * `isCurrentRoute`

## AdminExtension
If you have implemented a custom admin extension, you must adapt the signature of the following new methods to match the one in `AdminExtensionInterface` again:
 * `configureActionButtons`
 * `configureBatchActions`
 * `getAccessMapping`

## SonataAdminExtension
The Twig filters that come with the bundle will no longer load a default template when used with a missing template.
The `sonata_admin` twig extension is now final. You may no longer extend it.
