Field Types
===========

List and Show Actions
---------------------

There are many field types that can be used in the list action or show action :

* **array**: display value from an array
* **boolean**: display a green or red picture dependant on the boolean value
* **date**: display a formatted date. Accepts an optional ``format`` parameter
* **datetime**: display a formatted date and time. Accepts an optional ``format`` parameter
* **text**: display a text
* **trans**: translate the value with a provided ``catalogue`` option
* **string**: display a text
* **decimal**: display a number
* **currency**: display a number with a provided ``currency`` option
* **percent**: display a percentage
* **choice**: uses the given value as index for the ``choices`` array and displays (and optionally translates) the matching value
* **url**: display a link

Theses types accept an ``editable`` parameter to edit the value from within the list action.
This is currently limited to scalar types (text, integer, url...).

.. note::

    If the ``SonataIntlBundle`` is installed in the project some template types
    will be changed to use localized information.

    Option for currency type must be an official ISO code, example : EUR for "euros".
    List of ISO codes : `http://en.wikipedia.org/wiki/List_of_circulating_currencies <http://en.wikipedia.org/wiki/List_of_circulating_currencies>`_

    In ``date`` and ``datetime`` field types, ``format`` pattern must match twig's
    ``date`` filter specification, available at: `http://twig.sensiolabs.org/doc/filters/date.html <http://twig.sensiolabs.org/doc/filters/date.html>`_
    
More types might be provided based on the persistency layer defined. Please refer to their
related documentations.

Choice
^^^^^^

.. code-block:: php

    // For value `prog` is displayed text `In progress`. The `AcmeDemoBundle` catalogue will be used to translate `In progress` message.
    $listMapper->add('status', 'choice', array('choices'=>array('prep'=>'Prepared', 'prog'=>'In progress', 'done'=>'Done'), 'catalogue' => 'AcmeDemoBundle'));

``choice`` filed type also supports multiple values that can be separated by ``delimiter`` (default delimiter is a comma ",").

.. code-block:: php

    // For value `array('r', 'b')` is displayed `text `red | blue`.
    $listMapper->add('colors', 'choice', array('multiple'=>true, 'delimiter'=>' | ', 'choices'=>array('r'=>'red', 'g'=>'green', 'b'=>'blue')));

Url
^^^

Display url link to external website or controller's action.


Parameters:

* **hide_protocol**: remove protocol part from the link text
* **url**: url address (e.g. ``http://example.com``)
* **route.name**: route name (e.g. ``acme_demo_homepage``)
* **route.parameters**: array of route parameters (e.g. ``array('type'=>'example', 'display'=>'full')``)
* **route.absolute**: boolean value, create absolute or relative url address based on ``route.name`` and  ``route.parameters`` (defalut ``false``)
* **route.identifier_parameter_name**: parameter added to ``route.parameters``, it's value is an object identifier (e.g. 'id') to create dynamic links based on rendered objects.

.. code-block:: php

    // Output for value `http://example.com`: `<a href="http://example.com">http://example.com</a>`
    $listMapper->add('targetUrl', 'url');

    // Output for value `http://example.com`: `<a href="http://example.com">example.com</a>`
    $listMapper->add('targetUrl', 'url', array('hide_protocol' => true));

    // Output for value `Homepage of example.com` : `<a href="http://example.com">Homepage of example.com</a>`
    $listMapper->add('title', 'url', array('url' => 'http://example.com'));

    // Output for value `Acme Blog Homepage`: `<a href="http://blog.example.com">Acme Blog Homepage</a>`
    $listMapper->add('title', 'url', array('route' => array('name'=>'acme_blog_homepage', 'absolute'=>true)));

    // Output for value `Sonata is great!` (related object has identifier `123`): `<a href="http://blog.example.com/xml/123">Sonata is great!</a>`
    $listMapper->add('title', 'url', array('route' => array('name'=>'acme_blog_article', 'absolute'=>true, 'parameters'=>array('format'=>'xml'), 'identifier_parameter_name'=>'id')));

.. note::

    Do not use ``url`` type with ``addIdentifier`` method, because it will create invalid nested urls.
