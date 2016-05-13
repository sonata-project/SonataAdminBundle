UPGRADE FROM 2.0 to 2.1
=======================

### Form

  * Due to refactoring in the Form Component, some type definitions have been changed and 
    new ones have been introduced:

      * sonata_type_model : this type now only renders a standard select widget or a list
        widget (if the `multiple` option is set)

      * sonata_type_model_list : this type replaces the option `edit = list` provided as
        a 4th argument on the `sonata_type_model`
