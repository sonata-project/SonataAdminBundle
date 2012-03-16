Field Types
===========

List and Show Actions
---------------------

There are many field types that can be used in the list action or show action :

* array: display value from an array
* boolean: display a green or red picture dependant on the boolean value, this type accepts an ``editable``
  parameter to edit the value from within the list or the show actions
* date: display a formatted date
* datetime: display a formatted date and time
* text: display a text
* trans: translate the value with a provided ``catalogue`` option
* string: display a text
* decimal: display a number
* currency: display a number with a provided ``currency`` option
* percent: display a percentage

.. note::

    If the ``SonataIntlBundle`` is installed in the project some template types
    will be changed to use localized information.


More types might be provided based on the persistency layer defined. Please refer to their
related documentations.