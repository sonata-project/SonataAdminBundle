Field Types
===========

List and Show Actions
---------------------

There are many field types that can be used in the list action or show action :

* array: display value from an array
* boolean: display a green or red picture depend on the boolean value
* date: display a formatted date
* datetime: display a formatted date
* text: display a text
* trans: translate the value with a provided ``catalogue`` option
* string: display a text
* decimal: display a number
* currency: display a number with a provided ``currency`` option
* percent: display a percentage

.. note::

    If the ``SonataIntlBundle`` is installed in the project some template types
    will be changed to use localized information.


More types might be provided depends on the persistency layer defined. Please refer to there
related documentations.