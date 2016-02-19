UPGRADE FROM 3.x to 4.0
=======================

## Admin
If you have implemented a custom admin, you must adapt the signature of the following new methods to match the one in `AdminInterface` again:
 * `configureActionButtons`

## AdminExtension
If you have implemented a custom admin extension, you must adapt the signature of the following new methods to match the one in `AdminExtensionInterface` again:
 * `configureActionButtons`
