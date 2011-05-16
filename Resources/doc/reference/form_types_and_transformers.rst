Form types and data transformers
================================

The AdminBundle is shipped with custom form types and data transfomers in order
to handle the diffent model's workflows and lifecycle.

Form types
----------

    - ``sonata_type_admin`` : this type is linked to an Admin class and the field construction is
      delegated to an Admin class.
    - ``sonata_type_collection`` : this type works like the native ``CollectionType`` but contains two extra
      features : the data layer is abstracted to work with any implemented layer and a delete option is added
      so a collection entry can be deleted.
    - ``sonata_type_model`` : this type works like the native ``EntityType`` but this internal is abstracted
      to work with any implemented layer.


Datatransformer
---------------

    - ``ArrayToModelTransformer`` : transform an array to an object
    - ``ModelsToArrayTransformer`` : transform a collection of array into a collection of object
    - ``ModelToIdTransformater`` : transform an ``id`` into an object