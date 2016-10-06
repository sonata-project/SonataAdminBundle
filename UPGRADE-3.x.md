UPGRADE 3.x
===========

UPGRADE FROM 3.8 to 3.9
=======================

## Deprecated admin translator

The `$translator` property and the corresponding methods (`setTranslator`, `getTranslator`, `trans` and `transChoice`) in `AbstractAdmin` are deprecated.
Please use `CRUDController::trans` or twig templates instead.

UPGRADE FROM 3.4 to 3.5
=======================

## Deprecated injection of container to GlobalVariables

The `$container` property in `Twig/GlobalVariables` is deprecated.

## Deprecated ModelTypeList for rename

The `Sonata\AdminBundle\Form\Type\ModelTypeList` class is now deprecated.

Use `Sonata\AdminBundle\Form\Type\ModelListType` instead.

### Tests

All files under the ``Tests`` directory are now correctly handled as internal test classes. 
You can't extend them anymore, because they are only loaded when running internal tests. 
More information can be found in the [composer docs](https://getcomposer.org/doc/04-schema.md#autoload-dev).

UPGRADE FROM 3.2 to 3.3
=======================

## Deprecated AbstractAdmin method argument

The `$context` argument is deprecated and will be removed.
Instead of relying on it (and using a `switch` statement),
rely on an abstraction, and inject different implementations of this abstraction in different actions.
Here is [an example](https://github.com/sonata-project/SonataAdminBundle/pull/3247#issuecomment-217744025).

UPGRADE FROM 3.1 to 3.2
=======================

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

## Deprecated AbstractAdmin methods
- `buildBreacrumbs` is deprecated, and no replacement is given, it will become an internal method.
- `getBreadcrumbs` is deprecated in favor of the homonym method of the `sonata.admin.breadcrumbs_builder` service.
- The breadcrumbs builder accessors are deprecated,
the `sonata.admin.breadcrumbs_builder` service should be used directly instead.
