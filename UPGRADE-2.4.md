UPGRADE FROM 2.3 to 2.4
=======================

### Dependencies

You will need to follow the dependencies upgrade instructions.

## Datagrid builders

If you have implemented a custom datagrid builder, you must adapt the signature
of its `addFilter` method to match the one in `DatagridBuilderInterface` again.

## sonata_type_model_autocomplete
CSS class ``sonata-autocomplete-dropdown-item`` is not automatically added to
dropdown autocomplete item in ``sonata_type_model_autocomplete``, use option
``dropdown_item_css_class`` to set the CSS class of dropdown item.

