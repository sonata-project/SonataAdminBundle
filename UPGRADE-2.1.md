UPGRADE FROM 2.0 to 2.1
=======================

** Work In Progress - Please do not use this code in production **

### Form

  * Due to some refactoring in the Form Component, some types definition have been changed
    and new ones have been introduces:

      * sonata_type_model : this type now only render a standard select widget or a list
        widget (if multiple option is set)

      * sonata_type_model_list : this type replaces the option ``edit = list`` provided as
        a 4th arguments on the ``sonata_type_model``

