UPGRADE FROM 4.x to 5.0
=======================

## Deprecations

All the deprecated code introduced on 4.x is removed on 5.0.

Please read [4.x](https://github.com/sonata-project/SonataAdminBundle/tree/4.x) upgrade guides for more information.

See also the [diff code](https://github.com/sonata-project/SonataAdminBundle/compare/4.x...5.x).

## AdminExtension
If you have implemented a custom admin extension, which does not extend the `AbstractAdminExtension` then you should add the following methods which were newly introduced in the `AdminExtensionInterface`:
 * `preBatchAction`

