Troubleshooting
===============

The toString method
-------------------

Sometimes the bundle needs to display your model objects, in order to do it,
objects are converted to string by using the `__toString`_ magic method.
Take care to never return anything else than a string in this method.
For example, if your method looks like that :

.. code-block:: php

    public function __toString()
    {
        return $this->getTitle();
    }


You cannot be sure your object will *always* have a title when the bundle will want to convert it to a string.
So in order to avoid any fatal error, you must return an empty string
(or anything you prefer) for when the title is missing, like this :

.. code-block:: php

    public function __toString()
    {
        return $this->getTitle() ?: '';
    }


.. _`__toString`: http://www.php.net/manual/en/language.oop5.magic.php#object.tostring


Large filters and long urls problem
-----------------------------------

If you will try to add hundreds of filters to a single admin class, you will get a problem - very long generated filter form url.
In most cases you will get server response like *Error 400 Bad Request* OR *Error 414 Request-URI Too Long*. According to
`a StackOverflow discussion <http://stackoverflow.com/questions/417142/what-is-the-maximum-length-of-a-url-in-different-browsers>`_
"safe" url length is just around 2000 characters.
You can fix this issue by adding a simple JQuery piece of code on your edit template :

.. code-block:: javascript

    $(function() {
        // Add class 'had-value-on-load' to inputs/selects with values.
        $(".sonata-filter-form input").add(".sonata-filter-form select").each(function(){
            if($(this).val()) {
                $(this).addClass('had-value-on-load');
            }
        });
        // REMOVE ALL EMPTY INPUT FROM FILTER FORM (except inputs, which has class 'had-value-on-load')
        $(".sonata-filter-form").submit(function() {
            $(".sonata-filter-form input").add(".sonata-filter-form select").each(function(){
                if(!$(this).val() && !$(this).hasClass('had-value-on-load')) {
                    $(this).remove()
                };
            });
        });
    });


