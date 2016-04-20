UPGRADE FROM 2.x to 3.0
=======================

### Dependencies

You will need to follow the dependencies upgrade instructions.

## Datagrid builders

If you have implemented a custom datagrid builder, you must adapt the signature of its `addFilter` method to match the one in `DatagridBuilderInterface` again.

## sonata_type_model_autocomplete
CSS class ``sonata-autocomplete-dropdown-item`` is not automatically added to dropdown autocomplete item in ``sonata_type_model_autocomplete``, use option ``dropdown_item_css_class`` to set the CSS class of dropdown item.

## Standard Layout
``sonata_wrapper`` block was moved and is now inside the ``.wrapper`` div of admin lte theme.

## ErrorElement

The inline validation has been migrating to CoreBundle. Just rename ``Sonata\AdminBundle\Validator\ErrorElement`` to ``Sonata\CoreBundle\Validator\ErrorElement``

## AdminLTE 2

AdminLTE version 2 has been integrated, this should work out of the box if you havn't change templates. If not you can review the upgrade guide here : [http://almsaeedstudio.com/themes/AdminLTE/documentation/index.html#upgrade](http://almsaeedstudio.com/themes/AdminLTE/documentation/index.html#upgrade)

## AclSecurityHandler

In order to fix deprecated issue by spiting `SecurityContextInterface`, `AclSecurityHandler` constructor got a new argument.

## AdminPoolLoader

If you're using a custom implementation of `sonata.admin.route_loader` service, make sure to provide an array as 2nd argument since the type for this argument is now hinted to `array`.

## LegacyModelsToArrayTransformer

The ``ModelsToArrayTransformer`` has been renamed to ``LegacyModelsToArrayTransformer``. ``ModelsToArrayTransformer`` should be only be used with SF2.7+
