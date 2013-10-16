Troubleshooting
===============

The toString method
-------------------

Sometimes the bundle needs to display your model objects, in order to do it, objects are converted to string by using the `__toString`_ magic method.
Take care to never return anything else than a string in this method.
For example, if your method looks like that :

.. code-block:: php

    public function __toString()
    {
        return $this->getTitle();
    }


You can't be sure your object will *always* have a title when the bundle will want to convert it to a string.
So in order to avoid any fatal error, you must return an empty string (or anything you prefer) for when the title is missing, like this :

.. code-block:: php

    public function __toString()
    {
        return $this->getTitle() ?: '';
    }


.. _`__toString`: http://www.php.net/manual/en/language.oop5.magic.php#object.tostring
