UPGRADE FROM 2.3 to 2.4
=======================

### Dependencies

You will need to follow the dependencies upgrade instructions.

## Datagrid builders

If you have implemented a custom datagrid builder, you must adapt the signature
of its `addFilter` method to match the one in `DatagridBuilderInterface` again.
