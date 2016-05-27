UPGRADE 3.x
===========

## Deprecated override of AbstractAdmin::getBatchActions

Since `AbstractAdmin::configureBatchActions` is present, you should not override `AbstractAdmin::getBatchActions`.

This method will be final in 4.0.

## Backward compatibility break for AbstractAdmin::getSubject()

Now `AbstractAdmin::getSubject()` return `null` or `object` of subject entity. Previously,
`AbstractAdmin::getSubject()` may return `false` if entity identifier not match regexp `/^[0-9A-Fa-f\-]+$/`.

UPGRADE FROM 3.0 to 3.1
=======================

## Deprecated Admin class

The `Admin` class is deprecated. Use `AbstractAdmin` instead.

## Deprecated AdminExtension class

The `AdminExtension` class is deprecated. Use `AbstractAdminExtension` instead.

## Deprecated template fallback mechanism

The Twig extension method that fallback to a default template when the specified one does not exist.
You can no longer rely on that and should always specify templates that exist.
