UPGRADE FROM 3.x to 4.0
=======================

## Deprecations

All the deprecated code introduced on 3.x is removed on 4.0.

Please read [3.x](https://github.com/sonata-project/SonataAdminBundle/tree/3.x) upgrade guides for more information.

See also the [diff code](https://github.com/sonata-project/SonataAdminBundle/compare/3.x...4.0.0).

## Admin
If you have implemented a custom admin, you must adapt the signature of the following new methods to match the one in `AdminInterface` again:
 * `configureActionButtons`

## AdminExtension
If you have implemented a custom admin extension, you must adapt the signature of the following new methods to match the one in `AdminExtensionInterface` again:
 * `configureActionButtons`
 * `configureBatchActions`
 * `getAccessMapping`

## SonataAdminExtension
The Twig filters that come with the bundle will no longer load a default template when used with a missing template.
The `sonata_admin` twig extension is now final. You may no longer extend it.

## Templates & Scripts
If you extended the `base_list.html.twig` (or any child) template or the `Admin.js` file, you should replace the following attributes:
  * `filter-container` with `data-filter-container`
  * `filter-target` with `data-filter-target`
  * `objectId` with `data-object-id`
  * `sonata-filter` with `data-sonata-filter`
