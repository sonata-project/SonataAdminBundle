UPGRADE 3.x
===========

## Deprecated AbstractAdmin::setBaseCodeRoute() and AbstractAdmin::baseCodeRoute property

The `AbstractAdmin::baseCodeRoute` property is no longer supported.
Please use the `AbstractAdmin::getBaseCodeRoute()` method instead.

The `AbstractAdmin::setBaseCodeRoute()` method is no longer supported.
There is no replacement for this method.
You can still use the `AbstractAdmin::setCode()` method to set the code
of an admin.

UPGRADE FROM 3.20 to 3.21
=========================

## Deprecated ModelChoiceList class in favor of ModelChoiceLoader

The `ModelChoiceList` class is no longer supported. Please use the `ModelChoiceLoader` class instead.

The `ModelChoiceList` is usually used on the [`choice_list`](http://symfony.com/doc/2.8/reference/forms/types/choice.html#choice-list)
option of a `FormBuilder`. The `ModelChoiceLoader` class must be replaced on the
[`choice_loader`](http://symfony.com/doc/3.3/reference/forms/types/choice.html#choice-loader) option.

UPGRADE FROM 3.13 to 3.14
=========================

## Deprecated automatic annotation registration with JMSDiExtraBundle

Starting with version 4.0, SonataAdminBundle will no longer register
annotations with JMSDiExtraBundle automatically. Please add the following to
your config.yml to register the annotations yourself:


```yaml
jms_di_extra:
    annotation_patterns:
        - JMS\DiExtraBundle\Annotation
        - Sonata\AdminBundle\Annotation
```

### Exporter service and class

The `sonata.admin.exporter` is deprecated in favor of the `sonata.exporter.exporter` service.
To make this service available, you have to install `sonata-project.exporter` ^1.7
and enable the bundle as described in the documentation.

UPGRADE FROM 3.11 to 3.12
=========================

## Deprecated ModelsToArrayTransformer::$choiceList property

When instantiating a ModelsToArrayTransformer object, please use the 2 parameter signature ($modelManager, $class).

UPGRADE FROM 3.10 to 3.11
=========================

## Deprecated Pager::getFirstIndice() and Pager::getLastIndice()

Please use `Pager::getFirstIndex()` and `Pager::getLastIndex()` instead!

UPGRADE FROM 3.9 to 3.10
========================

## Deprecated passing no 3rd argument to GroupMenuProvider

Passing no 3rd argument to `Menu\Provider\GroupMenuProvider` is deprecated.
Pass `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface` as 3rd argument.

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
